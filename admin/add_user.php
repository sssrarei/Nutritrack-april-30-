<?php
include '../includes/auth.php';
include '../config/database.php';
checkRole(1);

$success = "";
$error = "";

if (isset($_POST['add_user'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $cdc_ids = isset($_POST['cdc_ids']) ? $_POST['cdc_ids'] : [];

    if (
        $first_name == "" || $last_name == "" || $email == "" ||
        $contact_number == "" || $address == "" ||
        $password == "" || $confirm_password == ""
    ) {
        $error = "Please fill in all required fields.";
    } elseif ($password != $confirm_password) {
        $error = "Password and confirm password do not match.";
    } elseif (empty($cdc_ids)) {
        $error = "Please select at least one CDC assignment.";
    } else {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result && $check_result->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            $role_id = 2;

            $insert_user = $conn->prepare("
                INSERT INTO users (role_id, first_name, last_name, email, contact_number, address, password)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $insert_user->bind_param(
                "issssss",
                $role_id,
                $first_name,
                $last_name,
                $email,
                $contact_number,
                $address,
                $password
            );

            if ($insert_user->execute()) {
                $new_user_id = $conn->insert_id;

                $assign_stmt = $conn->prepare("INSERT INTO cdw_assignments (user_id, cdc_id) VALUES (?, ?)");

                foreach ($cdc_ids as $cdc_id) {
                    $cdc_id = (int)$cdc_id;
                    $assign_stmt->bind_param("ii", $new_user_id, $cdc_id);
                    $assign_stmt->execute();
                }

                $success = "CDW account added successfully.";
                $_POST = [];
            } else {
                $error = "Failed to add CDW account.";
            }
        }
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

$total_users_result = $conn->query("SELECT COUNT(*) AS total_users FROM users WHERE role_id IN (2,3)");
$total_users = $total_users_result ? (int)$total_users_result->fetch_assoc()['total_users'] : 0;

$total_cdw_result = $conn->query("SELECT COUNT(*) AS total_cdw FROM users WHERE role_id = 2");
$total_cdw = $total_cdw_result ? (int)$total_cdw_result->fetch_assoc()['total_cdw'] : 0;

$total_guardian_result = $conn->query("SELECT COUNT(*) AS total_guardian FROM users WHERE role_id = 3");
$total_guardian = $total_guardian_result ? (int)$total_guardian_result->fetch_assoc()['total_guardian'] : 0;

$cdc_list = $conn->query("SELECT cdc_id, cdc_name, barangay FROM cdc ORDER BY cdc_name ASC");

if ($search != "") {
    $like = "%" . $search . "%";
    $users_stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.contact_number,
            u.address,
            u.role_id,
            u.created_at,
            u.last_active,
            GROUP_CONCAT(DISTINCT c.cdc_name ORDER BY c.cdc_name ASC SEPARATOR ', ') AS assigned_cdc,
            GROUP_CONCAT(
                DISTINCT CONCAT(ch.first_name, ' ', ch.last_name)
                ORDER BY ch.first_name ASC, ch.last_name ASC
                SEPARATOR ', '
            ) AS linked_children
        FROM users u
        LEFT JOIN cdw_assignments ca ON u.user_id = ca.user_id
        LEFT JOIN cdc c ON ca.cdc_id = c.cdc_id
        LEFT JOIN parent_child_links pcl ON u.user_id = pcl.parent_id
        LEFT JOIN children ch ON pcl.child_id = ch.child_id
        WHERE u.role_id IN (2,3)
        AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)
        GROUP BY u.user_id, u.first_name, u.last_name, u.email, u.contact_number, u.address, u.role_id, u.created_at, u.last_active
        ORDER BY u.user_id DESC
    ");
    $users_stmt->bind_param("sss", $like, $like, $like);
    $users_stmt->execute();
    $users_result = $users_stmt->get_result();
} else {
    $users_result = $conn->query("
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.contact_number,
            u.address,
            u.role_id,
            u.created_at,
            u.last_active,
            GROUP_CONCAT(DISTINCT c.cdc_name ORDER BY c.cdc_name ASC SEPARATOR ', ') AS assigned_cdc,
            GROUP_CONCAT(
                DISTINCT CONCAT(ch.first_name, ' ', ch.last_name)
                ORDER BY ch.first_name ASC, ch.last_name ASC
                SEPARATOR ', '
            ) AS linked_children
        FROM users u
        LEFT JOIN cdw_assignments ca ON u.user_id = ca.user_id
        LEFT JOIN cdc c ON ca.cdc_id = c.cdc_id
        LEFT JOIN parent_child_links pcl ON u.user_id = pcl.parent_id
        LEFT JOIN children ch ON pcl.child_id = ch.child_id
        WHERE u.role_id IN (2,3)
        GROUP BY u.user_id, u.first_name, u.last_name, u.email, u.contact_number, u.address, u.role_id, u.created_at, u.last_active
        ORDER BY u.user_id DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../assets/admin-style.css">
    <link rel="stylesheet" href="../assets/add_user.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="<?php echo (isset($_SESSION['theme_mode']) && $_SESSION['theme_mode'] === 'dark') ? 'dark-mode' : ''; ?>">

<?php include '../includes/admin_sidebar.php'; ?>
<?php include '../includes/admin_topbar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-header">
        <h1>User Management</h1>
        <p>Manage CDW and guardian accounts using the CSWD admin interface.</p>
    </div>

    <?php if ($success != "") { ?>
        <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">All Users</div>
            <div class="summary-value"><?php echo $total_users; ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">CDW Accounts</div>
            <div class="summary-value"><?php echo $total_cdw; ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Guardian Accounts</div>
            <div class="summary-value"><?php echo $total_guardian; ?></div>
        </div>
    </div>

    <div class="toolbar-card">
        <form method="GET" class="search-form">
            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search name or email"
                value="<?php echo htmlspecialchars($search); ?>"
            >
            <button type="submit" class="btn btn-secondary">Search</button>
            <a href="add_user.php" class="btn btn-light">Reset</a>
        </form>

        <button type="button" class="btn btn-primary" onclick="openAddUserForm()">Add User</button>
    </div>

    <div class="form-card <?php echo ($error != '' || $success != '') ? 'show' : ''; ?>" id="addUserForm">
        <div class="card-header">
            <h2>Add CDW Account</h2>
            <p>Enter the account details and assign at least one CDC.</p>
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
                        value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        required
                        value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                    >
                </div>

                <div class="form-group full">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input
                        type="text"
                        id="contact_number"
                        name="contact_number"
                        required
                        value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        required
                        value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-group full">
                    <label>Select CDC Assignment</label>
                    <div class="checkbox-group">
                        <?php
                        if ($cdc_list && $cdc_list->num_rows > 0) {
                            while ($cdc = $cdc_list->fetch_assoc()) {
                                $checked = "";
                                if (isset($_POST['cdc_ids']) && in_array($cdc['cdc_id'], $_POST['cdc_ids'])) {
                                    $checked = "checked";
                                }

                                echo "<div class='checkbox-item'>" .
                                    "<input type='checkbox' name='cdc_ids[]' id='cdc_" . $cdc['cdc_id'] . "' value='" . $cdc['cdc_id'] . "' $checked>" .
                                    "<label for='cdc_" . $cdc['cdc_id'] . "'>" . htmlspecialchars($cdc['cdc_name'] . " - " . $cdc['barangay']) . "</label>" .
                                    "</div>";
                            }
                        }
                        ?>
                    </div>
                    <div class="form-note">Select at least one CDC assignment.</div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="add_user" class="btn btn-primary">Save CDW Account</button>
                <button type="button" class="btn btn-light" onclick="closeAddUserForm()">Cancel</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="card-header">
            <h2>User List</h2>
            <p>View all CDW and guardian accounts.</p>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 45%;">User Name</th>
                        <th style="width: 25%;">Last Active</th>
                        <th style="width: 20%;">Date Added</th>
                        <th style="width: 10%; text-align:center;">View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users_result && $users_result->num_rows > 0) { ?>
                        <?php while ($user = $users_result->fetch_assoc()) { ?>
                            <?php
                                $full_name = trim($user['first_name'] . ' ' . $user['last_name']);
                                $role_name = ($user['role_id'] == 2) ? 'CDW' : (($user['role_id'] == 3) ? 'Guardian' : 'Unknown');
                                $assigned_cdc = !empty($user['assigned_cdc']) ? $user['assigned_cdc'] : 'No assigned CDC';
                                $linked_children = !empty($user['linked_children']) ? $user['linked_children'] : 'No linked child';
                                $drawer_label = ($user['role_id'] == 2) ? 'Assigned CDC' : 'Linked Child';
                                $drawer_value = ($user['role_id'] == 2) ? $assigned_cdc : $linked_children;
                            ?>
                            <tr>
                                <td>
                                    <div class="name-cell"><?php echo htmlspecialchars($full_name); ?></div>
                                    <div class="email-text"><?php echo htmlspecialchars($user['email']); ?></div>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($user['last_active']) && $user['last_active'] != '0000-00-00 00:00:00') {
                                        echo date("F d, Y g:i A", strtotime($user['last_active']));
                                    } else {
                                        echo "—";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($user['created_at']) && $user['created_at'] != '0000-00-00 00:00:00') {
                                        echo date("F d, Y", strtotime($user['created_at']));
                                    } else {
                                        echo "—";
                                    }
                                    ?>
                                </td>
                                <td class="view-cell">
                                    <button
                                        type="button"
                                        class="view-profile-btn"
                                        data-name="<?php echo htmlspecialchars($full_name, ENT_QUOTES); ?>"
                                        data-role="<?php echo htmlspecialchars($role_name, ENT_QUOTES); ?>"
                                        data-email="<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>"
                                        data-contact="<?php echo htmlspecialchars(!empty($user['contact_number']) ? $user['contact_number'] : '—', ENT_QUOTES); ?>"
                                        data-address="<?php echo htmlspecialchars(!empty($user['address']) ? $user['address'] : '—', ENT_QUOTES); ?>"
                                        data-link-label="<?php echo htmlspecialchars($drawer_label, ENT_QUOTES); ?>"
                                        data-link-value="<?php echo htmlspecialchars($drawer_value, ENT_QUOTES); ?>"
                                        onclick="openProfileDrawer(this)"
                                        aria-label="View Profile"
                                    >
                                        <?php if ($user['role_id'] == 2) { ?>
                                            <svg viewBox="0 0 24 24" class="action-icon" aria-hidden="true">
                                                <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5zm0 2c-3.866 0-7 3.134-7 7h14c0-3.866-3.134-7-7-7z"></path>
                                            </svg>
                                        <?php } else { ?>
                                            <svg viewBox="0 0 24 24" class="action-icon" aria-hidden="true">
                                                <path d="M9 11c2.209 0 4-1.791 4-4S11.209 3 9 3 5 4.791 5 7s1.791 4 4 4zm6 1c1.657 0 3-1.343 3-3s-1.343-3-3-3c-.295 0-.579.043-.848.123A5.978 5.978 0 0 1 15 7c0 1.641-.66 3.128-1.728 4.214.236-.139.511-.214.728-.214zm-6 1c-3.314 0-6 2.686-6 6h12c0-3.314-2.686-6-6-6zm6 1c-.341 0-.676.029-1 .084C15.729 15.11 17 16.943 17 19h5c0-2.761-2.239-5-5-5z"></path>
                                            </svg>
                                        <?php } ?>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="4" class="empty-state">No users found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="drawer-overlay" id="drawerOverlay" onclick="closeProfileDrawer()"></div>

<div class="profile-drawer" id="profileDrawer" aria-hidden="true">
    <div class="drawer-header">
        <h2>Profile Information</h2>
        <button type="button" class="drawer-close" onclick="closeProfileDrawer()" aria-label="Close">
            &times;
        </button>
    </div>

    <div class="drawer-profile-head">
        <div class="drawer-name" id="drawerName">—</div>
        <div class="drawer-role" id="drawerRole">—</div>
    </div>

    <div class="drawer-section">
        <div class="drawer-label">Email Address</div>
        <div class="drawer-value" id="drawerEmail">—</div>
    </div>

    <div class="drawer-section">
        <div class="drawer-label">Contact Number</div>
        <div class="drawer-value" id="drawerContact">—</div>
    </div>

    <div class="drawer-section">
        <div class="drawer-label">Address</div>
        <div class="drawer-value" id="drawerAddress">—</div>
    </div>

    <div class="drawer-section">
        <div class="drawer-label" id="drawerLinkLabel">Assigned CDC</div>
        <div class="drawer-value" id="drawerLinkValue">—</div>
    </div>
</div>

<script>
function openAddUserForm() {
    const form = document.getElementById('addUserForm');
    form.classList.add('show');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function closeAddUserForm() {
    document.getElementById('addUserForm').classList.remove('show');
}

function openProfileDrawer(button) {
    document.getElementById('drawerName').textContent = button.dataset.name || '—';
    document.getElementById('drawerRole').textContent = button.dataset.role || '—';
    document.getElementById('drawerEmail').textContent = button.dataset.email || '—';
    document.getElementById('drawerContact').textContent = button.dataset.contact || '—';
    document.getElementById('drawerAddress').textContent = button.dataset.address || '—';
    document.getElementById('drawerLinkLabel').textContent = button.dataset.linkLabel || 'Assigned CDC';
    document.getElementById('drawerLinkValue').textContent = button.dataset.linkValue || '—';

    document.getElementById('profileDrawer').classList.add('show');
    document.getElementById('drawerOverlay').classList.add('show');
}

function closeProfileDrawer() {
    document.getElementById('profileDrawer').classList.remove('show');
    document.getElementById('drawerOverlay').classList.remove('show');
}

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