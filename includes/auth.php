<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include __DIR__ . '/../config/database.php';

$update_active = $conn->prepare("UPDATE users SET last_active = NOW() WHERE user_id = ?");
$update_active->bind_param("i", $_SESSION['user_id']);
$update_active->execute();

/* =========================
   FIX: prevent redeclare
========================= */
if (!function_exists('checkRole')) {
    function checkRole($allowed_role) {
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != $allowed_role) {
            header("Location: ../login.php");
            exit();
        }
    }
}

/* =========================
   theme function safe
========================= */
if (!function_exists('themeClass')) {
    function themeClass() {
        return (isset($_SESSION['theme_mode']) && $_SESSION['theme_mode'] === 'dark')
            ? 'dark-mode'
            : '';
    }
}
?>