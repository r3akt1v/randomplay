<?php
declare(strict_types=1);

require_once __DIR__ . '/admin.php';

if (!site_admin_is()) {
    header('Location: /index.php', true, 302);
    exit;
}
