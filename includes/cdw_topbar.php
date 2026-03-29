<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cdw_name = trim(
    (isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '') . ' ' .
    (isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '')
);

if ($cdw_name == '') {
    $cdw_name = 'CDW User';
}
?>

<div class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" onclick="toggleSidebar()" type="button" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <a href="dashboard.php" class="topbar-brand">
            <img src="../NUTRITRACK-LOGO.svg" alt="NutriTrack Logo" class="topbar-logo">
        </a>
    </div>

    <div class="topbar-right">
        <a href="notifications.php" class="notif-text">🔔 Notifications</a>

        <div class="user-text">
            CDW: <?php echo htmlspecialchars($cdw_name); ?>
        </div>

        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>