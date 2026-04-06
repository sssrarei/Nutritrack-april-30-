<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>MENU</h2>
    </div>

    <div class="sidebar-section">
        <!-- CHILD -->
        <a href="child_records.php" class="sidebar-link">Child Profiles</a>

        <!-- MONITORING -->
        <div class="sidebar-title">Monitoring</div>
        <a href="monitoring_reports.php" class="sidebar-link">Monitoring Reports</a>
        <a href="intervention_guidance.php" class="sidebar-link">Intervention Guidance</a>

        <!-- MANAGEMENT -->
        <div class="sidebar-title">Management</div>
        <a href="add_cdc.php" class="sidebar-sublink">CDC Management</a>
        <a href="add_user.php" class="sidebar-sublink">User Management</a>

        <!-- NOTIFICATIONS -->
        <div class="sidebar-title">Notifications</div>
        <a href="create_reminder.php" class="sidebar-sublink">Create Reminder</a>

        <!-- SETTINGS -->
        <div class="sidebar-title">Settings</div>
        <a href="settings.php" class="sidebar-sublink">Profile Information</a>
        <a href="change_password.php" class="sidebar-sublink">Change Password</a>

    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>