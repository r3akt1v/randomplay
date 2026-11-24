<?php
/**
 * JSON history per user: purchases + views (demo project, no real payments).
 */

function history_data_dir(): string
{
    return dirname(__DIR__) . '/data/users';
}

/** Non-empty login from cookie (value as stored at login). */
function history_cookie_login(): ?string
{
    if (empty($_COOKIE['login'])) {
        return null;
    }
    $login = trim((string) $_COOKIE['login']);
    return $login === '' ? null : $login;
}

/**
 * Safe filename slug for JSON path (same login always maps to same file).
 */
function history_user_slug(string $login): string
{
    $s = preg_replace('/[^A-Za-z0-9_-]+/', '_', $login);
    $s = trim($s, '_');
    if ($s === '') {
        return 'u_' . md5($login);
    }
    if (strlen($s) > 80) {
        $s = substr($s, 0, 80);
    }
    return $s;
}

function history_user_file(string $login): string
{
    return history_data_dir() . '/' . history_user_slug($login) . '.json';
}

function history_ensure_dir(): void
{
    $dir = history_data_dir();
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

/**
 * @return array{purchases: list<array>, views: list<array>}
 */
function history_load(string $login): array
{
    history_ensure_dir();
    $path = history_user_file($login);
    if (!is_file($path)) {
        return ['purchases' => [], 'views' => []];
    }
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
        return ['purchases' => [], 'views' => []];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return ['purchases' => [], 'views' => []];
    }
    $purchases = isset($data['purchases']) && is_array($data['purchases']) ? $data['purchases'] : [];
    $views = isset($data['views']) && is_array($data['views']) ? $data['views'] : [];
    return ['purchases' => $purchases, 'views' => $views];
}

function history_save(string $login, array $data): bool
{
    history_ensure_dir();
    $path = history_user_file($login);
    $payload = json_encode(
        [
            'purchases' => $data['purchases'] ?? [],
            'views' => $data['views'] ?? [],
        ],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
    if ($payload === false) {
        return false;
    }
    return file_put_contents($path, $payload, LOCK_EX) !== false;
}

function history_purchased_film_ids(string $login): array
{
    $data = history_load($login);
    $ids = [];
    foreach ($data['purchases'] as $row) {
        if (!empty($row['filmId']) && is_string($row['filmId'])) {
            $ids[$row['filmId']] = true;
        }
    }
    return array_keys($ids);
}

function history_has_purchased(string $login, string $filmId): bool
{
    foreach (history_load($login)['purchases'] as $row) {
        if (isset($row['filmId']) && $row['filmId'] === $filmId) {
            return true;
        }
    }
    return false;
}

/**
 * Append purchase rows (one per cart line).
 *
 * @param list<array{filmId: string, title: string, price: int}> $items
 */
function history_append_purchases(string $login, array $items): void
{
    $data = history_load($login);
    $ts = time();
    $seenInBatch = [];
    foreach ($items as $item) {
        if (empty($item['filmId']) || empty($item['title'])) {
            continue;
        }
        $fid = (string) $item['filmId'];
        if (isset($seenInBatch[$fid])) {
            continue;
        }
        if (history_has_purchased($login, $fid)) {
            continue;
        }
        $seenInBatch[$fid] = true;
        $price = isset($item['price']) ? (int) $item['price'] : 0;
        $data['purchases'][] = [
            'filmId' => $fid,
            'title' => (string) $item['title'],
            'price' => $price,
            'ts' => $ts,
        ];
    }
    history_save($login, $data);
}

function history_append_view(string $login, string $filmId, string $title): void
{
    $data = history_load($login);
    $data['views'][] = [
        'filmId' => $filmId,
        'title' => $title,
        'ts' => time(),
    ];
    history_save($login, $data);
}

/**
 * Sort purchase/view rows for display: newest timestamp first.
 *
 * @param list<array> $rows
 * @return list<array>
 */
function history_sort_rows_by_ts_desc(array $rows): array
{
    usort($rows, static function (array $a, array $b): int {
        return ((int) ($b['ts'] ?? 0)) <=> ((int) ($a['ts'] ?? 0));
    });
    return $rows;
}

/** Format stored Unix timestamp for display in Moscow time (MSK). */
function history_format_ts_msk($ts): string
{
    if (!is_int($ts) && !is_numeric($ts)) {
        return '';
    }
    $t = (int) $ts;
    if ($t <= 0) {
        return '';
    }
    $dt = (new DateTimeImmutable('@' . $t))->setTimezone(new DateTimeZone('Europe/Moscow'));

    return $dt->format('Y-m-d H:i:s');
}
