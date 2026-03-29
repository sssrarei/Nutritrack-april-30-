<?php
include '../includes/auth.php';
checkRole(1);
?>

<!DOCTYPE html>
<html>
<head>
    <title>CSWD Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #ececf1;
        }

        /* TOPBAR */
        .topbar {
            height: 78px;
            background: #ffffff;
            border-bottom: 1px solid #cfcfcf;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .menu-btn {
            font-size: 34px;
            cursor: pointer;
            border: none;
            background: none;
            color: #444;
            padding: 0;
            line-height: 1;
        }

        .logo {
            height: 60px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 28px;
            font-size: 15px;
            color: #333;
        }

        .notif-link {
            text-decoration: none;
            color: #222;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .notif-icon {
            font-size: 22px;
        }

        .user-label {
            font-weight: 600;
        }

        .logout-btn {
            border: 2px solid #9d9d9d;
            background: #fff;
            border-radius: 24px;
            padding: 8px 22px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
        }

        /* SIDEBAR */
        .sidebar {
            width: 300px;
            background: #ffffff;
            border-right: 1px solid #d4d4d4;
            height: calc(100vh - 79px);
            position: fixed;
            top: 79px;
            left: -300px;
            transition: left 0.3s ease;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar.open {
            left: 0;
        }

        .sidebar-logo {
            text-align: center;
            padding: 18px 10px 8px;
        }

        .sidebar-logo img {
            height: 74px;
        }

        .menu-group {
            margin-top: 8px;
        }

        .menu-link,
        .accordion-btn {
            width: 100%;
            background: none;
            border: none;
            text-align: left;
            padding: 12px 28px;
            font-size: 15px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
        }

        .menu-link {
            text-decoration: none;
        }

        .menu-link.active {
            background: #c7c7c7;
        }

        .accordion-content {
            display: none;
            padding: 0 0 6px 0;
        }

        .accordion-content a {
            display: block;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            font-weight: bold;
            padding: 8px 46px;
        }

        .accordion-content a:hover,
        .menu-link:hover,
        .accordion-btn:hover {
            background: #efefef;
        }

        .arrow {
            font-size: 16px;
        }

        /* MAIN */
        .main-content {
            padding: 22px 36px 40px 36px;
        }

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 4px 0 22px;
            color: #333;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .card {
            border: 1px solid #595959;
            background: #fff;
            min-height: 112px;
        }

        .card-title {
            color: #fff;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
            padding: 10px 8px;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        .card-body {
            height: 62px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .bottom-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 26px;
            margin-top: 36px;
        }

        .panel {
            background: #fff;
            border: 1px solid #bcbcbc;
            min-height: 300px;
        }

        .panel-header {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            padding: 12px 16px 6px 16px;
        }

        .panel-body {
            padding: 14px 16px 20px 16px;
            color: #666;
        }

        .placeholder-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .placeholder-table th,
        .placeholder-table td {
            border: 1px solid #d0d0d0;
            padding: 10px 8px;
            font-size: 14px;
            text-align: left;
        }

        .placeholder-table th {
            background: #f2f2f2;
        }

        .as-of {
            font-weight: bold;
            margin-top: 14px;
            color: #444;
        }

        .graph-box {
            height: 210px;
            border-top: 1px solid #d0d0d0;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #777;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            box-sizing: border-box;
        }

        @media (max-width: 1200px) {
            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .bottom-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-left">
            <button class="menu-btn" onclick="toggleSidebar()">☰</button>
            <a href="dashboard.php">
                <img src="../NUTRITRACK-LOGO.svg" alt="NutriTrack Logo" class="logo">
            </a>
        </div>

        <div class="topbar-right">
            <a href="#" class="notif-link">
                <span class="notif-icon">🔔</span>
                <span>Notifications</span>
            </a>

            <span class="user-label">CSWD - <?php echo htmlspecialchars($_SESSION['first_name']); ?></span>

            <a href="../logout.php">
                <button class="logout-btn">Logout</button>
            </a>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="../NUTRITRACK-LOGO.svg" alt="NutriTrack Logo">
        </div>

        <div class="menu-group">
            <a href="#" class="menu-link active">Child Profiles</a>

            <a href="#" class="menu-link">Monitoring reports</a>

            <a href="#" class="menu-link">Intervention Guidance</a>

            <button class="accordion-btn" onclick="toggleAcc('managementMenu')">
                <span>Management</span>
                <span class="arrow">⌄</span>
            </button>
            <div class="accordion-content" id="managementMenu">
                <a href="add_cdc.php">CDC Management</a>
                <a href="add_user.php">User Management</a>
            </div>

            <button class="accordion-btn" onclick="toggleAcc('notifMenu')">
                <span>Notifications</span>
                <span class="arrow">⌄</span>
            </button>
            <div class="accordion-content" id="notifMenu">
                <a href="#">Create Reminder</a>
            </div>

            <button class="accordion-btn" onclick="toggleAcc('settingsMenu')">
                <span>Settings</span>
                <span class="arrow">⌄</span>
            </button>
            <div class="accordion-content" id="settingsMenu">
                <a href="#">Profile Information</a>
                <a href="#">Change Password</a>
            </div>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content">
        <div class="title">CAVITE CITY OF BACOOR</div>

        <div class="cards-grid">
            <div class="card">
                <div class="card-title" style="background:#3498db;">
                    Total no. of Child Enrolled in Child Development Center
                </div>
                <div class="card-body">0</div>
            </div>

            <div class="card">
                <div class="card-title" style="background:#4caf50;">Normal</div>
                <div class="card-body">0</div>
            </div>

            <div class="card">
                <div class="card-title" style="background:#e74c3c;">Underweight</div>
                <div class="card-body">0</div>
            </div>

            <div class="card">
                <div class="card-title" style="background:#8b0000;">Severely Underweight</div>
                <div class="card-body">0</div>
            </div>

            <div class="card">
                <div class="card-title" style="background:#f39c12;">Overweight</div>
                <div class="card-body">0</div>
            </div>

            <div class="card">
                <div class="card-title" style="background:#c0392b;">Obese</div>
                <div class="card-body">0</div>
            </div>

            <div class="card">
                <div class="card-title" style="background:#9b59b6;">Stunted</div>
                <div class="card-body">0</div>
            </div>

            <div class="card">
                <div class="card-title" style="background:#6c3483;">Severely Stunted</div>
                <div class="card-body">0</div>
            </div>

            <div class="card">
                <div class="card-title" style="background:#f1c40f; color:#fff;">Moderately Wasted</div>
                <div class="card-body">0</div>
            </div>

            <div class="card">
                <div class="card-title" style="background:#b7950b;">Severely Wasted</div>
                <div class="card-body">0</div>
            </div>
        </div>

        <div class="bottom-section">
            <div class="panel">
                <div class="panel-header">CDC NUTRITIONAL STATUS SUMMARY</div>
                <div class="panel-body">
                    <table class="placeholder-table">
                        <tr>
                            <th>CDC Name</th>
                            <th>Total Pupils</th>
                            <th>Normal</th>
                            <th>Underweight</th>
                            <th>Severely Underweight</th>
                            <th>Overweight</th>
                        </tr>
                        <tr>
                            <td>Sample CDC 1</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <td>Sample CDC 2</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                        </tr>
                    </table>
                    <div class="as-of">AS OF MARCH 12, 2026 3:00PM</div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header" style="text-align:center;">
                    Graphical Representation of the Nutritional Status of Children Across All Child Development Center
                </div>
                <div class="graph-box">
                    Graph Placeholder
                </div>
                <div class="panel-body" style="padding-top:0;">
                    <div class="as-of" style="text-align:right;">AS OF MARCH 12, 2026 3:00PM</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("open");
        }

        function toggleAcc(id) {
            var content = document.getElementById(id);
            if (content.style.display === "block") {
                content.style.display = "none";
            } else {
                content.style.display = "block";
            }
        }
    </script>

</body>
</html>