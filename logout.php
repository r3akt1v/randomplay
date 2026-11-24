<?php
/**
 * Clear login cookie and return to home.
 */
setcookie('login', '', time() - 3600, '/');
header('Location: /index.php');
exit;
