<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="topbar">
    <div class="topbar-left">
        <button id="menuToggle" class="menu-toggle" type="button" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

       <A href="dashboard.php"><img src="../NUTRITRACK-LOGO.png" alt="NutriTrack Logo" class="topbar-logo">
       </A> 
    </div>

    <div class="topbar-right">
        <span class="user-text">
            Guardian - <?php echo htmlspecialchars($_SESSION['first_name']); ?>
        </span>

        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>