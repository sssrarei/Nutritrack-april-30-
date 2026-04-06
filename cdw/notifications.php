<?php
include '../includes/auth.php';
include '../config/database.php';
checkRole(2);

$user_id = $_SESSION['user_id'];

if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $notification_id = (int)$_GET['read'];

    $mark_stmt = $conn->prepare("
        UPDATE notifications
        SET is_read = 1, read_at = NOW()
        WHERE notification_id = ? AND user_id = ?
    ");
    $mark_stmt->bind_param("ii", $notification_id, $user_id);
    $mark_stmt->execute();

    header("Location: notifications.php");
    exit();
}

$notifications = $conn->prepare("
    SELECT notification_id, title, message, deadline, is_read, read_at, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC, notification_id DESC
");
$notifications->bind_param("i", $user_id);
$notifications->execute();
$result = $notifications->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../assets/cdw-style.css">
    <link rel="stylesheet" href="../assets/cdw-topbar-notification.css">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Inter', sans-serif;
        }

        .main-content {
            padding: 24px;
        }

        .page-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            padding: 24px;
        }

        .page-title {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #2E7D32;
            margin-bottom: 20px;
        }

        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .notification-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 18px;
            background: #f9fafb;
        }

        .notification-card.unread {
            border-left: 5px solid #2563eb;
            background: #eff6ff;
        }

        .notification-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #111827;
        }

        .notification-card p {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .meta {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.6;
        }

        .read-link {
            display: inline-block;
            margin-top: 10px;
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }

        .empty-box {
            padding: 24px;
            text-align: center;
            color: #6b7280;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            background: #f9fafb;
        }
    </style>
</head>
<body>
<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>
<div class="main-content">
    <div class="page-card">
        <div class="page-title">Notifications</div>

        <?php if ($result && $result->num_rows > 0) { ?>
            <div class="notification-list">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="notification-card <?php echo ((int)$row['is_read'] === 0) ? 'unread' : ''; ?>">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                        <div class="meta">
                            Deadline: <?php echo !empty($row['deadline']) ? date("F d, Y", strtotime($row['deadline'])) : '—'; ?><br>
                            Sent: <?php echo !empty($row['created_at']) ? date("F d, Y g:i A", strtotime($row['created_at'])) : '—'; ?><br>
                            <?php if ((int)$row['is_read'] === 1 && !empty($row['read_at'])) { ?>
                                Read Viewed on <?php echo date("F d, Y g:i A", strtotime($row['read_at'])); ?>
                            <?php } ?>
                        </div>

                        <?php if ((int)$row['is_read'] === 0) { ?>
                            <a href="notifications.php?read=<?php echo (int)$row['notification_id']; ?>" class="read-link">Mark as Read</a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="empty-box">No notifications available.</div>
        <?php } ?>
    </div>
</div>

</body>
</html>