<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">MENU</div>

    <div class="sidebar-section">
        <a href="dashboard.php" class="sidebar-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            Dashboard
        </a>

        <a href="child_list.php" class="sidebar-link <?php echo ($current_page == 'child_list.php' || $current_page == 'child_profile.php' || $current_page == 'add_child.php' || $current_page == 'edit_child.php' || $current_page == 'edit_guardian.php') ? 'active' : ''; ?>">
            Pupil Records
        </a>

        <a href="anthropometric_records.php" class="sidebar-link <?php echo ($current_page == 'anthropometric_records.php' || $current_page == 'anthropometric_list.php') ? 'active' : ''; ?>">
            Input Height, Weight and MUAC
        </a>

        <a href="feeding_list.php" class="sidebar-link <?php echo ($current_page == 'feeding_list.php' || $current_page == 'feeding.php') ? 'active' : ''; ?>">
            Feeding
        </a>

        <a href="milk_feeding_list.php" class="sidebar-link <?php echo ($current_page == 'milk_feeding_list.php' || $current_page == 'milk_feeding.php') ? 'active' : ''; ?>">
            Milk Feeding
        </a>

        <a href="deworming_list.php" class="sidebar-link <?php echo ($current_page == 'deworming_list.php' || $current_page == 'deworming.php') ? 'active' : ''; ?>">
            Deworming
        </a>
    </div>

    <div class="sidebar-label">Reports</div>
    <div class="sidebar-section">
        <a href="wmr_report.php" class="sidebar-link <?php echo ($current_page == 'wmr.php') ? 'active' : ''; ?>">
            WMR
        </a>

        <a href="masterlist_beneficiaries.php" class="sidebar-link <?php echo ($current_page == 'masterlist.php') ? 'active' : ''; ?>">
            Masterlist of Beneficiaries
        </a>

        <a href="feeding_attendance_report.php" class="sidebar-link <?php echo ($current_page == 'feeding_attendance.php') ? 'active' : ''; ?>">
            Feeding Attendance
        </a>

        <a href="nutritional_status_summary.php" class="sidebar-link <?php echo ($current_page == 'nutritional_status_summary.php') ? 'active' : ''; ?>">
            Nutritional Status Summary
        </a>

        <a href="terminal_report.php" class="sidebar-link <?php echo ($current_page == 'terminal_report.php') ? 'active' : ''; ?>">
            Terminal Report
        </a>
    </div>

    <div class="sidebar-label">Account</div>
    <div class="sidebar-section">
        <a href="settings.php" class="sidebar-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
            Settings
        </a>
         <a href="change_password.php" class="sidebar-link <?php echo ($current_page == 'change_password.php') ? 'active' : ''; ?>">
            Change Password
        </a>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>