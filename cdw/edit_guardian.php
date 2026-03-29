<?php
include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 2){
    header("Location: ../login.php");
    exit();
}

if(!isset($_SESSION['active_cdc_id'])){
    die("Please select an active CDC first from the dashboard.");
}

$active_cdc_id = (int) $_SESSION['active_cdc_id'];
$child_id = isset($_GET['child_id']) ? (int) $_GET['child_id'] : 0;

if($child_id <= 0){
    die("Invalid child selected.");
}

$success = "";
$error = "";

// Check if child belongs to active CDC
$child_sql = "SELECT * FROM children
              WHERE child_id = '$child_id'
              AND cdc_id = '$active_cdc_id'
              LIMIT 1";
$child_result = $conn->query($child_sql);

if(!$child_result){
    die("Query error: " . $conn->error);
}

if($child_result->num_rows == 0){
    die("Child not found or not assigned to the active CDC.");
}

$child = $child_result->fetch_assoc();

// Load guardian
$guardian_sql = "SELECT * FROM guardians WHERE child_id = '$child_id' LIMIT 1";
$guardian_result = $conn->query($guardian_sql);
$guardian = ($guardian_result && $guardian_result->num_rows > 0) ? $guardian_result->fetch_assoc() : null;

// Update guardian
if(isset($_POST['update'])){
    $guardian_name = trim($_POST['guardian_name']);
    $relationship_to_child = trim($_POST['relationship_to_child']);
    $guardian_address = trim($_POST['guardian_address']);
    $contact_number = trim($_POST['contact_number']);
    $guardian_email = trim($_POST['guardian_email']);

    $guardian_first_name = "";
    $guardian_middle_name = "";
    $guardian_last_name = "";

    if(!empty($guardian_name)){
        $guardian_parts = preg_split('/\s+/', $guardian_name);

        if(count($guardian_parts) == 1){
            $guardian_first_name = $guardian_parts[0];
        } elseif(count($guardian_parts) == 2){
            $guardian_first_name = $guardian_parts[0];
            $guardian_last_name = $guardian_parts[1];
        } else {
            $guardian_first_name = array_shift($guardian_parts);
            $guardian_last_name = array_pop($guardian_parts);
            $guardian_middle_name = implode(" ", $guardian_parts);
        }
    }

    $child_guardian_update_sql = "UPDATE children SET
        guardian_name = '$guardian_name',
        contact_number = '$contact_number'
        WHERE child_id = '$child_id'
        AND cdc_id = '$active_cdc_id'";

    if(!$conn->query($child_guardian_update_sql)){
        $error = "Error updating child guardian information: " . $conn->error;
    }

    if(empty($error)){
        if($guardian){
            $update_guardian_sql = "UPDATE guardians SET
                first_name = '$guardian_first_name',
                middle_name = '$guardian_middle_name',
                last_name = '$guardian_last_name',
                relationship_to_child = '$relationship_to_child',
                contact_number = '$contact_number',
                email = '$guardian_email',
                address = '$guardian_address'
                WHERE child_id = '$child_id'";

            if($conn->query($update_guardian_sql)){
                $success = "Guardian information updated successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        } else {
            $insert_guardian_sql = "INSERT INTO guardians
                (child_id, first_name, middle_name, last_name, relationship_to_child, contact_number, email, address)
                VALUES
                ('$child_id', '$guardian_first_name', '$guardian_middle_name', '$guardian_last_name', '$relationship_to_child', '$contact_number', '$guardian_email', '$guardian_address')";

            if($conn->query($insert_guardian_sql)){
                $success = "Guardian information updated successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        }

        $guardian_result = $conn->query($guardian_sql);
        $guardian = ($guardian_result && $guardian_result->num_rows > 0) ? $guardian_result->fetch_assoc() : null;
    }
}

$guardian_full_name = "";
if($guardian){
    $guardian_full_name = trim($guardian['first_name'] . ' ' . $guardian['middle_name'] . ' ' . $guardian['last_name']);
} elseif(!empty($child['guardian_name'])) {
    $guardian_full_name = $child['guardian_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Guardian Information | NutriTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/cdw-style.css">

    <style>
        .page-wrapper{
            max-width:1000px;
            margin:0 auto;
        }

        .page-header{
            background:#ffffff;
            border:1px solid #dcdcdc;
            border-radius:12px;
            padding:22px 24px;
            margin-bottom:18px;
        }

        .back-link{
            display:inline-flex;
            align-items:center;
            gap:8px;
            margin-bottom:12px;
            color:#2E7D32;
            font-size:13px;
            font-weight:600;
        }

        .page-title{
            font-family:'Poppins', sans-serif;
            font-size:22px;
            font-weight:700;
            color:#2f2f2f;
            margin:0 0 8px 0;
        }

        .small-label{
            font-size:12px;
            color:#666;
        }

        .message{
            border-radius:10px;
            padding:14px 16px;
            margin-bottom:16px;
            font-size:13px;
            font-weight:600;
        }

        .message.success{
            background:#e8f5e9;
            color:#2e7d32;
            border:1px solid #c8e6c9;
        }

        .message.error{
            background:#fdeaea;
            color:#c62828;
            border:1px solid #f5c2c7;
        }

        .form-card{
            background:#ffffff;
            border:1px solid #dcdcdc;
            border-radius:12px;
            padding:20px;
        }

        .section-title{
            font-family:'Poppins', sans-serif;
            font-size:17px;
            color:#2f2f2f;
            margin:0 0 16px 0;
        }

        .form-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
        }

        .form-row{
            margin-bottom:14px;
        }

        .form-label{
            display:block;
            font-size:12px;
            color:#666;
            margin-bottom:6px;
            font-weight:500;
        }

        .form-control,
        .form-select{
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

        .form-control:focus,
        .form-select:focus{
            border-color:#2E7D32;
            box-shadow:0 0 0 3px rgba(46,125,50,0.08);
        }

        .form-actions{
            margin-top:18px;
            display:flex;
            justify-content:flex-end;
            gap:10px;
            flex-wrap:wrap;
        }

        .btn{
            border:none;
            border-radius:8px;
            padding:11px 16px;
            font-size:13px;
            font-weight:600;
            font-family:'Inter', sans-serif;
            cursor:pointer;
        }

        .btn-cancel{
            background:#e0e0e0;
            color:#444;
        }

        .btn-save{
            background:#2E7D32;
            color:#fff;
        }

        @media (max-width: 900px){
            .page-wrapper{
                max-width:100%;
            }

            .form-grid{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-wrapper">
        <div class="page-header">
            <a href="child_profile.php?child_id=<?php echo $child_id; ?>" class="back-link">← Back to Child Profile</a>
            <h2 class="page-title">Edit Guardian Information</h2>
            <div class="small-label">Active CDC: <?php echo htmlspecialchars($_SESSION['active_cdc_name']); ?></div>
        </div>

        <?php if(!empty($success)){ ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>

        <?php if(!empty($error)){ ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="POST">
            <div class="form-card">
                <h3 class="section-title">Guardian Information</h3>

                <div class="form-grid">
                    <div class="form-row">
                        <label class="form-label">Parent/Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="<?php echo htmlspecialchars($guardian_full_name); ?>">
                    </div>

                    <div class="form-row">
                        <label class="form-label">Relationship to the Child</label>
                        <select name="relationship_to_child" class="form-select">
                            <option value="">Select Relationship</option>
                            <option value="Mother" <?php if(($guardian['relationship_to_child'] ?? '') == 'Mother') echo 'selected'; ?>>Mother</option>
                            <option value="Father" <?php if(($guardian['relationship_to_child'] ?? '') == 'Father') echo 'selected'; ?>>Father</option>
                            <option value="Grandmother" <?php if(($guardian['relationship_to_child'] ?? '') == 'Grandmother') echo 'selected'; ?>>Grandmother</option>
                            <option value="Grandfather" <?php if(($guardian['relationship_to_child'] ?? '') == 'Grandfather') echo 'selected'; ?>>Grandfather</option>
                            <option value="Guardian" <?php if(($guardian['relationship_to_child'] ?? '') == 'Guardian') echo 'selected'; ?>>Guardian</option>
                            <option value="Aunt" <?php if(($guardian['relationship_to_child'] ?? '') == 'Aunt') echo 'selected'; ?>>Aunt</option>
                            <option value="Uncle" <?php if(($guardian['relationship_to_child'] ?? '') == 'Uncle') echo 'selected'; ?>>Uncle</option>
                            <option value="Sibling" <?php if(($guardian['relationship_to_child'] ?? '') == 'Sibling') echo 'selected'; ?>>Sibling</option>
                            <option value="Other" <?php if(($guardian['relationship_to_child'] ?? '') == 'Other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Guardian Address</label>
                        <input type="text" name="guardian_address" class="form-control" value="<?php echo htmlspecialchars($guardian['address'] ?? $child['address']); ?>">
                    </div>

                    <div class="form-row">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($guardian['contact_number'] ?? $child['contact_number']); ?>">
                    </div>

                    <div class="form-row">
                        <label class="form-label">Email</label>
                        <input type="email" name="guardian_email" class="form-control" value="<?php echo htmlspecialchars($guardian['email'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="window.location.href='child_profile.php?child_id=<?php echo $child_id; ?>'">Cancel</button>
                <button type="submit" name="update" class="btn btn-save">Update Guardian Information</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');

    if (window.innerWidth <= 991) {
        sidebar.classList.toggle('open');
    } else {
        sidebar.classList.toggle('closed');
        mainContent.classList.toggle('full');
    }
}
</script>

</body>
</html>