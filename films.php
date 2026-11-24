<?php
require_once __DIR__ . "/lib/films_catalog.php";
require_once __DIR__ . "/lib/analytics_store.php";

$catalog = films_catalog();
$filmWatchViews = analytics_film_watch_views_all_time();
$filmRatings = analytics_film_avg_ratings_all_time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>RandomPlay</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width-device-width, initial-scale=1.0">
    <link rel="stylesheet" type = "text/css" href="style.css?v=1.0.8">
</head>
<body>
    <div class="wrapper">
        <?php require_once "blocks/header.php"; ?>
        <div class="films container">
            <div class="films--info">
                <h2>Films</h2>
                <h1>This is our choice</h1>
                <p>All our films are presented below, we recommend all of them for viewing, I am sure you will have a pleasant time watching them. We wish you pleasant viewing!</p>
            </div>
            <div class="photo">
            <img src="assets/images/wisefilms-Photoroom.png" alt="">
            </div>
        </div>  
        <div class="container trending_films">
            <div class="films-page-top">
                <h3 class="films-page-title">All our films</h3>
                <a href="/films.php" class="see-all">SEE ALL</a>
            </div>
            <div class="films-toolbar">
                <div class="films-toolbar-inner">
                    <label class="films-search-wrap" for="filmsSearchInput">
                        <span class="films-toolbar-label">Search</span>
                        <input type="search" id="filmsSearchInput" class="films-search-input" placeholder="Type a film title…" autocomplete="off" spellcheck="false">
                    </label>
                    <label class="films-genre-wrap" for="filmsGenreFilter">
                        <span class="films-toolbar-label">Genre</span>
                        <select id="filmsGenreFilter" class="films-genre-select">
                            <option value="">All genres</option>
                            <option value="action">Action</option>
                            <option value="drama">Drama</option>
                            <option value="comedy">Comedy</option>
                            <option value="fantasy">Fantasy</option>
                        </select>
                    </label>
                    <button type="button" class="films-filter-reset" id="filmsFilterReset" title="Clear filters">Reset</button>
                </div>
                <p class="films-filter-count" id="filmsFilterCount" aria-live="polite"></p>
            </div>
            <div class="films_all" id="filmsCatalogGrid">
                <?php foreach ($catalog as $fid => $m): ?>
                    <?php
                    $searchBlob = strtolower((string) $m['title'] . ' ' . $fid);
                    ?>
                    <div class="block films-catalog-card" data-film-id="<?= htmlspecialchars($fid, ENT_QUOTES, 'UTF-8') ?>" data-genre="<?= htmlspecialchars((string) $m['genre'], ENT_QUOTES, 'UTF-8') ?>" data-search="<?= htmlspecialchars($searchBlob, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (($m['img'] ?? '') !== ''): ?>
                        <img src="<?= htmlspecialchars($m['img'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <?php else: ?>
                        <div class="films-card-title-plate" aria-hidden="true"><?= htmlspecialchars((string) $m['title'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <span>
                            <?= (int)($filmWatchViews[$fid] ?? 0) ?> Views
                            <?= (int)$m['price'] ?> rub
                            |
                            Rating:
                            <?php if (!isset($filmRatings[$fid])): ?>
                                —
                            <?php else: ?>
                                <?= number_format((float)$filmRatings[$fid], 1) ?>/10
                            <?php endif; ?>
                        </span>
                        <a href="<?= htmlspecialchars($m['href'], ENT_QUOTES, 'UTF-8') ?>" class="more-button"> more</a>
                        <button class="add-to-cart" data-film-id="<?= htmlspecialchars($fid, ENT_QUOTES, 'UTF-8') ?>" data-title="<?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?>" data-price="<?= (int)$m['price'] ?>">Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <h3>There will be even more films soon</h3>
        </div>
        <div class="container cart-outer">
            <div class="cart">
                <h3>Your Cart</h3>
                <div class="cart-items"></div>
                <div class="total-price">Total: <span id="total">0</span> rub</div>
                <div class="cart-actions">
                    <button type="button" id="clearCartBtn" class="cart-clear-btn">Clear cart</button>
                    <button type="button" id="checkoutBtn" class="more-button cart-checkout-btn">Pay</button>
                </div>
            </div>
        </div>
        <?php require_once "blocks/footer.php";?>
    </div>
    <script src="perehod.js?v=1.0.1"></script>
    <script src="cart.js?v=1.0.5"></script>
    <script>
        (function () {
            var input = document.getElementById('filmsSearchInput');
            var genre = document.getElementById('filmsGenreFilter');
            var reset = document.getElementById('filmsFilterReset');
            var countEl = document.getElementById('filmsFilterCount');
            var grid = document.getElementById('filmsCatalogGrid');
            if (!input || !genre || !grid || !countEl) return;

            var cards = grid.querySelectorAll('.films-catalog-card');
            var total = cards.length;

            function apply() {
                var q = (input.value || '').toLowerCase().trim();
                var g = (genre.value || '').toLowerCase();
                var shown = 0;
                for (var i = 0; i < cards.length; i++) {
                    var el = cards[i];
                    var hay = (el.getAttribute('data-search') || '').toLowerCase();
                    var gen = (el.getAttribute('data-genre') || '').toLowerCase();
                    var okQ = q === '' || hay.indexOf(q) !== -1;
                    var okG = g === '' || gen === g;
                    var ok = okQ && okG;
                    if (ok) {
                        el.classList.remove('is-filter-hidden');
                        shown++;
                    } else {
                        el.classList.add('is-filter-hidden');
                    }
                }
                countEl.textContent = shown === total
                    ? 'Showing all ' + total + ' films'
                    : ('Showing ' + shown + ' of ' + total + ' films');
            }

            input.addEventListener('input', apply);
            genre.addEventListener('change', apply);
            reset.addEventListener('click', function () {
                input.value = '';
                genre.value = '';
                apply();
                input.focus();
            });
            apply();
        })();
    </script>
</body>
</html> 