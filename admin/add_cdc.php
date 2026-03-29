<?php
include '../includes/auth.php';
include '../config/database.php';
checkRole(1);

$success = "";
$error = "";

// Add CDC
if(isset($_POST['add_cdc'])){
    $cdc_name = trim($_POST['cdc_name']);
    $barangay = trim($_POST['barangay']);
    $address = trim($_POST['address']);

    if($cdc_name == "" || $barangay == "" || $address == ""){
        $error = "Please fill in all required fields.";
    } else {
        $check_stmt = $conn->prepare("SELECT cdc_id FROM cdc WHERE cdc_name = ?");
        $check_stmt->bind_param("s", $cdc_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if($check_result && $check_result->num_rows > 0){
            $error = "CDC name already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO cdc (cdc_name, barangay, address, status) VALUES (?, ?, ?, 'Active')");
            $stmt->bind_param("sss", $cdc_name, $barangay, $address);

            if($stmt->execute()){
                $success = "CDC added successfully!";
                $_POST = [];
            } else {
                $error = "Error adding CDC.";
            }
        }
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// CDC list with child count
if($search != ""){
    $like = "%" . $search . "%";

    $cdc_stmt = $conn->prepare("
        SELECT 
            c.cdc_id,
            c.cdc_name,
            c.barangay,
            c.address,
            COUNT(ch.child_id) AS total_children
        FROM cdc c
        LEFT JOIN children ch ON c.cdc_id = ch.cdc_id
        WHERE c.cdc_name LIKE ? OR c.barangay LIKE ? OR c.address LIKE ?
        GROUP BY c.cdc_id, c.cdc_name, c.barangay, c.address
        ORDER BY c.cdc_id DESC
    ");
    $cdc_stmt->bind_param("sss", $like, $like, $like);
    $cdc_stmt->execute();
    $cdc_result = $cdc_stmt->get_result();
} else {
    $cdc_result = $conn->query("
        SELECT 
            c.cdc_id,
            c.cdc_name,
            c.barangay,
            c.address,
            COUNT(ch.child_id) AS total_children
        FROM cdc c
        LEFT JOIN children ch ON c.cdc_id = ch.cdc_id
        GROUP BY c.cdc_id, c.cdc_name, c.barangay, c.address
        ORDER BY c.cdc_id DESC
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>CDC Management</title>
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

        input[type="text"] {
            padding: 11px 12px;
            border: 1px solid #9e9e9e;
            font-size: 15px;
            outline: none;
            background: #fff;
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

        .child-count {
            font-weight: bold;
            text-align: center;
        }

        .empty-text {
            padding: 24px;
            text-align: center;
            color: #666;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="page-header">
    <a href="dashboard.php" class="back-link">◀</a>
    <span>CDC Management</span>
</div>

<div class="container">

    <div class="top-summary">
        <form method="GET">
            <input type="text" name="search" class="search-box" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
        </form>
        <button type="button" class="open-form-btn" onclick="openAddCdcForm()">+ Add CDC</button>
    </div>

    <?php if($success != ""){ ?>
        <div class="message-success"><?php echo $success; ?></div>
    <?php } ?>

    <?php if($error != ""){ ?>
        <div class="message-error"><?php echo $error; ?></div>
    <?php } ?>

    <div class="form-card <?php echo ($error != '' || $success != '') ? 'show' : ''; ?>" id="addCdcForm">
        <h2 class="form-title">Add CDC</h2>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group full">
                    <label>CDC Name</label>
                    <input type="text" name="cdc_name" required value="<?php echo isset($_POST['cdc_name']) ? htmlspecialchars($_POST['cdc_name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Barangay</label>
                    <input type="text" name="barangay" required value="<?php echo isset($_POST['barangay']) ? htmlspecialchars($_POST['barangay']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" required value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                </div>
            </div>

            <div class="form-buttons">
                <button type="submit" name="add_cdc" class="save-btn">Save CDC</button>
                <button type="button" class="cancel-btn" onclick="closeAddCdcForm()">Cancel</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th style="width:30%;">CDC Name</th>
                    <th style="width:20%;">Barangay</th>
                    <th style="width:35%;">Address</th>
                    <th style="width:15%; text-align:center;">No. of Child</th>
                </tr>
            </thead>
            <tbody>
                <?php if($cdc_result && $cdc_result->num_rows > 0){ ?>
                    <?php while($row = $cdc_result->fetch_assoc()){ ?>
                        <tr>
                            <td class="name-cell"><?php echo htmlspecialchars($row['cdc_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['barangay']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td class="child-count"><?php echo (int)$row['total_children']; ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4" class="empty-text">No CDC found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>

<script>
function openAddCdcForm() {
    document.getElementById('addCdcForm').classList.add('show');
    document.getElementById('addCdcForm').scrollIntoView({ behavior: 'smooth' });
}

function closeAddCdcForm() {
    document.getElementById('addCdcForm').classList.remove('show');
}
</script>

</body>
</html>