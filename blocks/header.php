<?php
require_once __DIR__ . '/../lib/history_store.php';
require_once __DIR__ . '/../lib/admin.php';
$navLogin = history_cookie_login();
$navIsAdmin = site_admin_is($navLogin);

// Determine current page more reliably
$currentPage = basename($_SERVER['PHP_SELF']);
if (empty($currentPage)) {
    $currentPage = basename($_SERVER['SCRIPT_NAME']);
}
?>
<header class="container">
    <span class="Random_Play"><a href="/index.php"><img src="assets/images/lll-Photoroom.png" alt=""></a></span>
    <nav>
        <ul>
            <li class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>"><a href="/index.php">Home</a></li> 
            <li class="<?php echo $currentPage == 'about.php' ? 'active' : ''; ?>"><a href="/about.php">About us</a></li>
            <li class="<?php echo $currentPage == 'films.php' ? 'active' : ''; ?>"><a href="/films.php">Films</a></li>
            <li class="<?php echo $currentPage == 'news.php' ? 'active' : ''; ?>"><a href="/news.php">News</a></li>
            <?php if ($navIsAdmin): ?>
            <li class="<?php echo $currentPage == 'report.php' ? 'active' : ''; ?>"><a href="/report.php">Reports</a></li>
            <li class="<?php echo $currentPage == 'charts.php' ? 'active' : ''; ?>"><a href="/charts.php">Charts</a></li>
            <li class="<?php echo $currentPage == 'ratings_history.php' ? 'active' : ''; ?>"><a href="/ratings_history.php">Ratings</a></li>
            <li class="<?php echo $currentPage == 'admin.php' ? 'active' : ''; ?>"><a href="/admin.php">Admin</a></li>
            <?php endif; ?>
            <li class="btn <?php echo $currentPage == 'contacts.php' ? 'active' : ''; ?>"><a href="/contacts.php">Contacts</a></li>
            <?php
                if ($navLogin !== null) {
                    echo '<li class="' . ($currentPage == 'user.php' ? 'active' : '') . '"><a href="/user.php">User</a></li>';
                } else {
                    echo '<li class="btn ' . ($currentPage == 'login.php' ? 'active' : '') . '"><a href="/login.php">Log in</a></li>';
                }
            ?>
        </ul>
    </nav>
</header>
