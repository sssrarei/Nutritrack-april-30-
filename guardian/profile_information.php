<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/auth.php';
include '../config/database.php';

checkRole(3);

if (!isset($_SESSION['theme_mode'])) {
    $_SESSION['theme_mode'] = 'light';
}

$current_page = 'profile_information';
$user_id = (int)$_SESSION['user_id'];

$message = '';
$message_type = '';

$user = null;

/*
|--------------------------------------------------------------------------
| LOAD GUARDIAN PROFILE
| Priority:
| 1. users table
| 2. fallback to guardians table (linked child)
|--------------------------------------------------------------------------
*/
$load_sql = "
    SELECT
        u.first_name,
        u.last_name,
        u.email,
        COALESCE(NULLIF(u.contact_number, ''), g.contact_number) AS contact_number,
        COALESCE(NULLIF(u.address, ''), g.address) AS address
    FROM users u
    LEFT JOIN parent_child_links pcl ON pcl.parent_id = u.user_id
    LEFT JOIN guardians g ON g.child_id = pcl.child_id
    WHERE u.user_id = ?
    LIMIT 1
";

$load_stmt = $conn->prepare($load_sql);

if ($load_stmt) {
    $load_stmt->bind_param("i", $user_id);
    $load_stmt->execute();
    $result = $load_stmt->get_result();
    $user = $result->fetch_assoc();
    $load_stmt->close();
}

if (!$user) {
    die("Guardian account not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($first_name === '' || $last_name === '' || $email === '') {
        $message = 'First name, last name, and email are required.';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'error';
    } else {
        $check_email_sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ? LIMIT 1";
        $check_email_stmt = $conn->prepare($check_email_sql);

        if ($check_email_stmt) {
            $check_email_stmt->bind_param("si", $email, $user_id);
            $check_email_stmt->execute();
            $email_result = $check_email_stmt->get_result();
            $email_exists = $email_result->num_rows > 0;
            $check_email_stmt->close();

            if ($email_exists) {
                $message = 'That email address is already being used by another account.';
                $message_type = 'error';
            } else {
                $update_sql = "
                    UPDATE users
                    SET first_name = ?, last_name = ?, email = ?, contact_number = ?, address = ?
                    WHERE user_id = ?
                ";
                $update_stmt = $conn->prepare($update_sql);

                if ($update_stmt) {
                    $update_stmt->bind_param("sssssi", $first_name, $last_name, $email, $contact_number, $address, $user_id);

                    if ($update_stmt->execute()) {
                        $_SESSION['first_name'] = $first_name;
                        $_SESSION['last_name'] = $last_name;
                        $_SESSION['email'] = $email;

                        $message = 'Profile information updated successfully.';
                        $message_type = 'success';

                        $user['first_name'] = $first_name;
                        $user['last_name'] = $last_name;
                        $user['email'] = $email;
                        $user['contact_number'] = $contact_number;
                        $user['address'] = $address;
                    } else {
                        $message = 'Failed to update profile information.';
                        $message_type = 'error';
                    }

                    $update_stmt->close();
                } else {
                    $message = 'Failed to prepare profile update.';
                    $message_type = 'error';
                }
            }
        } else {
            $message = 'Failed to validate email address.';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Information | NutriTrack</title>
    <link rel="stylesheet" href="../assets/guardian-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .profile-shell{
            max-width:900px;
            margin:0 auto;
            width:100%;
        }

        .profile-card{
            background:#ffffff;
            border:1px solid #d7dde5;
            border-radius:22px;
            overflow:hidden;
            box-shadow:0 10px 30px rgba(15, 23, 42, 0.05);
        }

        .profile-card-header{
            background:#f5ede5;
            padding:20px 24px;
            border-bottom:1px solid #e7e7e7;
        }

        .profile-card-title{
            font-family:'Poppins', sans-serif;
            font-size:20px;
            font-weight:700;
            color:#c96f00;
        }

        .profile-card-subtitle{
            margin-top:6px;
            font-size:13px;
            color:#7b8794;
            font-family:'Inter', sans-serif;
        }

        .profile-card-body{
            padding:24px;
        }

        .message-box{
            margin-bottom:18px;
            padding:14px 16px;
            border-radius:12px;
            font-size:14px;
            font-weight:600;
            line-height:1.6;
        }

        .message-box.success{
            background:#e8f5e9;
            color:#2e7d32;
            border:1px solid #c8e6c9;
        }

        .message-box.error{
            background:#fdeaea;
            color:#b42318;
            border:1px solid #efb0b0;
        }

        .profile-form{
            display:grid;
            grid-template-columns:repeat(2, minmax(0, 1fr));
            gap:18px 20px;
        }

        .form-group{
            display:flex;
            flex-direction:column;
            gap:7px;
        }

        .form-group.full{
            grid-column:1 / -1;
        }

        .form-label{
            font-size:13px;
            font-weight:600;
            color:#555;
            font-family:'Inter', sans-serif;
        }

        .form-input{
            width:100%;
            border:1px solid #cfcfcf;
            border-radius:12px;
            padding:12px 14px;
            font-family:'Inter', sans-serif;
            font-size:14px;
            color:#243041;
            background:#ffffff;
            outline:none;
        }

        textarea.form-input{
            min-height:110px;
            resize:vertical;
        }

        .form-input:focus{
            border-color:#c96f00;
            box-shadow:0 0 0 3px rgba(201,111,0,0.08);
        }

        .form-help{
            font-size:12px;
            color:#6b7280;
            line-height:1.5;
        }

        .form-actions{
            grid-column:1 / -1;
            margin-top:4px;
            display:flex;
            justify-content:flex-start;
        }

        .btn-save-profile{
            border:none;
            border-radius:10px;
            padding:12px 18px;
            background:#c96f00;
            color:#ffffff;
            font-size:14px;
            font-weight:600;
            font-family:'Inter', sans-serif;
            cursor:pointer;
            transition:0.2s ease;
        }

        .btn-save-profile:hover{
            background:#a95d00;
        }

        body.dark-mode .profile-card{
            background:#111827;
            border-color:#334155;
        }

        body.dark-mode .profile-card-header{
            background:#1e293b;
            border-bottom-color:#334155;
        }

        body.dark-mode .profile-card-title{
            color:#f8fafc;
        }

        body.dark-mode .profile-card-subtitle,
        body.dark-mode .form-label,
        body.dark-mode .form-help{
            color:#cbd5e1;
        }

        body.dark-mode .form-input{
            background:#0f172a;
            color:#f8fafc;
            border-color:#475569;
        }

        @media (max-width: 768px){
            .profile-card-header,
            .profile-card-body{
                padding:18px;
            }

            .profile-form{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body class="<?php echo (isset($_SESSION['theme_mode']) && $_SESSION['theme_mode'] === 'dark') ? 'dark-mode' : ''; ?>">

<?php include '../includes/guardian_topbar.php'; ?>
<?php include '../includes/guardian_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="profile-shell">
        <div class="profile-card">
            <div class="profile-card-header">
                <h1 class="profile-card-title">Profile Information</h1>
                <div class="profile-card-subtitle">Update your guardian account details.</div>
            </div>

            <div class="profile-card-body">
                <?php if (!empty($message)) { ?>
                    <div class="message-box <?php echo htmlspecialchars($message_type); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php } ?>

                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input
                            type="text"
                            name="first_name"
                            id="first_name"
                            class="form-input"
                            value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input
                            type="text"
                            name="last_name"
                            id="last_name"
                            class="form-input"
                            value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <div class="form-group full">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-input"
                            value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input
                            type="text"
                            name="contact_number"
                            id="contact_number"
                            class="form-input"
                            value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>"
                        >
                    </div>

                    <div class="form-group full">
                        <label for="address" class="form-label">Address</label>
                        <textarea
                            name="address"
                            id="address"
                            class="form-input"
                        ><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save-profile">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const mainContent = document.getElementById('mainContent');

function handleDesktopToggle() {
    if (!sidebar || !mainContent) return;
    sidebar.classList.toggle('hidden');
    mainContent.classList.toggle('full');
}

function handleMobileToggle() {
    if (!sidebar || !sidebarOverlay) return;
    sidebar.classList.toggle('show');
    sidebarOverlay.classList.toggle('show');
}

if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', function () {
        if (window.innerWidth <= 991) {
            handleMobileToggle();
        } else {
            handleDesktopToggle();
        }
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function () {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });
}

window.addEventListener('resize', function () {
    if (window.innerWidth > 991) {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    }
});
</script>

</body>
</html>