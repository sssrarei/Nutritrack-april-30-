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

$cdc_id = $_SESSION['active_cdc_id'];
$error = "";
$rows = [];
$prepared_by = trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

function compute_age_months_from_birthdate($birthdate){
    if(empty($birthdate) || $birthdate == '0000-00-00'){
        return 'N/A';
    }

    try{
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        $diff = $birth->diff($today);

        return ($diff->y * 12) + $diff->m;
    } catch(Exception $e){
        return 'N/A';
    }
}

$sql = "SELECT
            child_id,
            first_name,
            middle_name,
            last_name,
            birthdate,
            sex,
            guardian_name,
            address
        FROM children
        WHERE cdc_id = ?
        ORDER BY last_name ASC, first_name ASC";

$stmt = $conn->prepare($sql);

if($stmt){
    $stmt->bind_param("i", $cdc_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){
        $middle_name = !empty($row['middle_name']) ? ' ' . $row['middle_name'] : '';
        $row['full_name'] = trim($row['first_name'] . $middle_name . ' ' . $row['last_name']);
        $row['age_in_months'] = compute_age_months_from_birthdate($row['birthdate']);
        $rows[] = $row;
    }

    $stmt->close();
} else {
    $error = "Failed to prepare Masterlist query.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masterlist of Beneficiaries | NutriTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/cdw-style.css">
    <style>
        *{ box-sizing:border-box; margin:0; padding:0; }

        body{
            font-family:'Inter', sans-serif;
            background:#eef0f3;
            color:#333;
        }

        a{ text-decoration:none; }

        .main-content{
            margin-left:260px;
            padding:112px 24px 30px;
        }

        .page-header{
            background:#fff;
            border:1px solid #dcdcdc;
            border-radius:14px;
            padding:22px 24px;
            margin-bottom:18px;
        }

        .back-link{
            display:inline-flex;
            gap:8px;
            margin-bottom:10px;
            font-size:13px;
            font-weight:600;
            color:#2E7D32;
        }

        .page-title{
            font-family:'Poppins';
            font-size:24px;
            font-weight:700;
        }

        .page-subtitle{
            font-size:13px;
            color:#666;
        }

        .content-card{
            background:#fff;
            border:1px solid #dcdcdc;
            border-radius:14px;
            padding:20px;
        }

        .button-group{
            margin-bottom:18px;
        }

        .btn-submit{
            background:#3498db;
            color:#fff;
            border:none;
            padding:11px 16px;
            border-radius:8px;
            font-weight:600;
            cursor:pointer;
        }

        .prepared-by{
            margin-bottom:18px;
            padding:12px;
            background:#f8fbf8;
            border-radius:10px;
        }

        .beneficiary-card{
            border:1px solid #e3e3e3;
            border-radius:14px;
            margin-bottom:18px;
            overflow:hidden;
        }

        .beneficiary-header{
            background:#2E7D32;
            color:#fff;
            padding:14px;
            font-weight:700;
        }

        .beneficiary-body{
            padding:18px;
        }

        .report-grid{
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:14px;
        }

        .report-item{
            background:#fff;
            border:1px solid #ebebeb;
            padding:12px;
            border-radius:10px;
        }

        .report-label{
            font-size:12px;
            color:#777;
        }

        .report-value{
            font-size:14px;
            font-weight:600;
        }
    </style>
</head>
<body>

<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<div class="main-content">

    <div class="page-header">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1 class="page-title">Masterlist of Beneficiaries</h1>
        <div class="page-subtitle">
            Active CDC: <?php echo htmlspecialchars($_SESSION['active_cdc_name']); ?>
        </div>
    </div>

    <div class="content-card">

        <!-- SUBMIT BUTTON ONLY -->
        <div class="button-group">
            <button class="btn-submit">Submit Report</button>
        </div>

        <div class="prepared-by">
            <strong>Prepared by:</strong>
            <?php echo htmlspecialchars($prepared_by); ?>
        </div>

        <?php foreach($rows as $row){ ?>
            <div class="beneficiary-card">
                <div class="beneficiary-header">
                    <?php echo htmlspecialchars($row['full_name']); ?>
                </div>

                <div class="beneficiary-body">
                    <div class="report-grid">

                        <div class="report-item">
                            <div class="report-label">Sex</div>
                            <div class="report-value"><?php echo $row['sex']; ?></div>
                        </div>

                        <div class="report-item">
                            <div class="report-label">Birthdate</div>
                            <div class="report-value"><?php echo date("F d, Y", strtotime($row['birthdate'])); ?></div>
                        </div>

                        <div class="report-item">
                            <div class="report-label">Age (Months)</div>
                            <div class="report-value"><?php echo $row['age_in_months']; ?></div>
                        </div>

                        <div class="report-item">
                            <div class="report-label">Guardian</div>
                            <div class="report-value"><?php echo $row['guardian_name']; ?></div>
                        </div>

                        <div class="report-item" style="grid-column: span 2;">
                            <div class="report-label">Address</div>
                            <div class="report-value"><?php echo $row['address']; ?></div>
                        </div>

                    </div>
                </div>
            </div>
        <?php } ?>

    </div>
</div>

</body>
</html>