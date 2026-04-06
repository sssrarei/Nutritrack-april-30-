<?php
include '../includes/auth.php';
include '../config/database.php';

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['theme_mode'])) {
    $_SESSION['theme_mode'] = 'light';
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

/* =========================
   SAVE PROFILE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);

    if ($first_name === '' || $last_name === '') {
        $error = "First name and last name are required.";
    } else {
        $sql_update = "UPDATE users 
                       SET first_name = ?, last_name = ?, contact_number = ?, address = ?
                       WHERE user_id = ? AND role_id = 2";
        $stmt_update = $conn->prepare($sql_update);

        if ($stmt_update) {
            $stmt_update->bind_param("ssssi", $first_name, $last_name, $contact_number, $address, $user_id);

            if ($stmt_update->execute()) {
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $success = "Profile information updated successfully.";
            } else {
                $error = "Failed to update profile information.";
            }

            $stmt_update->close();
        } else {
            $error = "Failed to prepare profile update.";
        }
    }
}

/* =========================
   SAVE THEME
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_theme'])) {
    $theme_mode = (isset($_POST['theme_mode']) && $_POST['theme_mode'] === 'dark') ? 'dark' : 'light';
    $_SESSION['theme_mode'] = $theme_mode;
    $success = "Appearance preference updated successfully.";
}

/* =========================
   FETCH USER INFORMATION
========================= */
$user = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'contact_number' => '',
    'address' => ''
];

$sql_user = "SELECT first_name, last_name, email, contact_number, address
             FROM users
             WHERE user_id = ? AND role_id = 2
             LIMIT 1";
$stmt_user = $conn->prepare($sql_user);

if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user && $result_user->num_rows > 0) {
        $row_user = $result_user->fetch_assoc();

        $user['first_name'] = $row_user['first_name'] ?? '';
        $user['last_name'] = $row_user['last_name'] ?? '';
        $user['email'] = $row_user['email'] ?? '';
        $user['contact_number'] = $row_user['contact_number'] ?? '';
        $user['address'] = $row_user['address'] ?? '';
    }

    $stmt_user->close();
}

/* =========================
   FETCH ASSIGNED CDCs
========================= */
$assigned_cdcs = [];

$sql_cdc = "SELECT c.cdc_name
            FROM cdw_assignments ca
            INNER JOIN cdc c ON ca.cdc_id = c.cdc_id
            WHERE ca.user_id = ?
            ORDER BY c.cdc_name ASC";
$stmt_cdc = $conn->prepare($sql_cdc);

if ($stmt_cdc) {
    $stmt_cdc->bind_param("i", $user_id);
    $stmt_cdc->execute();
    $result_cdc = $stmt_cdc->get_result();

    while ($row_cdc = $result_cdc->fetch_assoc()) {
        $assigned_cdcs[] = $row_cdc['cdc_name'];
    }

    $stmt_cdc->close();
}

$cdc_count = count($assigned_cdcs);
$cdc_names = ($cdc_count > 0) ? implode(', ', $assigned_cdcs) : 'No assigned CDC';
$assigned_cdc_display = $cdc_count . ' Assigned CDC' . ($cdc_count > 1 ? 's' : '') . ' (' . $cdc_names . ')';

$theme_mode = $_SESSION['theme_mode'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | NutriTrack</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/cdw-style.css">
    <link rel="stylesheet" href="../assets/cdw-topbar-notification.css">

    <style>
        .main-content{
            margin-left:260px;
            padding:112px 24px 30px;
            transition:margin-left 0.25s ease;
        }

        .main-content.full{
            margin-left:0;
        }

        .page-header{
            background:#ffffff;
            border:1px solid #dcdcdc;
            border-radius:14px;
            padding:22px 24px;
            margin-bottom:18px;
        }

        .back-link{
            display:inline-flex;
            align-items:center;
            gap:8px;
            margin-bottom:10px;
            font-size:13px;
            font-weight:600;
            color:#2E7D32;
        }

        .page-title{
            font-family:'Poppins', sans-serif;
            font-size:24px;
            font-weight:700;
            color:#2f2f2f;
            margin-bottom:6px;
        }

        .page-subtitle{
            font-size:13px;
            color:#666;
            line-height:1.6;
        }

        .settings-grid{
            display:grid;
            grid-template-columns:1.1fr 0.9fr;
            gap:18px;
        }

        .content-card{
            background:#ffffff;
            border:1px solid #dcdcdc;
            border-radius:14px;
            padding:20px;
        }

        .section-title{
            font-family:'Poppins', sans-serif;
            font-size:18px;
            font-weight:700;
            color:#2f2f2f;
            margin-bottom:6px;
        }

        .section-subtitle{
            font-size:13px;
            color:#666;
            line-height:1.6;
            margin-bottom:18px;
        }

        .settings-form-grid{
            display:grid;
            grid-template-columns:repeat(2, 1fr);
            gap:14px;
        }

        .form-group label{
            display:block;
            font-size:12px;
            color:#666;
            margin-bottom:6px;
            font-weight:500;
        }

        .form-control{
            width:100%;
            border:1px solid #cfcfcf;
            border-radius:8px;
            padding:11px 12px;
            font-size:13px;
            font-family:'Inter', sans-serif;
            background:#fff;
            color:#333;
            outline:none;
        }

        .form-control:focus{
            border-color:#2E7D32;
            box-shadow:0 0 0 3px rgba(46,125,50,0.08);
        }

        .form-control[readonly]{
            background:#f5f5f5;
            cursor:not-allowed;
        }

        .full-width{
            grid-column:1 / -1;
        }

        textarea.form-control{
            min-height:105px;
            resize:vertical;
        }

        .button-group{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            margin-top:18px;
        }

        .btn{
            border:none;
            border-radius:8px;
            padding:11px 16px;
            font-size:13px;
            font-weight:600;
            font-family:'Inter', sans-serif;
            cursor:pointer;
            display:inline-flex;
            align-items:center;
            justify-content:center;
        }

        .btn-save{
            background:#2E7D32;
            color:#fff;
        }

        .success-message{
            background:#eaf7ee;
            color:#1f7a46;
            border:1px solid #c8e6d0;
            border-radius:10px;
            padding:14px 16px;
            margin-bottom:16px;
            font-size:13px;
            font-weight:600;
        }

        .error-message{
            background:#fdeaea;
            color:#c62828;
            border:1px solid #f5c2c7;
            border-radius:10px;
            padding:14px 16px;
            margin-bottom:16px;
            font-size:13px;
            font-weight:600;
        }

        .appearance-box{
            border:1px solid #e3e3e3;
            border-radius:12px;
            background:#fafafa;
            padding:16px;
        }

        .appearance-title{
            font-family:'Poppins', sans-serif;
            font-size:17px;
            font-weight:700;
            color:#2f2f2f;
            margin-bottom:8px;
        }

        .appearance-note{
            font-size:13px;
            color:#666;
            line-height:1.6;
            margin-bottom:18px;
        }

        .toggle-row{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            margin-bottom:16px;
        }

        .toggle-label{
            font-size:14px;
            font-weight:600;
            color:#2f2f2f;
        }

        .switch{
            position:relative;
            display:inline-block;
            width:56px;
            height:30px;
        }

        .switch input{
            opacity:0;
            width:0;
            height:0;
        }

        .slider{
            position:absolute;
            cursor:pointer;
            inset:0;
            background:#cfcfcf;
            transition:.3s;
            border-radius:30px;
        }

        .slider:before{
            content:"";
            position:absolute;
            height:22px;
            width:22px;
            left:4px;
            top:4px;
            background:#fff;
            transition:.3s;
            border-radius:50%;
        }

        .switch input:checked + .slider{
            background:#2E7D32;
        }

        .switch input:checked + .slider:before{
            transform:translateX(26px);
        }

        .preference-table{
            width:100%;
            border-collapse:collapse;
            margin-top:16px;
        }

        .preference-table th,
        .preference-table td{
            border:1px solid #ebebeb;
            padding:12px 14px;
            text-align:left;
            font-size:13px;
            color:#2f2f2f;
        }

        .preference-table th{
            width:34%;
            background:#f7f7f7;
            font-weight:600;
        }

        body.dark-mode{
            background:#0f172a;
            color:#e5e7eb;
        }

        body.dark-mode .page-header,
        body.dark-mode .content-card,
        body.dark-mode .appearance-box{
            background:#111827;
            border-color:#334155;
        }

        body.dark-mode .page-title,
        body.dark-mode .section-title,
        body.dark-mode .appearance-title,
        body.dark-mode .toggle-label,
        body.dark-mode .preference-table th,
        body.dark-mode .preference-table td{
            color:#f8fafc;
        }

        body.dark-mode .page-subtitle,
        body.dark-mode .section-subtitle,
        body.dark-mode .appearance-note,
        body.dark-mode .form-group label{
            color:#cbd5e1;
        }

        body.dark-mode .form-control{
            background:#0f172a;
            color:#f8fafc;
            border-color:#475569;
        }

        body.dark-mode .form-control[readonly]{
            background:#1e293b;
        }

        body.dark-mode .preference-table th{
            background:#1e293b;
            border-color:#334155;
        }

        body.dark-mode .preference-table td{
            border-color:#334155;
        }

        body.dark-mode .slider{
            background:#475569;
        }

        @media (max-width: 991px){
            .main-content{
                margin-left:0;
                padding:104px 16px 24px;
            }

            .settings-grid{
                grid-template-columns:1fr;
            }

            .settings-form-grid{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body class="<?php echo ($theme_mode === 'dark') ? 'dark-mode' : ''; ?>">

<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-header">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1 class="page-title">Settings</h1>
        <div class="page-subtitle">Manage your profile information and appearance preference.</div>
    </div>

    <?php if ($success != '') { ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <?php if ($error != '') { ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <div class="settings-grid">
        <div class="content-card">
            <div class="section-title">Profile Information</div>
            <div class="section-subtitle">
                The information entered by CSWD is shown below. You may also edit your profile information here.
            </div>

            <form method="POST">
                <div class="settings-form-grid">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" class="form-control" value="CDW" readonly>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($user['contact_number']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Assigned CDC</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($assigned_cdc_display); ?>" readonly>
                    </div>

                    <div class="form-group full-width">
                        <label>Address</label>
                        <textarea name="address" class="form-control"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" name="save_profile" class="btn btn-save">Save Changes</button>
                </div>
            </form>
        </div>

        <div class="content-card">
            <div class="section-title">Appearance Preference</div>
            <div class="section-subtitle">
                Choose how your CDW pages will look during your current session.
            </div>

            <form method="POST">
                <div class="appearance-box">
                    <div class="appearance-title">Dark Mode</div>
                    <div class="appearance-note">
                        Turn this on if you want a darker interface while using the system.
                    </div>

                    <div class="toggle-row">
                        <div class="toggle-label" id="themeLabel">
                            <?php echo ($theme_mode === 'dark') ? 'Dark Mode Enabled' : 'Light Mode Enabled'; ?>
                        </div>

                        <label class="switch">
                            <input type="checkbox" id="themeToggle" <?php echo ($theme_mode === 'dark') ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <input type="hidden" name="theme_mode" id="theme_mode" value="<?php echo htmlspecialchars($theme_mode); ?>">

                    <div class="button-group">
                        <button type="submit" name="save_theme" class="btn btn-save">Save Appearance</button>
                    </div>
                </div>

                <table class="preference-table">
                    <tr>
                        <th>Preference</th>
                        <td><?php echo ($theme_mode === 'dark') ? 'Dark Mode' : 'Light Mode'; ?></td>
                    </tr>
                    <tr>
                        <th>Applies To</th>
                        <td>All CDW pages during the current session</td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var mainContent = document.getElementById('mainContent');

    if (window.innerWidth <= 991) {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    } else {
        sidebar.classList.toggle('closed');
        mainContent.classList.toggle('full');
    }
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

const themeToggle = document.getElementById('themeToggle');
const themeModeInput = document.getElementById('theme_mode');
const themeLabel = document.getElementById('themeLabel');
const body = document.body;

themeToggle.addEventListener('change', function () {
    if (this.checked) {
        body.classList.add('dark-mode');
        themeModeInput.value = 'dark';
        themeLabel.textContent = 'Dark Mode Enabled';
    } else {
        body.classList.remove('dark-mode');
        themeModeInput.value = 'light';
        themeLabel.textContent = 'Light Mode Enabled';
    }
});
</script>

</body>
</html>