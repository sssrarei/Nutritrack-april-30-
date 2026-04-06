<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$cdw_name = trim(
    (isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '') . ' ' .
    (isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '')
);

if ($cdw_name == '') {
    $cdw_name = 'CDW User';
}

$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$cdw_notifications = [];
$unread_count = 0;

if ($user_id > 0) {
    $notif_sql = "
        SELECT 
            notification_id,
            title,
            message,
            deadline,
            is_read,
            created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC, notification_id DESC
        LIMIT 10
    ";

    $stmt = $conn->prepare($notif_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $cdw_notifications[] = $row;

        if ((int)$row['is_read'] === 0) {
            $unread_count++;
        }
    }

    $stmt->close();
}
?>

<div class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" onclick="toggleSidebar()" type="button" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <a href="dashboard.php" class="topbar-logo-link">
            <img src="../NUTRITRACK-LOGO.svg" alt="NutriTrack Logo" class="topbar-logo">
        </a>
    </div>

    <div class="topbar-right">
        <div class="topbar-notification" id="cdwNotificationWrapper">
            <button class="notif-btn" id="cdwNotifToggle" type="button" aria-label="View Notifications">
                <span class="notif-bell">&#128276;</span>
                <?php if ($unread_count > 0): ?>
                    <span class="notif-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </button>

            <div class="notif-dropdown" id="cdwNotifDropdown">
                <div class="notif-dropdown-header">Notifications</div>

                <?php if (!empty($cdw_notifications)): ?>
                    <?php foreach ($cdw_notifications as $notif): ?>
                        <div class="notif-dropdown-item">
                            <div class="notif-title">
                                <?php echo htmlspecialchars($notif['title']); ?>
                            </div>

                            <div class="notif-meta">
                                <?php echo htmlspecialchars($notif['message']); ?>
                            </div>

                            <?php if (!empty($notif['deadline'])): ?>
                                <div class="notif-meta">
                                    Deadline: <?php echo date('F j, Y', strtotime($notif['deadline'])); ?>
                                </div>
                            <?php endif; ?>

                            <div class="notif-meta">
                                <?php echo !empty($notif['created_at']) ? date('F j, Y g:i A', strtotime($notif['created_at'])) : ''; ?>
                            </div>

                            <?php if ((int)$notif['is_read'] === 0): ?>
                                <form action="mark_notification_read.php" method="POST" class="notif-action-form">
                                    <input type="hidden" name="notification_id" value="<?php echo (int)$notif['notification_id']; ?>">
                                    <button type="submit" class="notif-read-btn">Mark as Read</button>
                                </form>
                            <?php else: ?>
                                <div class="notif-meta">Already read</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="notif-empty">No notifications yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="user-text">
            CDW: <?php echo htmlspecialchars($cdw_name); ?>
        </div>

        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const notifToggle = document.getElementById('cdwNotifToggle');
    const notifDropdown = document.getElementById('cdwNotifDropdown');
    const notifWrapper = document.getElementById('cdwNotificationWrapper');

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