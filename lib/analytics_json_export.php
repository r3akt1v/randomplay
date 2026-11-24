<?php
require_once __DIR__ . '/analytics_gate.php';
require_once __DIR__ . '/analytics_store.php';
require_once __DIR__ . '/films_catalog.php';

function generate_analytics_json($type) {
    $summary = analytics_compute_summary();
    $catalog = films_catalog();
    
    function film_name(array $catalog, string $filmId): string {
        return isset($catalog[$filmId]) ? (string)$catalog[$filmId]['title'] : $filmId;
    }
    
    $data = [
        'type' => $type,
        'generated' => date('Y-m-d H:i:s'),
        'title' => $type === 'charts' ? 'RandomPlay - Charts Report' : 'RandomPlay - Analytics Report',
    ];
    
    if ($type === 'report') {
        $data['kpi'] = [
            'unique_visitors_7d' => (int)$summary['uniqueVisitors7'],
            'visits_7d' => (int)$summary['visits7'],
            'watch_events_30d' => (int)$summary['watchCount30'],
            'revenue_30d' => (int)$summary['revenue30'],
            'avg_order_30d' => (int)$summary['avgOrder30'],
        ];
        
        $data['weekly_performance'] = [
            [
                'metric' => 'Page views (7d)',
                'this_week' => (int)$summary['visits7'],
                'prev_week' => (int)max(0, (int)$summary['visits30'] - (int)$summary['visits7']),
                'note' => 'Live from pageviews.jsonl'
            ],
            [
                'metric' => 'Purchases (30d)',
                'this_week' => (int)$summary['purchaseCount30'],
                'prev_week' => '—',
                'note' => 'Live from user history JSON'
            ],
            [
                'metric' => 'Top watched genre (30d)',
                'this_week' => !empty($summary['watchByGenre30']) ? array_key_first($summary['watchByGenre30']) : '—',
                'prev_week' => '—',
                'note' => 'Computed from watch history'
            ],
            [
                'metric' => 'Rating avg (30d)',
                'this_week' => $summary['avgRating30'] !== null ? number_format((float)$summary['avgRating30'], 2) : '—',
                'prev_week' => (int)$summary['ratingCount30'] . ' votes',
                'note' => 'Prompt after "Watch"'
            ]
        ];
        
        $data['top_purchased_films'] = [];
        foreach (($summary['topPurchasedFilms30'] ?? []) as $filmId => $count) {
            $data['top_purchased_films'][] = [
                'film' => film_name($catalog, (string)$filmId),
                'count' => (int)$count
            ];
        }
        
        $data['top_watched_films'] = [];
        foreach (($summary['topWatchedFilms30'] ?? []) as $filmId => $count) {
            $data['top_watched_films'][] = [
                'film' => film_name($catalog, (string)$filmId),
                'count' => (int)$count
            ];
        }
        
        $data['top_pages'] = [];
        foreach (($summary['topPages30'] ?? []) as $path => $count) {
            $data['top_pages'][] = [
                'path' => (string)$path,
                'views' => (int)$count
            ];
        }
        
    } elseif ($type === 'charts') {
        $visits = analytics_visits_by_day(7);
        $genreCounts = $summary['watchByGenre30'] ?? [];
        $ratings = analytics_ratings_hist(30);
        
        $data['visits_by_day'] = [];
        foreach ($visits['labels'] as $i => $label) {
            $data['visits_by_day'][] = [
                'day' => (string)$label,
                'visits' => (int)($visits['counts'][$i] ?? 0)
            ];
        }
        
        $data['views_by_genre'] = [];
        foreach ($genreCounts as $genre => $count) {
            $data['views_by_genre'][] = [
                'genre' => (string)$genre,
                'views' => (int)$count
            ];
        }
        
        $data['ratings_distribution'] = [];
        foreach ($ratings['labels'] as $i => $label) {
            $data['ratings_distribution'][] = [
                'rating' => (string)$label,
                'votes' => (int)($ratings['counts'][$i] ?? 0)
            ];
        }
    }
    
    return $data;
}
