<!DOCTYPE html>
<html lang="en">
<head>
    <title>RandomPlay</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type = "text/css" href="style.css?v=1.0.8">
</head>
<body>
    <div class="wrapper">
        <?php
            require_once __DIR__ . "/lib/films_catalog.php";
            require_once __DIR__ . "/lib/history_store.php";
            require_once __DIR__ . "/lib/analytics_store.php";

            $catalog = films_catalog();
            $defaultTrending = ['film1', 'film2', 'film3', 'film4'];
            $filmWatchViews = analytics_film_watch_views_all_time();
            $filmRatings = analytics_film_avg_ratings_all_time();

            $login = history_cookie_login();
            $recommended = [];

            if ($login !== null) {
                $history = history_load($login);
                $genreCounts = [];
                foreach (($history['views'] ?? []) as $row) {
                    $filmId = isset($row['filmId']) ? (string)$row['filmId'] : '';
                    if ($filmId === '' || !isset($catalog[$filmId])) {
                        continue;
                    }
                    $genre = $catalog[$filmId]['genre'];
                    $genreCounts[$genre] = ($genreCounts[$genre] ?? 0) + 1;
                }

                if (!empty($genreCounts)) {
                    arsort($genreCounts);
                    $topGenre = array_key_first($genreCounts);
                    foreach ($catalog as $filmId => $meta) {
                        if ($meta['genre'] === $topGenre) {
                            $recommended[] = $filmId;
                        }
                    }
                }
            }

            $trendingFilmIds = [];
            foreach ($recommended as $fid) {
                if (count($trendingFilmIds) >= 4) break;
                $trendingFilmIds[] = $fid;
            }
            foreach ($defaultTrending as $fid) {
                if (count($trendingFilmIds) >= 4) break;
                if (!in_array($fid, $trendingFilmIds, true)) {
                    $trendingFilmIds[] = $fid;
                }
            }

            $watchByGenreAll = analytics_watch_by_genre_all_time();
            $siteGenreOrder = ['action', 'drama', 'comedy', 'fantasy'];
            $siteGenreTiles = [
                'action' => ['label' => 'Action', 'img' => 'assets/images/shuter_photo-resizer.ru.png', 'blurb' => 'High-energy stories and set-pieces that keep the pace up.'],
                'drama' => ['label' => 'Drama', 'img' => 'assets/images/melodrama_photo-resizer.ru.png', 'blurb' => 'Character-driven plots with emotional stakes and strong performances.'],
                'comedy' => ['label' => 'Comedy', 'img' => 'assets/images/comedian_photo.png', 'blurb' => 'Light, witty, and feel-good moments to unwind with.'],
                'fantasy' => ['label' => 'Fantasy', 'img' => 'assets/images/fantasy_photo.png', 'blurb' => 'Imaginative worlds, myths, and adventures beyond the everyday.'],
            ];
            $siteGenreCounts = [];
            $totalSiteGenreWatches = 0;
            foreach ($siteGenreOrder as $g) {
                $c = (int)($watchByGenreAll[$g] ?? 0);
                $siteGenreCounts[$g] = $c;
                $totalSiteGenreWatches += $c;
            }
            $siteGenrePct = [];
            foreach ($siteGenreOrder as $g) {
                $siteGenrePct[$g] = $totalSiteGenreWatches > 0
                    ? ($siteGenreCounts[$g] * 100.0 / $totalSiteGenreWatches)
                    : 0.0;
            }
        ?>
        <?php require_once "blocks/header.php"; ?>
        <div class="hero container">
            <div class="hero--info">
                <h2>Random Play</h2>
                <h1>Work thet we produce for our clients</h1>
                <p>The site presents countless of our films. Here every viewer can find a film to their liking. Each film has its own unique story and may be of interest to you. We wish you pleasant viewing.</p>
                <a href="/otzyv.php" class="otzyv">View reviews</a>
            </div>
            <img src="assets/images/3-Photoroom.png" alt="">
        </div>
        

        <div class="container trending">
            <h3><?= (!empty($recommended) ? 'Recommendations' : 'Currently Trending Films') ?></h3>
            <a href="/films.php" class="see-all">SEE ALL</a>
            <div class="films">
                <?php foreach ($trendingFilmIds as $fid): ?>
                    <?php if (!isset($catalog[$fid])) continue; ?>
                    <?php $m = $catalog[$fid]; ?>
                    <div class="block">
                        <a href="<?= htmlspecialchars($m['href'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php if (($m['img'] ?? '') !== ''): ?>
                            <img src="<?= htmlspecialchars($m['img'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                            <?php else: ?>
                            <div class="films-card-title-plate" aria-hidden="true"><?= htmlspecialchars((string) $m['title'], ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </a>
                        <span>
                            <img src="assets/images/fff-Photoroom_photo-resizer.ru.png" alt="">
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
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="container banner">
            <h3>The site features films for all ages</h3>
            <p>On our website you will find films of various genres: from classic cartoons to modern blockbusters, from family comedies to exciting adventures. We try to provide you only the best and most interesting films that will delight you and your loved ones. We understand that choosing a movie can be a difficult task, especially when it comes to children. That's why we've created easy navigation on our website that will allow you to easily find a movie that suits your child's age and interests.</p>
            <img src="assets/images/11.png" alt="">
        </div>
    </div>
    <div class="features">
        <div class="container">
            <h3>Your favorite genres</h3>
            <?php if ($totalSiteGenreWatches === 0): ?>
                <p>Share of completed <strong>Watch</strong> events across all viewers for our four catalog genres (action, drama, comedy, fantasy). No watch history has been recorded yet, so the split will appear after users watch purchased films.</p>
            <?php else: ?>
                <p>
                    Share of completed <strong>Watch</strong> events across all viewers for our four catalog genres (based on <?= (int)$totalSiteGenreWatches ?> total watches):
                    <?php foreach ($siteGenreOrder as $i => $g): ?>
                        <?php $t = $siteGenreTiles[$g]; ?>
                        <strong><?= htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8') ?></strong>
                        (<?= number_format((float)$siteGenrePct[$g], 2) ?>%, <?= (int)$siteGenreCounts[$g] ?> watches)<?= ($i < count($siteGenreOrder) - 1) ? ';' : '.' ?>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
            <div class="info info-4cols">
                <?php foreach ($siteGenreOrder as $g): ?>
                    <?php $t = $siteGenreTiles[$g]; ?>
                    <div class="block">
                        <img src="<?= htmlspecialchars($t['img'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8') ?>">
                        <p>
                            <strong><?= htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                            <?= $totalSiteGenreWatches > 0 ? htmlspecialchars(number_format((float)$siteGenrePct[$g], 2) . '%', ENT_QUOTES, 'UTF-8') : '—' ?>
                            <span class="features-genre-blurb"><?= htmlspecialchars($t['blurb'], ENT_QUOTES, 'UTF-8') ?></span>
                        </p>
                        <img src="assets/images/strelka_photo-resizer.ru.png" alt="">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="wrapper">
        <div class="container projects">
            <h3>Our employees</h3>
            <p>Our consultants will always help you with choosing a film just for you</p>
            <div class="images">
                <img src="assets/images/b1-Photoroom.png" alt="">
                <img src="assets/images/b2-Photoroom.png" alt="">
                <img src="assets/images/b3-Photoroom.png" alt="">
            </div>
            <div class="images">
                <img src="assets/images/b4-Photoroom.png" alt="">
                <img src="assets/images/b5-Photoroom.png" alt="">
                <img src="assets/images/b6-Photoroom.png" alt="">
            </div>
            <a href="/index.php" class="see-all">SEE ALL</a>
        </div>
        <div class="container email">
            <h3>We are always glad to see you on our website</h3>
            <p>I hope you liked us and don't forget to tell your friends about us</p>
            <div class="block">
                <div>
                    <h4>Our advertising</h4>
                    <p>Enter your email to know all the new items on our site</p>
                </div>
                <div>
                    <input type="email" id="emailField" placeholder="Enter your email adress">
                    <button onclick="checkEmail()">Continue</button>
                </div>
            </div>
        </div>
    </div>
    <?php require_once "blocks/footer.php";?>
    <script>
        function checkEmail(){
            let email = document.querySelector('#emailField').value;
            if(!email.includes('@')) alert('Нет символа @'); 
            else if(!email.includes('.')) alert('Нет символа .'); 
            else alert('Cool!')
        }
    </script>
    <script src="perehod.js"></script>
</body>
</html> 