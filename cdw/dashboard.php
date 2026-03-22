<?php
include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 2){
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

// Kunin lahat ng assigned CDC ng logged-in CDW
$cdc_result = $conn->query("
    SELECT c.cdc_id, c.cdc_name, c.barangay
    FROM cdw_assignments ca
    JOIN cdc c ON ca.cdc_id = c.cdc_id
    WHERE ca.user_id = '$user_id'
    ORDER BY c.cdc_name ASC
");

// Switch active CDC
if(isset($_POST['switch_cdc'])){
    $selected_cdc_id = $_POST['cdc_id'];

    $check = $conn->query("
        SELECT * FROM cdw_assignments
        WHERE user_id = '$user_id' AND cdc_id = '$selected_cdc_id'
    ");

    if($check && $check->num_rows > 0){
        $_SESSION['active_cdc_id'] = $selected_cdc_id;

        $cdc_info = $conn->query("
            SELECT cdc_name, barangay
            FROM cdc
            WHERE cdc_id = '$selected_cdc_id'
        ");

        if($cdc_info && $cdc_info->num_rows > 0){
            $cdc_row = $cdc_info->fetch_assoc();
            $_SESSION['active_cdc_name'] = $cdc_row['cdc_name'];
            $_SESSION['active_cdc_barangay'] = $cdc_row['barangay'];
        }

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid CDC selection.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>CDW Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f4f6;
        }

        .topbar {
            height: 66px;
            background: #ffffff;
            border-bottom: 1px solid #dcdcdc;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .menu-btn {
            font-size: 25px;
            cursor: pointer;
            border: none;
            background: none;
        }

        .logo {
            height: 56px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 13px;
        }

        .notif-link {
            text-decoration: none;
            color: #000;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn {
            border: 1px solid #bbb;
            background: #fff;
            border-radius: 20px;
            padding: 7px 16px;
            cursor: pointer;
        }

        .sidebar {
            width: 260px;
            background: #f7f7f7;
            border-right: 1px solid #dcdcdc;
            height: calc(100vh - 66px);
            position: fixed;
            top: 66px;
            left: -260px;
            overflow-y: auto;
            transition: left 0.3s ease;
            z-index: 1000;
        }

        .sidebar.open {
            left: 0;
        }

        .sidebar-header {
            padding: 18px 20px 10px 20px;
            font-weight: bold;
            font-size: 14px;
            color: #444;
        }

        .accordion-btn {
            width: 100%;
            background: none;
            border: none;
            text-align: left;
            padding: 14px 20px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border-top: 1px solid #e2e2e2;
        }

        .accordion-content {
            display: none;
            padding: 0 0 8px 0;
            background: #fafafa;
        }

        .accordion-content a {
            display: block;
            text-decoration: none;
            color: #222;
            font-size: 13px;
            padding: 8px 28px;
        }

        .accordion-content a:hover {
            background: #ececec;
        }

        .main-content {
            padding: 18px 24px 28px 24px;
        }

        .cdc-switch-area {
            text-align: center;
            margin: 8px 0 18px 0;
        }

        .cdc-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .cdc-form select,
        .cdc-form button {
            padding: 8px 10px;
            font-size: 14px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-top: 20px;
        }

        .card {
            border: 1px solid #bcbcbc;
            background: #fff;
            min-height: 82px;
        }

        .card-title {
            color: #fff;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
            padding: 10px 8px;
        }

        .card-body {
            height: 40px;
            background: #fff;
        }

        .bottom-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 26px;
        }

        .placeholder-box {
            background: #fff;
            border: 1px solid #bcbcbc;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #555;
            font-weight: bold;
            text-align: center;
            padding: 20px;
        }

        .error-msg {
            color: red;
            text-align: center;
            margin-top: 8px;
        }

        .active-cdc-text {
            margin-top: 10px;
            color: #333;
            font-size: 14px;
        }

        @media (max-width: 1100px) {
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
        <a href="notifications.php" class="notif-link">
            <span>🔔</span>
            <span>Notifications</span>
        </a>

        <span>CDW - <?php echo htmlspecialchars($_SESSION['first_name']); ?></span>

        <a href="../logout.php">
            <button class="logout-btn">Logout</button>
        </a>
    </div>
</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">MENU</div>

    <button class="accordion-btn" onclick="toggleAccordion('pupilRecords')">Pupil Records</button>
    <div class="accordion-content" id="pupilRecords">
        <a href="child_list.php">Child Profile / Add Pupil</a>
        <a href="#">Input Height, Weight and MUAC</a>
        <a href="#">Feeding</a>
        <a href="#">Milk Feeding</a>
        <a href="#">Deworming</a>
    </div>

    <button class="accordion-btn" onclick="toggleAccordion('reportsMenu')">Reports</button>
    <div class="accordion-content" id="reportsMenu">
        <a href="#">WMR</a>
        <a href="#">Masterlist of Beneficiaries</a>
        <a href="#">Feeding Attendance</a>
        <a href="#">Nutritional Status Summary</a>
        <a href="#">Terminal Report</a>
    </div>

    <button class="accordion-btn" onclick="toggleAccordion('settingsMenu')">Settings</button>
    <div class="accordion-content" id="settingsMenu">
        <a href="#">Profile Information</a>
        <a href="#">Change Password</a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <?php if(!empty($error)){ ?>
        <p class="error-msg"><?php echo $error; ?></p>
    <?php } ?>

    <div class="cdc-switch-area">
        <div class="cdc-title">
            <?php
            if(isset($_SESSION['active_cdc_name']) && !empty($_SESSION['active_cdc_name'])){
                echo htmlspecialchars($_SESSION['active_cdc_name']);
            } else {
                echo "NO ACTIVE CDC";
            }
            ?>
        </div>

        <form method="POST" class="cdc-form">
            <select name="cdc_id" required>
                <option value="">Select CDC</option>
                <?php
                if($cdc_result && $cdc_result->num_rows > 0){
                    while($cdc = $cdc_result->fetch_assoc()){
                ?>
                    <option value="<?php echo $cdc['cdc_id']; ?>"
                        <?php
                        if(isset($_SESSION['active_cdc_id']) && $_SESSION['active_cdc_id'] == $cdc['cdc_id']){
                            echo "selected";
                        }
                        ?>>
                        <?php echo htmlspecialchars($cdc['cdc_name'] . " - " . $cdc['barangay']); ?>
                    </option>
                <?php
                    }
                }
                ?>
            </select>
            <button type="submit" name="switch_cdc">Switch</button>
        </form>

        <?php if(isset($_SESSION['active_cdc_name'])){ ?>
            <div class="active-cdc-text">
                Active CDC:
                <?php echo htmlspecialchars($_SESSION['active_cdc_name']); ?>
                <?php
                if(isset($_SESSION['active_cdc_barangay']) && !empty($_SESSION['active_cdc_barangay'])){
                    echo " - " . htmlspecialchars($_SESSION['active_cdc_barangay']);
                }
                ?>
            </div>
        <?php } ?>
    </div>

    <div class="cards-grid">
        <div class="card">
            <div class="card-title" style="background:#3498db;">Total no. of Child Enrolled in Child Development Center</div>
            <div class="card-body"></div>
        </div>

        <div class="card">
            <div class="card-title" style="background:#4caf50;">Normal</div>
            <div class="card-body"></div>
        </div>

        <div class="card">
            <div class="card-title" style="background:#e74c3c;">Underweight</div>
            <div class="card-body"></div>
        </div>

        <div class="card">
            <div class="card-title" style="background:#8b0000;">Severely Underweight</div>
            <div class="card-body"></div>
        </div>

        <div class="card">
            <div class="card-title" style="background:#f39c12;">Overweight</div>
            <div class="card-body"></div>
        </div>

        <div class="card">
            <div class="card-title" style="background:#c0392b;">Obese</div>
            <div class="card-body"></div>
        </div>

        <div class="card">
            <div class="card-title" style="background:#9b59b6;">Stunted</div>
            <div class="card-body"></div>
        </div>

        <div class="card">
            <div class="card-title" style="background:#6c3483;">Severely Stunted</div>
            <div class="card-body"></div>
        </div>

        <div class="card">
            <div class="card-title" style="background:#f1c40f; color:#fff;">Moderately Wasted</div>
            <div class="card-body"></div>
        </div>

        <div class="card">
            <div class="card-title" style="background:#b7950b;">Severely Wasted</div>
            <div class="card-body"></div>
        </div>
    </div>

    <div class="bottom-section">
        <div class="placeholder-box">
            Food pyramid / nutrition guide placeholder
        </div>

        <div class="placeholder-box">
            Graph placeholder
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("open");
    }

    function toggleAccordion(id) {
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