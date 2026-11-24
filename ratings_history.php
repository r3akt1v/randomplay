<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/analytics_gate.php';
require_once __DIR__ . '/lib/analytics_store.php';

if (!site_admin_is()) {
    header('Location: /login.php', true, 302);
    exit;
}

// Get ratings history
$ratingsHistory = analytics_ratings_history_with_users(null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>RandomPlay — Ratings History</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css?v=1.0.9">
</head>
<body>
    <div class="wrapper">
        <?php require_once __DIR__ . '/blocks/header.php'; ?>
        <div class="feedback">
            <div class="container admin-panel-narrow">
                <h2 class="admin-page-title">Ratings History</h2>
                <p class="feedback-user-meta">Logged in as <b><?= htmlspecialchars(site_admin_login(), ENT_QUOTES, 'UTF-8') ?></b></p>
                <p class="admin-manage-lead">Complete history of user ratings with timestamps and film information.</p>
                
                <a href="/admin.php" class="more-button" style="display: inline-block; margin-bottom: 20px;">← Back to Admin</a>
                
                <?php if (empty($ratingsHistory)): ?>
                    <p class="admin-manage-empty">No ratings recorded yet.</p>
                <?php else: ?>
                    <div class="analytics-table-wrap">
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th scope="col">Date/Time</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Film</th>
                                    <th scope="col" class="analytics-table-num">Rating</th>
                                    <th scope="col">Context</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ratingsHistory as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date('Y-m-d H:i:s', $row['ts']), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($row['user'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($row['filmTitle'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="analytics-table-num">
                                            <strong><?= (int)$row['score'] ?>/10</strong>
                                        </td>
                                        <td><?= htmlspecialchars($row['context'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p style="margin-top: 15px; opacity: 0.7; font-size: 14px;">
                        Total: <?= count($ratingsHistory) ?> ratings
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/blocks/footer.php'; ?>
    <script src="perehod.js"></script>
</body>
</html>