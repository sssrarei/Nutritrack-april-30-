<?php
include '../includes/auth.php';
include '../config/database.php';
checkRole(1);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Information</title>
    <link rel="stylesheet" href="../assets/admin-style.css">
    <link rel="stylesheet" href="../assets/admin_settings.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
        <div class="card-header">
            <h2>Account Details</h2>
            <p>Make sure your profile information is accurate and up to date.</p>
        </div>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        required
                        value="<?php echo htmlspecialchars($user['first_name']); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        required
                        value="<?php echo htmlspecialchars($user['last_name']); ?>"
                    >
                </div>

                <div class="form-group full">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        value="<?php echo htmlspecialchars($user['email']); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input
                        type="text"
                        id="contact_number"
                        name="contact_number"
                        value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"
                    >
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                <a href="change_password.php" class="btn btn-light">Change Password</a>
            </div>
        </form>
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
    if (window.innerWidth > 991 && sidebar && sidebarOverlay) {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    }
});
</script>

</body>
</html>