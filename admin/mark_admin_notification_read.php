<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notification_id = (int) $_POST['notification_id'];

    if ($notification_id > 0) {
        $update_sql = "UPDATE notifications 
                       SET admin_seen = 1, admin_seen_at = NOW() 
                       WHERE notification_id = ? 
                         AND is_read = 1 
                         AND admin_seen = 0";

        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $notification_id);
        $stmt->execute();
        $stmt->close();
    }
}

$redirect_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'dashboard.php';
header("Location: " . $redirect_page);
exit();
?>