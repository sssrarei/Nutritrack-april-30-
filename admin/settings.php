<?php
include '../includes/auth.php';
include '../config/database.php';
checkRole(1);

if (!isset($_SESSION['theme_mode'])) {
    $_SESSION['theme_mode'] = 'light';
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

$stmt = $conn->prepare("SELECT first_name, last_name, email, contact_number, address FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

if (isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);

    if ($first_name == "" || $last_name == "" || $email == "") {
        $error = "Please fill in all required fields.";
    } else {
        $check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ? LIMIT 1");
        $check_email->bind_param("si", $email, $user_id);
        $check_email->execute();
        $check_email_result = $check_email->get_result();

        if ($check_email_result && $check_email_result->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            $update = $conn->prepare("
                UPDATE users
                SET first_name = ?, last_name = ?, email = ?, contact_number = ?, address = ?
                WHERE user_id = ?
            ");
            $update->bind_param("sssssi", $first_name, $last_name, $email, $contact_number, $address, $user_id);

            if ($update->execute()) {
                $success = "Profile information updated successfully.";
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['email'] = $email;

                $user['first_name'] = $first_name;
                $user['last_name'] = $last_name;
                $user['email'] = $email;
                $user['contact_number'] = $contact_number;
                $user['address'] = $address;
            } else {
                $error = "Failed to update profile information.";
            }
        }
    }
}

/* =========================
   SAVE THEME (ADDED)
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_theme'])) {
    $theme_mode = (isset($_POST['theme_mode']) && $_POST['theme_mode'] === 'dark') ? 'dark' : 'light';
    $_SESSION['theme_mode'] = $theme_mode;
    $success = "Appearance preference updated successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Information</title>

    <link rel="stylesheet" href="../assets/admin/admin-style.css">
    <link rel="stylesheet" href="../assets/admin/admin_settings.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* DARK MODE (same logic as CDW) */
        body.dark-mode {
            background:#0f172a;
            color:#e5e7eb;
        }

        body.dark-mode .page-header,
        body.dark-mode .profile-card {
            background:#111827;
            border-color:#334155;
        }

        body.dark-mode h1,
        body.dark-mode h2,
        body.dark-mode p,
        body.dark-mode label {
            color:#f8fafc;
        }

        body.dark-mode input,
        body.dark-mode textarea {
            background:#0f172a;
            color:#f8fafc;
            border-color:#475569;
        }

        body.dark-mode input[readonly] {
            background:#1e293b;
        }

        /* BUTTON ADJUSTMENT (admin akma lang) */
        body.dark-mode .btn-primary {
            background:#2E7D32;
            color:#fff;
        }

        body.dark-mode .btn-light {
            background:#1e293b;
            color:#f8fafc;
            border:1px solid #334155;
        }

        body.dark-mode .alert.success {
            background:#052e1a;
            color:#86efac;
            border:1px solid #14532d;
        }

        body.dark-mode .alert.error {
            background:#3b0a0a;
            color:#fca5a5;
            border:1px solid #7f1d1d;
        }

        /* toggle UI (CDW style copy) */
        .switch {
            position:relative;
            display:inline-block;
            width:56px;
            height:30px;
        }

        .switch input {
            opacity:0;
            width:0;
            height:0;
        }

        .slider {
            position:absolute;
            cursor:pointer;
            inset:0;
            background:#cfcfcf;
            transition:.3s;
            border-radius:30px;
        }

        .slider:before {
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

        .switch input:checked + .slider {
            background:#2E7D32;
        }

        .switch input:checked + .slider:before {
            transform:translateX(26px);
        }

        .toggle-row {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:15px;
        }
    </style>
</head>

<body class="<?php echo (isset($_SESSION['theme_mode']) && $_SESSION['theme_mode'] === 'dark') ? 'dark-mode' : ''; ?>">

<?php include '../includes/admin_sidebar.php'; ?>
<?php include '../includes/admin_topbar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-header">
        <h1>Profile Information</h1>
        <p>View and update your CSWD account information.</p>
    </div>

    <?php if ($success != "") { ?>
        <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <div class="profile-card">

        <!-- PROFILE FORM (unchanged) -->
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>

                <div class="form-group full">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                <a href="change_password.php" class="btn btn-light">Change Password</a>
            </div>
        </form>

        <hr style="margin:25px 0; border:1px solid #ddd;">

        <!-- THEME TOGGLE (ADDED ONLY) -->
        <form method="POST">
            <div class="toggle-row">
                <strong>Dark Mode</strong>

                <label class="switch">
                    <input type="checkbox" id="themeToggle"
                        <?php echo ($_SESSION['theme_mode'] === 'dark') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <input type="hidden" name="theme_mode" id="theme_mode"
                value="<?php echo $_SESSION['theme_mode']; ?>">

            <button type="submit" name="save_theme" class="btn btn-primary">
                Save Appearance
            </button>
        </form>

    </div>
</div>

<script src="../assets/admin/sidebar.js"></script>
</body>
</html>