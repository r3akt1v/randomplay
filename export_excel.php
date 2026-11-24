<?php
require_once __DIR__ . '/lib/analytics_gate.php';
require_once __DIR__ . '/lib/analytics_store.php';
require_once __DIR__ . '/lib/films_catalog.php';

$type = isset($_GET['type']) ? (string)$_GET['type'] : '';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $type . '_report.xls"');

$summary = analytics_compute_summary();
$catalog = films_catalog();

function film_name(array $catalog, string $filmId): string
{
    return isset($catalog[$filmId]) ? (string)$catalog[$filmId]['title'] : $filmId;
}

// Generate simple Excel-compatible HTML
$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RandomPlay - ' . htmlspecialchars($type) . ' Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #00618c; color: white; }
    </style>
</head>
<body>';

if ($type === 'report') {
    $html .= '<h1>RandomPlay - Analytics Report</h1>';
    $html .= '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
    
    $html .= '<h2>Key Performance Indicators</h2>';
    $html .= '<table>
        <tr><th>Metric</th><th>Value</th></tr>
        <tr><td>Unique visitors (7d)</td><td>' . (int)$summary['uniqueVisitors7'] . '</td></tr>
        <tr><td>Visits (7d)</td><td>' . (int)$summary['visits7'] . '</td></tr>
        <tr><td>Watch events (30d)</td><td>' . (int)$summary['watchCount30'] . '</td></tr>
        <tr><td>Revenue (30d)</td><td>' . (int)$summary['revenue30'] . ' rub</td></tr>
    </table>';
    
    $html .= '<h2>Weekly Performance</h2>';
    $html .= '<table>
        <tr><th>Metric</th><th>This week</th><th>Prev. week</th><th>Note</th></tr>
        <tr><td>Page views (7d)</td><td>' . (int)$summary['visits7'] . '</td><td>' . (int)max(0, (int)$summary['visits30'] - (int)$summary['visits7']) . '</td><td>Live from pageviews.jsonl</td></tr>
        <tr><td>Purchases (30d)</td><td>' . (int)$summary['purchaseCount30'] . '</td><td>—</td><td>Live from user history JSON</td></tr>
    </table>';
    
    $html .= '<h2>Top Purchased Films (30d)</h2>';
    $html .= '<table><tr><th>Film</th><th>Count</th></tr>';
    foreach (($summary['topPurchasedFilms30'] ?? []) as $filmId => $c) {
        $html .= '<tr><td>' . htmlspecialchars(film_name($catalog, (string)$filmId)) . '</td><td>' . (int)$c . '</td></tr>';
    }
    $html .= '</table>';
    
} elseif ($type === 'charts') {
    $html .= '<h1>RandomPlay - Charts Report</h1>';
    $html .= '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
    
    $visits = analytics_visits_by_day(7);
    $genreCounts = $summary['watchByGenre30'] ?? [];
    $ratings = analytics_ratings_hist(30);
    
    $html .= '<h2>Visits by Day (7 days)</h2>';
    $html .= '<table><tr><th>Day</th><th>Visits</th></tr>';
    foreach ($visits['labels'] as $i => $label) {
        $html .= '<tr><td>' . htmlspecialchars($label) . '</td><td>' . (int)($visits['counts'][$i] ?? 0) . '</td></tr>';
    }
    $html .= '</table>';
    
    $html .= '<h2>Views by Genre (30 days)</h2>';
    $html .= '<table><tr><th>Genre</th><th>Views</th></tr>';
    foreach ($genreCounts as $genre => $count) {
        $html .= '<tr><td>' . htmlspecialchars($genre) . '</td><td>' . (int)$count . '</td></tr>';
    }
    $html .= '</table>';
    
    $html .= '<h2>Ratings Distribution (30 days)</h2>';
    $html .= '<table><tr><th>Rating</th><th>Votes</th></tr>';
    foreach ($ratings['labels'] as $i => $label) {
        $html .= '<tr><td>' . htmlspecialchars($label) . '</td><td>' . (int)($ratings['counts'][$i] ?? 0) . '</td></tr>';
    }
    $html .= '</table>';
}

$html .= '</body></html>';

echo $html;
