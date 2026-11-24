<?php
require_once __DIR__ . '/lib/history_store.php';

$login = history_cookie_login();
if ($login === null) {
    header('Location: /login.php');
    exit;
}

$history = history_load($login);
$purchases = history_sort_rows_by_ts_desc($history['purchases']);
$views = history_sort_rows_by_ts_desc($history['views']);
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
    <?php require_once "blocks/header.php"; ?>
    <div class="feedback">
        <div class="container">
            <h2>User account</h2>
            <p class="feedback-user-meta">
                Hi: <b><?= htmlspecialchars($login, ENT_QUOTES, 'UTF-8') ?></b>.
                <a href="/logout.php" class="more-button feedback-user-logout">Log out</a>
            </p>

            <h3 style="margin-top:32px;color:#00618c;">Purchases</h3>
            <?php if (empty($purchases)): ?>
                <p>No purchases yet.</p>
            <?php else: ?>
                <div class="analytics-table-wrap" style="margin-top:12px;">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th scope="col">Film</th>
                                <th scope="col">Price</th>
                                <th scope="col">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchases as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)($row['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int)($row['price'] ?? 0) ?> rub</td>
                                    <td><?= htmlspecialchars(history_format_ts_msk($row['ts'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <h3 style="margin-top:32px;color:#00618c;">Views</h3>
            <?php if (empty($views)): ?>
                <p>No views yet.</p>
            <?php else: ?>
                <div class="analytics-table-wrap" style="margin-top:12px;">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th scope="col">Film</th>
                                <th scope="col">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($views as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)($row['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(history_format_ts_msk($row['ts'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php require_once "blocks/footer.php";?>
    <script src="perehod.js"></script>
</body>
</html>
