<?php
require_once __DIR__ . '/lib/analytics_gate.php';
require_once __DIR__ . '/lib/analytics_store.php';

$summary = analytics_compute_summary();
$catalog = films_catalog();

function film_name(array $catalog, string $filmId): string
{
    return isset($catalog[$filmId]) ? (string)$catalog[$filmId]['title'] : $filmId;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>RandomPlay — Analytics reports</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css?v=1.0.9">
</head>
<body>
    <div class="wrapper">
        <?php require_once "blocks/header.php"; ?>
        <div class="container analytics-page">
            <p class="analytics-eyebrow">Internal analytics (live)</p>
            <h1>Reports &amp; summary</h1>
            <p class="analytics-lead">Live counters built from page views, purchases, and watch history (local project).</p>
            <nav class="analytics-tabs" aria-label="Analytics sections">
            </nav>
            <div class="analytics-download-buttons">
                <button onclick="downloadPDF('report')" class="analytics-download-btn analytics-download-btn--pdf">Download PDF Report</button>
                <a href="export_excel.php?type=report" class="analytics-download-btn analytics-download-btn--excel">Download Excel Report</a>
            </div>

            <div class="analytics-kpi">
                <div class="analytics-kpi-card">
                    <span class="analytics-kpi-label">Unique visitors (7d)</span>
                    <strong><?= (int)$summary['uniqueVisitors7'] ?></strong>
                    <span class="analytics-kpi-delta is-up">Tracked via ipHash/day</span>
                </div>
                <div class="analytics-kpi-card">
                    <span class="analytics-kpi-label">Visits (7d)</span>
                    <strong><?= (int)$summary['visits7'] ?></strong>
                    <span class="analytics-kpi-delta is-up">Page opens</span>
                </div>
                <div class="analytics-kpi-card">
                    <span class="analytics-kpi-label">Watch events (30d)</span>
                    <strong><?= (int)$summary['watchCount30'] ?></strong>
                    <span class="analytics-kpi-delta is-up">Button “Watch”</span>
                </div>
                <div class="analytics-kpi-card">
                    <span class="analytics-kpi-label">Revenue (30d)</span>
                    <strong><?= (int)$summary['revenue30'] ?> rub</strong>
                    <span class="analytics-kpi-delta is-up">Avg. order <?= (int)$summary['avgOrder30'] ?> rub</span>
                </div>
            </div>
            <h2 class="analytics-h2">Weekly performance</h2>
            <div class="analytics-table-wrap">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th scope="col">Metric</th>
                            <th scope="col">This week</th>
                            <th scope="col">Prev. week</th>
                            <th scope="col">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Page views (7d)</td>
                            <td><?= (int)$summary['visits7'] ?></td>
                            <td><?= (int)max(0, (int)$summary['visits30'] - (int)$summary['visits7']) ?></td>
                            <td>Live from `pageviews.jsonl`.</td>
                        </tr>
                        <tr>
                            <td>Purchases (30d)</td>
                            <td><?= (int)$summary['purchaseCount30'] ?></td>
                            <td>—</td>
                            <td>Live from user history JSON.</td>
                        </tr>
                        <tr>
                            <td>Top watched genre (30d)</td>
                            <td>
                                <?php
                                    $topGenre = '';
                                    if (!empty($summary['watchByGenre30'])) {
                                        $g = array_key_first($summary['watchByGenre30']);
                                        $topGenre = $g;
                                    }
                                ?>
                                <?= htmlspecialchars($topGenre !== '' ? $topGenre : '—', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>—</td>
                            <td>Computed from watch history.</td>
                        </tr>
                        <tr>
                            <td>Rating avg (30d)</td>
                            <td>
                                <?php if ($summary['avgRating30'] === null): ?>
                                    —
                                <?php else: ?>
                                    <?= number_format((float)$summary['avgRating30'], 2) ?> / 10
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$summary['ratingCount30'] ?> votes</td>
                            <td>Prompt after “Watch”.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <h2 class="analytics-h2">Content snapshot</h2>
            <div class="analytics-snapshot-grid">
                <div class="analytics-table-wrap analytics-snapshot-card">
                    <h3 class="analytics-snapshot-title">Top pages (30d)</h3>
                    <table class="analytics-table analytics-table--snapshot">
                        <thead>
                            <tr>
                                <th scope="col">Path</th>
                                <th scope="col" class="analytics-table-num">Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($summary['topPages30'])): ?>
                                <tr><td colspan="2">—</td></tr>
                            <?php else: ?>
                                <?php foreach (($summary['topPages30'] ?? []) as $p => $c): ?>
                                    <tr>
                                        <td class="analytics-snapshot-path"><?= htmlspecialchars((string) $p, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="analytics-table-num"><?= (int) $c ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="analytics-table-wrap analytics-snapshot-card">
                    <h3 class="analytics-snapshot-title">Top purchased films (30d)</h3>
                    <table class="analytics-table analytics-table--snapshot">
                        <thead>
                            <tr>
                                <th scope="col">Film</th>
                                <th scope="col" class="analytics-table-num">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($summary['topPurchasedFilms30'])): ?>
                                <tr><td colspan="2">—</td></tr>
                            <?php else: ?>
                                <?php foreach (($summary['topPurchasedFilms30'] ?? []) as $filmId => $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(film_name($catalog, (string) $filmId), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="analytics-table-num"><?= (int) $c ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="analytics-table-wrap analytics-snapshot-card">
                    <h3 class="analytics-snapshot-title">Top watched films (30d)</h3>
                    <table class="analytics-table analytics-table--snapshot">
                        <thead>
                            <tr>
                                <th scope="col">Film</th>
                                <th scope="col" class="analytics-table-num">Watches</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($summary['topWatchedFilms30'])): ?>
                                <tr><td colspan="2">—</td></tr>
                            <?php else: ?>
                                <?php foreach (($summary['topWatchedFilms30'] ?? []) as $filmId => $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(film_name($catalog, (string) $filmId), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="analytics-table-num"><?= (int) $c ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php require_once "blocks/footer.php"; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script>
        async function downloadPDF(type) {
            try {
                // Загружаем данные из JSON API
                const response = await fetch('/api/analytics_json.php?type=' + type);
                const data = await response.json();
                
                // Создаем PDF с помощью jsPDF
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                // Заголовок
                doc.setFontSize(20);
                doc.text(data.title, 15, 20);
                doc.setFontSize(10);
                doc.text('Generated: ' + data.generated, 15, 28);
                
                let yPos = 40;
                
                // KPI cards
                doc.setFontSize(14);
                doc.text('Key Performance Indicators', 15, yPos);
                yPos += 10;
                
                const kpiData = [
                    ['Unique visitors (7d)', data.kpi.unique_visitors_7d],
                    ['Visits (7d)', data.kpi.visits_7d],
                    ['Watch events (30d)', data.kpi.watch_events_30d],
                    ['Revenue (30d)', data.kpi.revenue_30d + ' rub'],
                    ['Avg order (30d)', data.kpi.avg_order_30d + ' rub']
                ];
                doc.autoTable({
                    startY: yPos,
                    head: [['Metric', 'Value']],
                    body: kpiData,
                    theme: 'grid',
                    headStyles: { fillColor: [0, 97, 140] }
                });
                yPos = doc.lastAutoTable.finalY + 15;
                
                // Weekly performance
                doc.setFontSize(14);
                doc.text('Weekly Performance', 15, yPos);
                yPos += 10;
                
                const weeklyData = data.weekly_performance.map(row => [row.metric, row.this_week, row.prev_week, row.note]);
                doc.autoTable({
                    startY: yPos,
                    head: [['Metric', 'This week', 'Prev. week', 'Note']],
                    body: weeklyData,
                    theme: 'grid',
                    headStyles: { fillColor: [0, 97, 140] }
                });
                yPos = doc.lastAutoTable.finalY + 15;
                
                // Top purchased films
                if (data.top_purchased_films.length > 0) {
                    doc.setFontSize(14);
                    doc.text('Top Purchased Films (30d)', 15, yPos);
                    yPos += 10;
                    
                    const filmsData = data.top_purchased_films.map(row => [row.film, row.count]);
                    doc.autoTable({
                        startY: yPos,
                        head: [['Film', 'Count']],
                        body: filmsData,
                        theme: 'grid',
                        headStyles: { fillColor: [0, 97, 140] }
                    });
                    yPos = doc.lastAutoTable.finalY + 15;
                }
                
                // Top watched films
                if (data.top_watched_films.length > 0) {
                    doc.setFontSize(14);
                    doc.text('Top Watched Films (30d)', 15, yPos);
                    yPos += 10;
                    
                    const watchedData = data.top_watched_films.map(row => [row.film, row.count]);
                    doc.autoTable({
                        startY: yPos,
                        head: [['Film', 'Watches']],
                        body: watchedData,
                        theme: 'grid',
                        headStyles: { fillColor: [0, 97, 140] }
                    });
                }
                
                // Top pages
                if (data.top_pages.length > 0) {
                    doc.setFontSize(14);
                    doc.text('Top Pages (30d)', 15, yPos);
                    yPos += 10;
                    
                    const pagesData = data.top_pages.map(row => [row.path, row.views]);
                    doc.autoTable({
                        startY: yPos,
                        head: [['Path', 'Views']],
                        body: pagesData,
                        theme: 'grid',
                        headStyles: { fillColor: [0, 97, 140] }
                    });
                }
                
                doc.save(type + '_report.pdf');
                
            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Ошибка при генерации PDF: ' + error.message);
            }
        }
    </script>
    <script src="perehod.js"></script>
</body>
</html>
