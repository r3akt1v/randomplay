<?php
declare(strict_types=1);

require_once __DIR__ . '/history_store.php';

/** Reserved site admin login (cookie `login`). */
const SITE_ADMIN_LOGIN = 'admin1';

function site_admin_login(): string
{
    return SITE_ADMIN_LOGIN;
}

function site_admin_is(?string $login = null): bool
{
    if ($login === null) {
        $login = history_cookie_login();
    }

    return $login !== null && $login === SITE_ADMIN_LOGIN;
}
