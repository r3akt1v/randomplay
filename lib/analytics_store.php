<?php
declare(strict_types=1);

require_once __DIR__ . '/history_store.php';
require_once __DIR__ . '/films_catalog.php';

function analytics_dir(): string
{
    return dirname(__DIR__) . '/data/analytics';
}

function analytics_ensure_dir(): void
{
    $dir = analytics_dir();
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function analytics_append_jsonl(string $filename, array $row): bool
{
    analytics_ensure_dir();
    $path = analytics_dir() . '/' . $filename;
    $line = json_encode($row, JSON_UNESCAPED_UNICODE);
    if ($line === false) {
        return false;
    }
    $line .= "\n";
    return file_put_contents($path, $line, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Read JSONL rows (best-effort) with an optional time floor.
 *
 * @return list<array>
 */
function analytics_read_jsonl(string $filename, ?int $minTs = null): array
{
    $path = analytics_dir() . '/' . $filename;
    if (!is_file($path)) {
        return [];
    }
    $rows = [];
    $fh = @fopen($path, 'rb');
    if (!$fh) {
        return [];
    }
    while (($line = fgets($fh)) !== false) {
        $line = trim($line);
        if ($line === '') continue;
        $row = json_decode($line, true);
        if (!is_array($row)) continue;
        if ($minTs !== null && isset($row['ts']) && is_numeric($row['ts']) && (int)$row['ts'] < $minTs) {
            continue;
        }
        $rows[] = $row;
    }
    fclose($fh);
    return $rows;
}

/**
 * Aggregate site-wide metrics from:
 * - pageviews.jsonl (page opens)
 * - data/users/*.json (purchases + watch views)
 */
function analytics_compute_summary(): array
{
    $now = time();
    $ts7 = $now - 7 * 86400;
    $ts30 = $now - 30 * 86400;

    $pageviews7 = analytics_read_jsonl('pageviews.jsonl', $ts7);
    $pageviews30 = analytics_read_jsonl('pageviews.jsonl', $ts30);

    $visitCount7 = count($pageviews7);
    $visitCount30 = count($pageviews30);

    $unique7 = [];
    foreach ($pageviews7 as $pv) {
        $k = ($pv['ipHash'] ?? '') . '|' . ($pv['day'] ?? '');
        if ($k !== '|') $unique7[$k] = true;
    }
    $uniqueVisitors7 = count($unique7);

    $topPages = [];
    foreach ($pageviews30 as $pv) {
        $path = (string)($pv['path'] ?? '');
        if ($path === '') continue;
        $topPages[$path] = ($topPages[$path] ?? 0) + 1;
    }
    arsort($topPages);

    // Purchases + watch views come from user history JSON files.
    $usersDir = dirname(__DIR__) . '/data/users';
    $userFiles = glob($usersDir . '/*.json') ?: [];

    $purchaseCount30 = 0;
    $revenue30 = 0;
    $purchaseByFilm = [];

    $watchCount30 = 0;
    $watchByFilm = [];
    $watchByGenre = [];

    $catalog = films_catalog();

    foreach ($userFiles as $file) {
        $raw = @file_get_contents($file);
        if ($raw === false || $raw === '') continue;
        $data = json_decode($raw, true);
        if (!is_array($data)) continue;

        foreach (($data['purchases'] ?? []) as $p) {
            $ts = isset($p['ts']) && is_numeric($p['ts']) ? (int)$p['ts'] : 0;
            if ($ts < $ts30) continue;
            $purchaseCount30++;
            $price = isset($p['price']) ? (int)$p['price'] : 0;
            $revenue30 += $price;
            $filmId = (string)($p['filmId'] ?? '');
            if ($filmId !== '') {
                $purchaseByFilm[$filmId] = ($purchaseByFilm[$filmId] ?? 0) + 1;
            }
        }

        foreach (($data['views'] ?? []) as $v) {
            $ts = isset($v['ts']) && is_numeric($v['ts']) ? (int)$v['ts'] : 0;
            if ($ts < $ts30) continue;
            $watchCount30++;
            $filmId = (string)($v['filmId'] ?? '');
            if ($filmId !== '') {
                $watchByFilm[$filmId] = ($watchByFilm[$filmId] ?? 0) + 1;
                if (isset($catalog[$filmId])) {
                    $g = $catalog[$filmId]['genre'];
                    $watchByGenre[$g] = ($watchByGenre[$g] ?? 0) + 1;
                }
            }
        }
    }

    arsort($purchaseByFilm);
    arsort($watchByFilm);
    arsort($watchByGenre);

    $ratings30 = analytics_read_jsonl('ratings.jsonl', $ts30);
    $ratingSum = 0;
    $ratingCount = 0;
    foreach ($ratings30 as $r) {
        if (!isset($r['score']) || !is_numeric($r['score'])) continue;
        $s = (int)$r['score'];
        if ($s < 1 || $s > 10) continue;
        $ratingSum += $s;
        $ratingCount++;
    }
    $avgRating30 = $ratingCount ? ($ratingSum / $ratingCount) : null;

    return [
        'visits7' => $visitCount7,
        'visits30' => $visitCount30,
        'uniqueVisitors7' => $uniqueVisitors7,
        'topPages30' => array_slice($topPages, 0, 8, true),
        'purchaseCount30' => $purchaseCount30,
        'revenue30' => $revenue30,
        'avgOrder30' => $purchaseCount30 ? ($revenue30 / $purchaseCount30) : 0,
        'topPurchasedFilms30' => array_slice($purchaseByFilm, 0, 5, true),
        'watchCount30' => $watchCount30,
        'topWatchedFilms30' => array_slice($watchByFilm, 0, 5, true),
        'watchByGenre30' => $watchByGenre,
        'avgRating30' => $avgRating30,
        'ratingCount30' => $ratingCount,
    ];
}

/**
 * Visits by day for last N days (including today).
 *
 * @return array{labels:list<string>,counts:list<int>}
 */
function analytics_visits_by_day(int $days = 7): array
{
    $now = time();
    $start = strtotime(date('Y-m-d', $now)) - ($days - 1) * 86400;
    $rows = analytics_read_jsonl('pageviews.jsonl', $start);

    $counts = [];
    for ($i = 0; $i < $days; $i++) {
        $d = date('Y-m-d', $start + $i * 86400);
        $counts[$d] = 0;
    }
    foreach ($rows as $pv) {
        $day = (string)($pv['day'] ?? '');
        if (isset($counts[$day])) {
            $counts[$day]++;
        }
    }
    return [
        'labels' => array_keys($counts),
        'counts' => array_values($counts),
    ];
}

/**
 * Ratings distribution (1-10) for last N days.
 *
 * @return array{labels:list<int>,counts:list<int>}
 */
function analytics_ratings_hist(int $days = 30): array
{
    $now = time();
    $start = $now - $days * 86400;
    $rows = analytics_read_jsonl('ratings.jsonl', $start);
    $counts = array_fill(1, 10, 0);
    foreach ($rows as $r) {
        if (!isset($r['score']) || !is_numeric($r['score'])) continue;
        $s = (int)$r['score'];
        if ($s < 1 || $s > 10) continue;
        $counts[$s] = ($counts[$s] ?? 0) + 1;
    }
    return ['labels' => array_keys($counts), 'counts' => array_values($counts)];
}

/**
 * Film page views (all time) based on pageviews.jsonl paths like /film14.php.
 *
 * @return array<string,int> map filmId => viewsCount
 */
function analytics_film_pageviews_all_time(): array
{
    $rows = analytics_read_jsonl('pageviews.jsonl', null);
    $counts = [];
    foreach ($rows as $pv) {
        $path = (string)($pv['path'] ?? '');
        if ($path === '') continue;
        if (preg_match('#^/film(\\d{1,2})\\.php$#', $path, $m)) {
            $filmId = 'film' . $m[1];
            $counts[$filmId] = ($counts[$filmId] ?? 0) + 1;
        }
    }
    return $counts;
}

/**
 * Film views (all time) based on Watch history saved in data/users/*.json.
 * This matches "user clicked Watch".
 *
 * @return array<string,int> map filmId => watchCount
 */
function analytics_film_watch_views_all_time(): array
{
    $usersDir = dirname(__DIR__) . '/data/users';
    $userFiles = glob($usersDir . '/*.json') ?: [];
    $counts = [];

    foreach ($userFiles as $file) {
        $raw = @file_get_contents($file);
        if ($raw === false || $raw === '') continue;
        $data = json_decode($raw, true);
        if (!is_array($data)) continue;
        foreach (($data['views'] ?? []) as $v) {
            $filmId = (string)($v['filmId'] ?? '');
            if ($filmId === '') continue;
            $counts[$filmId] = ($counts[$filmId] ?? 0) + 1;
        }
    }

    return $counts;
}

/**
 * Average film rating (all time) based on ratings.jsonl.
 * If a film has no ratings, it will be absent from the map.
 *
 * @return array<string,float> map filmId => averageScore
 */
function analytics_film_avg_ratings_all_time(): array
{
    $catalog = films_catalog();
    $rows = analytics_read_jsonl('ratings.jsonl', null);
    $sum = [];
    $cnt = [];
    foreach ($rows as $r) {
        $filmId = (string)($r['filmId'] ?? '');
        if ($filmId === '' || !isset($catalog[$filmId])) {
            continue;
        }
        $score = isset($r['score']) && is_numeric($r['score']) ? (int)$r['score'] : 0;
        if ($score < 1 || $score > 10) continue;
        $sum[$filmId] = ($sum[$filmId] ?? 0) + $score;
        $cnt[$filmId] = ($cnt[$filmId] ?? 0) + 1;
    }
    $avg = [];
    foreach ($sum as $filmId => $s) {
        $c = $cnt[$filmId] ?? 0;
        if ($c > 0) {
            $avg[$filmId] = $s / $c;
        }
    }
    return $avg;
}

/**
 * Watch counts grouped by genre (all time), across all users.
 * Uses films_catalog() mapping filmId -> genre.
 *
 * @return array<string,int>
 */
function analytics_watch_by_genre_all_time(): array
{
    $catalog = films_catalog();
    $usersDir = dirname(__DIR__) . '/data/users';
    $userFiles = glob($usersDir . '/*.json') ?: [];
    $byGenre = [];

    foreach ($userFiles as $file) {
        $raw = @file_get_contents($file);
        if ($raw === false || $raw === '') continue;
        $data = json_decode($raw, true);
        if (!is_array($data)) continue;
        foreach (($data['views'] ?? []) as $v) {
            $filmId = (string)($v['filmId'] ?? '');
            if ($filmId === '' || !isset($catalog[$filmId])) continue;
            $g = (string)$catalog[$filmId]['genre'];
            $byGenre[$g] = ($byGenre[$g] ?? 0) + 1;
        }
    }

    return $byGenre;
}

/**
 * Ratings history with user information for admin view.
 * Combines ratings.jsonl with user data from cookies/user files.
 *
 * @return list<array> each with ts, filmId, score, context, user (login or 'guest')
 */
function analytics_ratings_history_with_users(?int $minTs = null): array
{
    $rows = analytics_read_jsonl('ratings.jsonl', $minTs);
    $catalog = films_catalog();
    
    $history = [];
    foreach ($rows as $r) {
        $filmId = (string)($r['filmId'] ?? '');
        if ($filmId === '' || !isset($catalog[$filmId])) {
            continue;
        }
        
        $score = isset($r['score']) && is_numeric($r['score']) ? (int)$r['score'] : 0;
        if ($score < 1 || $score > 10) continue;
        
        $ts = isset($r['ts']) && is_numeric($r['ts']) ? (int)$r['ts'] : 0;
        $context = (string)($r['context'] ?? '');
        
        // Try to get user from the record if available, otherwise mark as guest
        $user = (string)($r['user'] ?? 'guest');
        
        $history[] = [
            'ts' => $ts,
            'filmId' => $filmId,
            'filmTitle' => (string)$catalog[$filmId]['title'],
            'score' => $score,
            'context' => $context,
            'user' => $user,
        ];
    }
    
    // Sort by timestamp descending (newest first)
    usort($history, function($a, $b) {
        return $b['ts'] <=> $a['ts'];
    });
    
    return $history;
}

