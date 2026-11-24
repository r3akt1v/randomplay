<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/films_catalog.php';
require_once __DIR__ . '/lib/analytics_store.php';
require_once __DIR__ . '/lib/history_store.php';

$id = isset($_GET['id']) ? (string) $_GET['id'] : '';
$catalog = films_catalog();
if ($id === '' || !isset($catalog[$id])) {
    http_response_code(404);
    $id = '';
} else {
    $href = (string) ($catalog[$id]['href'] ?? '');
    if ($href !== '' && preg_match('#^/film\d+\.php$#', $href)) {
        header('Location: ' . $href, true, 302);
        exit;
    }
}

$filmWatchViews = analytics_film_watch_views_all_time();
$lg = null;
$purchased = false;
$showFilmWatchScript = false;
if ($id !== '') {
    $lg = history_cookie_login();
    $purchased = $lg !== null && history_has_purchased($lg, $id);
    $showFilmWatchScript = $purchased;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>RandomPlay</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css?v=1.0.8">
</head>
<body>
    <div class="wrapper">
        <?php require_once __DIR__ . '/blocks/header.php'; ?>
        <?php if ($id === ''): ?>
        <div class="hero-film-custom container">
            <div class="hero--info">
                <h2>Film</h2>
                <h1>Not found</h1>
                <p><a href="/films.php">Back to films</a></p>
            </div>
        </div>
        <?php else: ?>
            <?php
                $m = $catalog[$id];
                $w = (int) ($filmWatchViews[$id] ?? 0);
                $desc = (string) ($m['description'] ?? '');
            ?>
        <div class="hero-film-custom container">
            <div class="hero--info">
                <h2>Film</h2>
                <h1><?= (int) $w ?> Views <?= (int) $m['price'] ?> rub</h1>
                <?php if ($desc !== ''): ?>
                    <p><?= nl2br(htmlspecialchars($desc, ENT_QUOTES, 'UTF-8')) ?></p>
                <?php else: ?>
                    <p>No description.</p>
                <?php endif; ?>
                <?php if ($lg !== null && $purchased): ?>
                    <button type="button" class="add-to-cart watch-film more-button film-watch-btn" data-film-id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" data-title="<?= htmlspecialchars((string) $m['title'], ENT_QUOTES, 'UTF-8') ?>">Watch</button>
                <?php endif; ?>
            </div>
            <div class="film-hero-side film-detail-text-poster"><?= htmlspecialchars((string) $m['title'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php require_once __DIR__ . '/blocks/footer.php'; ?>
    <script src="perehod.js"></script>
    <?php if ($showFilmWatchScript): ?>
    <script>
        (function () {
            var btn = document.querySelector('.watch-film');
            if (!btn) return;
            var API = '/api/history.php';
            btn.addEventListener('click', function () {
                var filmId = btn.getAttribute('data-film-id') || '';
                var title = btn.getAttribute('data-title') || '';
                fetch(API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ action: 'view', filmId: filmId, title: title }),
                }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, status: r.status, body: j }; }); })
                .then(function (res) {
                    if (res.ok && res.body && res.body.ok) {
                        alert('Просмотрено');
                    } else if (res.status === 403) {
                        alert('Сначала оплатите фильм');
                    } else {
                        alert('Ошибка');
                    }
                }).catch(function () { alert('Сеть недоступна'); });
            });
        })();
    </script>
    <?php endif; ?>
</body>
</html>
