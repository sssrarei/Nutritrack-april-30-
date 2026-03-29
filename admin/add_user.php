<?php
include '../includes/auth.php';
include '../config/database.php';
checkRole(1);

$success = "";
$error = "";

if(isset($_POST['add_user'])){
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $cdc_ids = isset($_POST['cdc_ids']) ? $_POST['cdc_ids'] : [];

    if($first_name == "" || $last_name == "" || $email == "" || $password == "" || $confirm_password == ""){
        $error = "Please fill in all required fields.";
    } elseif($password != $confirm_password){
        $error = "Password and confirm password do not match.";
    } elseif(empty($cdc_ids)){
        $error = "Please select at least one CDC assignment.";
    } else {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if($check_result && $check_result->num_rows > 0){
            $error = "Email already exists.";
        } else {
            $role_id = 2;

            $insert_user = $conn->prepare("INSERT INTO users (role_id, first_name, last_name, email, password) VALUES (?, ?, ?, ?, ?)");
            $insert_user->bind_param("issss", $role_id, $first_name, $last_name, $email, $password);

            if($insert_user->execute()){
                $new_user_id = $conn->insert_id;

                $assign_stmt = $conn->prepare("INSERT INTO cdw_assignments (user_id, cdc_id) VALUES (?, ?)");

                foreach($cdc_ids as $cdc_id){
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
$total_users = $total_users_result->fetch_assoc()['total_users'];

$total_cdw_result = $conn->query("SELECT COUNT(*) AS total_cdw FROM users WHERE role_id = 2");
$total_cdw = $total_cdw_result->fetch_assoc()['total_cdw'];

$total_guardian_result = $conn->query("SELECT COUNT(*) AS total_guardian FROM users WHERE role_id = 3");
$total_guardian = $total_guardian_result->fetch_assoc()['total_guardian'];

$cdc_list = $conn->query("SELECT cdc_id, cdc_name, barangay FROM cdc ORDER BY cdc_name ASC");

if($search != ""){
    $like = "%" . $search . "%";
    $users_stmt = $conn->prepare("
        SELECT user_id, first_name, last_name, email, role_id, created_at, last_active
        FROM users
        WHERE role_id IN (2,3)
        AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)
        ORDER BY user_id DESC
    ");
    $users_stmt->bind_param("sss", $like, $like, $like);
    $users_stmt->execute();
    $users_result = $users_stmt->get_result();
} else {
    $users_result = $conn->query("
        SELECT user_id, first_name, last_name, email, role_id, created_at, last_active
        FROM users
        WHERE role_id IN (2,3)
        ORDER BY user_id DESC
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #ececf1;
            color: #333;
        }

        .page-header {
            background: #dcdcdc;
            border-bottom: 1px solid #bdbdbd;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 18px;
            font-weight: bold;
        }

        .back-link {
            text-decoration: none;
            color: #555;
            font-size: 24px;
            line-height: 1;
        }

        .container {
            padding: 24px 32px 30px 32px;
        }

        .top-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .summary-left {
            display: flex;
            align-items: center;
            gap: 50px;
            flex-wrap: wrap;
        }

        .summary-item {
            font-size: 18px;
            font-weight: bold;
        }

        .summary-small {
            font-size: 14px;
            font-weight: bold;
            color: #444;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .search-box {
            width: 300px;
            padding: 10px 14px;
            font-size: 16px;
            border: 2px solid #7e7e7e;
            outline: none;
        }

        .open-form-btn {
            background: #3498db;
            color: #fff;
            border: 1px solid #2f7fb3;
            padding: 11px 18px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }

        .message-success {
            background: #eaf7ea;
            color: #1c6b1c;
            border: 1px solid #b7ddb7;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-weight: bold;
        }

        .message-error {
            background: #fdeaea;
            color: #b30000;
            border: 1px solid #efb0b0;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-weight: bold;
        }

        .form-card {
            background: #fff;
            border: 1px solid #c8c8c8;
            padding: 24px;
            margin-bottom: 24px;
            display: none;
        }

        .form-card.show {
            display: block;
        }

        .form-title {
            margin: 0 0 18px 0;
            font-size: 24px;
            font-weight: bold;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 15px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            padding: 11px 12px;
            border: 1px solid #9e9e9e;
            font-size: 15px;
            outline: none;
            background: #fff;
        }

        select[multiple] {
            min-height: 150px;
        }

        .form-note {
            margin-top: 6px;
            font-size: 13px;
            color: #666;
        }

        .form-buttons {
            margin-top: 20px;
            display: flex;
            gap: 12px;
        }

        .save-btn {
            background: #3498db;
            color: #fff;
            border: none;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .cancel-btn {
            background: #e0e0e0;
            color: #333;
            border: 1px solid #bcbcbc;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .table-card {
            background: #fff;
            border: 1px solid #c8c8c8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #ececec;
        }

        th, td {
            padding: 16px 14px;
            text-align: left;
            border-bottom: 1px solid #e4e4e4;
            vertical-align: top;
        }

        th {
            font-size: 15px;
            font-weight: bold;
        }

        td {
            font-size: 14px;
        }

        .name-cell {
            font-weight: bold;
            font-size: 16px;
        }

        .email-text {
            color: #555;
            margin-top: 4px;
        }

        .role-badge {
            font-weight: bold;
        }

        .empty-text {
            padding: 24px;
            text-align: center;
            color: #666;
            font-weight: bold;
        }

        @media (max-width: 900px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .search-box {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="page-header">
    <a href="dashboard.php" class="back-link">◀</a>
    <span>User Management</span>
</div>

<div class="container">

    <div class="top-summary">
        <div class="summary-left">
            <div class="summary-item">All User <?php echo $total_users; ?></div>
            <div class="summary-small"><?php echo $total_cdw; ?> CDW account</div>
            <div class="summary-small"><?php echo $total_guardian; ?> guardian account</div>
        </div>

        <div class="top-actions">
            <form method="GET">
                <input type="text" name="search" class="search-box" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
            </form>
            <button type="button" class="open-form-btn" onclick="openAddUserForm()">+ Add User</button>
        </div>
    </div>

    <?php if($success != ""){ ?>
        <div class="message-success"><?php echo $success; ?></div>
    <?php } ?>

    <?php if($error != ""){ ?>
        <div class="message-error"><?php echo $error; ?></div>
    <?php } ?>

    <div class="form-card <?php echo ($error != '' || $success != '') ? 'show' : ''; ?>" id="addUserForm">
        <h2 class="form-title">Add CDW Account</h2>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                </div>

                <div class="form-group full">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <div class="form-group full">
                    <label>Select CDC Assignment</label>
                    <select name="cdc_ids[]" multiple required>
                        <?php
                        if($cdc_list && $cdc_list->num_rows > 0){
                            while($cdc = $cdc_list->fetch_assoc()){
                                $selected = "";
                                if(isset($_POST['cdc_ids']) && in_array($cdc['cdc_id'], $_POST['cdc_ids'])){
                                    $selected = "selected";
                                }
                                echo "<option value='" . $cdc['cdc_id'] . "' $selected>" .
                                        htmlspecialchars($cdc['cdc_name'] . " - " . $cdc['barangay']) .
                                     "</option>";
                            }
                        }
                        ?>
                    </select>
                    <div class="form-note">Hold Ctrl to select multiple CDC.</div>
                </div>
            </div>

            <div class="form-buttons">
                <button type="submit" name="add_user" class="save-btn">Save CDW Account</button>
                <button type="button" class="cancel-btn" onclick="closeAddUserForm()">Cancel</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th style="width:45%;">User name</th>
                    <th style="width:15%;">Role</th>
                    <th style="width:20%;">Last active</th>
                    <th style="width:20%;">Date Added</th>
                </tr>
            </thead>
            <tbody>
                <?php if($users_result && $users_result->num_rows > 0){ ?>
                    <?php while($user = $users_result->fetch_assoc()){ ?>
                        <tr>
                            <td>
                                <div class="name-cell">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </div>
                                <div class="email-text">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </td>
                            <td class="role-badge">
                                <?php
                                if($user['role_id'] == 2){
                                    echo "CDW";
                                } elseif($user['role_id'] == 3){
                                    echo "Guardian";
                                } else {
                                    echo "Unknown";
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($user['last_active']) && $user['last_active'] != '0000-00-00 00:00:00'){
                                    echo date("F d, Y g:i A", strtotime($user['last_active']));
                                } else {
                                    echo "—";
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(!empty($user['created_at']) && $user['created_at'] != '0000-00-00 00:00:00'){
                                    echo date("F d, Y", strtotime($user['created_at']));
                                } else {
                                    echo "—";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4" class="empty-text">No users found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>

<script>
function openAddUserForm() {
    document.getElementById('addUserForm').classList.add('show');
    document.getElementById('addUserForm').scrollIntoView({ behavior: 'smooth' });
}

function closeAddUserForm() {
    document.getElementById('addUserForm').classList.remove('show');
}
</script>

</body>
</html>