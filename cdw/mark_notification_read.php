<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notification_id = (int) $_POST['notification_id'];
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    if ($notification_id > 0 && $user_id > 0) {
        $update_sql = "
            UPDATE notifications
            SET is_read = 1, read_at = NOW()
            WHERE notification_id = ?
              AND user_id = ?
              AND is_read = 0
        ";

        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ii", $notification_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

$redirect_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'dashboard.php';
header("Location: " . $redirect_page);
exit();
?>