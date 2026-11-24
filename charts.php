<?php
require_once __DIR__ . '/lib/analytics_gate.php';
require_once __DIR__ . '/lib/analytics_store.php';

$visits = analytics_visits_by_day(7);
$summary = analytics_compute_summary();
$ratings = analytics_ratings_hist(30);

// For charts: use watchByGenre30 if available, else empty.
$genreCounts = $summary['watchByGenre30'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>RandomPlay — Analytics charts</title>
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
            <h1>Charts &amp; trends</h1>
            <p class="analytics-lead">Charts are generated from live page views, watch history, and rating votes.</p>
            <nav class="analytics-tabs" aria-label="Analytics sections">
            </nav>
            <div class="analytics-download-buttons">
                <button onclick="downloadPDF('charts')" class="analytics-download-btn analytics-download-btn--pdf">Download PDF Report</button>
                <a href="export_excel.php?type=charts" class="analytics-download-btn analytics-download-btn--excel">Download Excel Report</a>
            </div>

            <div class="analytics-charts">
                <div class="analytics-chart-card">
                    <h2 class="analytics-chart-title">Visits by day (7 days)</h2>
                    <canvas id="chartVisits" width="400" height="220" aria-label="Line chart: visits by day"></canvas>
                </div>
                <div class="analytics-chart-card">
                    <h2 class="analytics-chart-title">Views by genre (30 days)</h2>
                    <canvas id="chartGenres" width="400" height="220" aria-label="Bar chart: watch time by genre"></canvas>
                </div>
                <div class="analytics-chart-card analytics-chart-card--wide">
                    <h2 class="analytics-chart-title">Ratings (1–10, last 30 days)</h2>
                    <canvas id="chartSources" width="520" height="240" aria-label="Doughnut chart: traffic sources"></canvas>
                </div>
            </div>
        </div>
    </div>
    <?php require_once "blocks/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script>
        (function () {
            const brand = { blue: '#00618c', orange: '#ff9500', gray: '#b1b1b1' };
            const grid = { color: 'rgba(255,255,255,0.08)' };
            const text = '#fff';
            if (typeof Chart === 'undefined') return;
            const visitsLabels = <?= json_encode($visits['labels'], JSON_UNESCAPED_UNICODE) ?>;
            const visitsCounts = <?= json_encode($visits['counts'], JSON_UNESCAPED_UNICODE) ?>;
            const genreLabels = <?= json_encode(array_keys($genreCounts), JSON_UNESCAPED_UNICODE) ?>;
            const genreCounts = <?= json_encode(array_values($genreCounts), JSON_UNESCAPED_UNICODE) ?>;
            const ratingLabels = <?= json_encode($ratings['labels'], JSON_UNESCAPED_UNICODE) ?>;
            const ratingCounts = <?= json_encode($ratings['counts'], JSON_UNESCAPED_UNICODE) ?>;
            new Chart(document.getElementById('chartVisits'), {
                type: 'line',
                data: {
                    labels: visitsLabels,
                    datasets: [{
                        label: 'Visits',
                        data: visitsCounts,
                        borderColor: brand.orange,
                        backgroundColor: 'rgba(255, 149, 0, 0.15)',
                        fill: true,
                        tension: 0.3,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { labels: { color: text } } },
                    scales: {
                        x: { ticks: { color: text }, grid: { color: grid.color } },
                        y: { ticks: { color: text }, grid: { color: grid.color } },
                    },
                },
            });
            new Chart(document.getElementById('chartGenres'), {
                type: 'bar',
                data: {
                    labels: genreLabels.length ? genreLabels : ['—'],
                    datasets: [{
                        label: 'Views',
                        data: genreCounts.length ? genreCounts : [0],
                        backgroundColor: genreLabels.map((_, i) => (i % 2 === 0 ? brand.blue : brand.orange)),
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: text }, grid: { color: grid.color } },
                        y: { ticks: { color: text }, grid: { color: grid.color } },
                    },
                },
            });
            new Chart(document.getElementById('chartSources'), {
                type: 'bar',
                data: {
                    labels: ratingLabels,
                    datasets: [{
                        label: 'Votes',
                        data: ratingCounts,
                        backgroundColor: ratingLabels.map((_, i) => (i % 2 === 0 ? brand.gray : brand.orange)),
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: text }, grid: { color: grid.color } },
                        y: { ticks: { color: text }, grid: { color: grid.color } },
                    },
                },
            });
        })();
    </script>
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
                
                if (type === 'charts') {
                    // Visits by day
                    doc.setFontSize(14);
                    doc.text('Visits by Day (7 days)', 15, yPos);
                    yPos += 10;
                    
                    const visitsData = data.visits_by_day.map(row => [row.day, row.visits]);
                    doc.autoTable({
                        startY: yPos,
                        head: [['Day', 'Visits']],
                        body: visitsData,
                        theme: 'grid',
                        headStyles: { fillColor: [0, 97, 140] }
                    });
                    yPos = doc.lastAutoTable.finalY + 15;
                    
                    // Views by genre
                    doc.setFontSize(14);
                    doc.text('Views by Genre (30 days)', 15, yPos);
                    yPos += 10;
                    
                    const genreData = data.views_by_genre.map(row => [row.genre, row.views]);
                    doc.autoTable({
                        startY: yPos,
                        head: [['Genre', 'Views']],
                        body: genreData,
                        theme: 'grid',
                        headStyles: { fillColor: [0, 97, 140] }
                    });
                    yPos = doc.lastAutoTable.finalY + 15;
                    
                    // Ratings distribution
                    doc.setFontSize(14);
                    doc.text('Ratings Distribution (30 days)', 15, yPos);
                    yPos += 10;
                    
                    const ratingsData = data.ratings_distribution.map(row => [row.rating, row.votes]);
                    doc.autoTable({
                        startY: yPos,
                        head: [['Rating', 'Votes']],
                        body: ratingsData,
                        theme: 'grid',
                        headStyles: { fillColor: [0, 97, 140] }
                    });
                    
                } else if (type === 'report') {
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
