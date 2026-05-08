<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$admin_full_name = 'Admin User';

if (isset($_SESSION['first_name']) || isset($_SESSION['last_name'])) {
    $first_name = isset($_SESSION['first_name']) ? trim($_SESSION['first_name']) : '';
    $last_name  = isset($_SESSION['last_name']) ? trim($_SESSION['last_name']) : '';
    $full_name  = trim($first_name . ' ' . $last_name);

    if (!empty($full_name)) {
        $admin_full_name = $full_name;
    }
}

$pending_notifications = [];
$pending_notification_count = 0;

$notif_sql = "
    SELECT 
        n.notification_id,
        n.title,
        n.message,
        n.read_at,
        n.created_at,
        u.first_name,
        u.last_name
    FROM notifications n
    INNER JOIN users u ON n.user_id = u.user_id
    WHERE n.is_read = 1
      AND n.read_at IS NOT NULL
      AND n.admin_seen = 0
    ORDER BY n.read_at DESC, n.notification_id DESC
    LIMIT 10
";

$notif_result = $conn->query($notif_sql);

if ($notif_result && $notif_result->num_rows > 0) {
    while ($row = $notif_result->fetch_assoc()) {
        $pending_notifications[] = $row;
    }
}

$pending_notification_count = count($pending_notifications);
?>

<div class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle" type="button" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <a href="dashboard.php" class="topbar-logo-link">
            <img src="../NUTRITRACK-LOGO.png" alt="NutriTrack Logo" class="topbar-logo">
        </a>
    </div>

    <div class="topbar-right">
        <div class="topbar-notification" id="adminNotificationWrapper">
            <button class="notif-btn" id="adminNotifToggle" type="button" aria-label="View Notifications">
                <span class="notif-bell">&#128276;</span>
                <?php if ($pending_notification_count > 0): ?>
                    <span class="notif-badge"><?php echo $pending_notification_count; ?></span>
                <?php endif; ?>
            </button>

            <div class="notif-dropdown" id="adminNotifDropdown">
                <div class="notif-dropdown-header">CDW Read Notifications</div>

                <?php if (!empty($pending_notifications)): ?>
                    <?php foreach ($pending_notifications as $notif): ?>
                        <?php
                            $cdw_name = trim(($notif['first_name'] ?? '') . ' ' . ($notif['last_name'] ?? ''));
                        ?>
                        <div class="notif-dropdown-item">
                            <div class="notif-title">
                                <?php echo htmlspecialchars($notif['title']); ?>
                            </div>

                            <div class="notif-meta">
                                Read by: <?php echo htmlspecialchars($cdw_name ?: 'CDW User'); ?>
                            </div>

                            <div class="notif-meta">
                                Read at:
                                <?php echo !empty($notif['read_at']) ? date('F j, Y g:i A', strtotime($notif['read_at'])) : 'N/A'; ?>
                            </div>

                            <form action="mark_admin_notification_read.php" method="POST" class="notif-action-form">
                                <input type="hidden" name="notification_id" value="<?php echo (int)$notif['notification_id']; ?>">
                                <button type="submit" class="notif-read-btn">Mark as Read</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="notif-empty">No new read notifications yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <span class="user-text">CSWD: <?php echo htmlspecialchars($admin_full_name); ?></span>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const notifToggle = document.getElementById('adminNotifToggle');
    const notifDropdown = document.getElementById('adminNotifDropdown');
    const notifWrapper = document.getElementById('adminNotificationWrapper');

    if (notifToggle && notifDropdown && notifWrapper) {
        notifToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!notifWrapper.contains(e.target)) {
                notifDropdown.classList.remove('show');
            }
        });
    }
});
</script>