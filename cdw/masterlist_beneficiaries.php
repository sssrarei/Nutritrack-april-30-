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

$cdc_id = (int) $_SESSION['active_cdc_id'];
$user_id = (int) $_SESSION['user_id'];
$error = "";
$success = "";
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

function fetch_masterlist_rows($conn, $cdc_id){
    $rows = [];

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

    if(!$stmt){
        return ['error' => 'Failed to prepare Masterlist query.', 'rows' => []];
    }

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

    return ['error' => '', 'rows' => $rows];
}

/* =========================================================
   SUBMIT REPORT
========================================================= */
if(isset($_POST['submit_report'])){
    $masterlist_data = fetch_masterlist_rows($conn, $cdc_id);

    if($masterlist_data['error'] !== ''){
        $error = $masterlist_data['error'];
    } elseif(empty($masterlist_data['rows'])){
        $error = "No beneficiary records found to submit.";
    } else {
        mysqli_begin_transaction($conn);

        try{
            $payload_rows = [];

            foreach($masterlist_data['rows'] as $row){
                $payload_rows[] = [
                    'child_id' => (int)$row['child_id'],
                    'full_name' => $row['full_name'],
                    'sex' => $row['sex'],
                    'birthdate' => $row['birthdate'],
                    'age_in_months' => $row['age_in_months'],
                    'guardian_name' => $row['guardian_name'],
                    'address' => $row['address']
                ];
            }

            $report_payload = json_encode([
                'report_type' => 'masterlist',
                'cdc_id' => $cdc_id,
                'cdc_name' => $_SESSION['active_cdc_name'],
                'prepared_by' => $prepared_by,
                'submitted_rows' => $payload_rows,
                'total_records' => count($payload_rows)
            ], JSON_UNESCAPED_UNICODE);

            if($report_payload === false){
                throw new Exception("Failed to encode report payload.");
            }

            $insert_stmt = $conn->prepare("
                INSERT INTO submitted_reports
                (report_type, cdc_id, submitted_by, date_from, date_to, submitted_at, status, report_payload)
                VALUES (?, ?, ?, NULL, NULL, NOW(), ?, ?)
            ");

            if(!$insert_stmt){
                throw new Exception("Failed to prepare submitted report insert query.");
            }

            $report_type = 'masterlist';
            $status = 'submitted';

            $insert_stmt->bind_param(
                "siiss",
                $report_type,
                $cdc_id,
                $user_id,
                $status,
                $report_payload
            );

            if(!$insert_stmt->execute()){
                throw new Exception("Failed to save submitted report.");
            }

            $insert_stmt->close();

            mysqli_commit($conn);
            $success = "Masterlist of Beneficiaries submitted successfully and is now visible to CSWD.";
        } catch(Exception $e){
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

/* =========================================================
   FETCH DATA
========================================================= */
$data = fetch_masterlist_rows($conn, $cdc_id);

if($data['error'] !== ''){
    $error = $data['error'];
} else {
    $rows = $data['rows'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masterlist of Beneficiaries | NutriTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/cdw/cdw-style.css">
    <link rel="stylesheet" href="../assets/cdw/cdw-topbar-notification.css">
    <style>
       *{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

body{
    font-family:'Inter', sans-serif;
    background:var(--page-bg);
    color:var(--text-color);
}

a{
    text-decoration:none;
}

.main-content{
    margin-left:260px;
    padding:112px 24px 30px;
    transition:margin-left 0.25s ease;
}

.main-content.full{
    margin-left:0;
}

.page-header{
    background:var(--card-bg);
    border:1px solid var(--border-color);
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
    color:var(--accent);
}

.page-title{
    font-family:'Poppins', sans-serif;
    font-size:24px;
    font-weight:700;
    color:var(--text-color);
    margin-bottom:6px;
}

.page-subtitle{
    font-size:13px;
    color:var(--muted-text);
    line-height:1.6;
}

.content-card{
    background:var(--card-bg);
    border:1px solid var(--border-color);
    border-radius:14px;
    padding:20px;
}

.button-group{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:18px;
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

.btn-submit{
    background:var(--btn-primary);
    color:#fff;
}

.prepared-by{
    margin-bottom:18px;
    padding:12px 14px;
    background:var(--card-subtle);
    border:1px solid var(--border-color);
    border-radius:10px;
}

.prepared-by-label{
    font-size:12px;
    color:var(--muted-text);
    margin-bottom:4px;
    font-weight:500;
}

.prepared-by-value{
    font-size:14px;
    color:var(--text-color);
    font-weight:700;
}

.success-message{
    background:var(--success-bg);
    color:var(--success-text);
    border:1px solid var(--success-border);
    border-radius:10px;
    padding:14px 16px;
    margin-bottom:16px;
    font-size:13px;
    font-weight:600;
}

.error-message{
    background:var(--danger-bg);
    color:var(--danger-text);
    border:1px solid var(--danger-border);
    border-radius:10px;
    padding:14px 16px;
    margin-bottom:16px;
    font-size:13px;
    font-weight:600;
}

.table-wrapper{
    width:100%;
    overflow-x:auto;
}

.masterlist-table{
    width:100%;
    min-width:1150px;
    border-collapse:collapse;
}

.masterlist-table th,
.masterlist-table td{
    border-bottom:1px solid var(--border-light);
    padding:14px 12px;
    text-align:left;
    vertical-align:middle;
    font-size:14px;
}

.masterlist-table th{
    background:var(--table-head);
    color:var(--text-color);
    font-weight:700;
    white-space:nowrap;
}

.masterlist-table tbody tr:hover{
    background:var(--hover-bg);
}

.empty-state{
    padding:16px 4px;
    font-size:13px;
    color:var(--muted-text);
}

@media (max-width: 991px){
    .sidebar{
        transform:translateX(-100%);
    }

    .sidebar.open{
        transform:translateX(0);
    }

    .sidebar-overlay.show{
        display:block;
        position:fixed;
        top:88px;
        left:0;
        width:100%;
        height:calc(100vh - 88px);
        background:rgba(0,0,0,0.25);
        z-index:1040;
    }

    .main-content{
        margin-left:0;
        padding:104px 16px 24px;
    }

    .topbar{
        padding:0 12px;
    }

    .topbar-logo{
        height:44px;
    }

    .user-chip{
        display:none;
    }
}
    </style>
</head>
<?php include __DIR__ . '/../includes/auth.php'; ?>
<body class="<?php echo themeClass(); ?>">

<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<div class="main-content" id="mainContent">

    <div class="page-header">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1 class="page-title">Masterlist of Beneficiaries</h1>
        <div class="page-subtitle">
            Active CDC: <?php echo htmlspecialchars($_SESSION['active_cdc_name']); ?>
        </div>
    </div>

    <div class="content-card">

        <?php if(!empty($error)){ ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <?php if(!empty($success)){ ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>

        <form method="POST">
            <div class="button-group">
                <button type="submit" name="submit_report" class="btn btn-submit">Submit Report</button>
            </div>
        </form>

        <div class="prepared-by">
            <div class="prepared-by-label">Prepared by</div>
            <div class="prepared-by-value"><?php echo htmlspecialchars($prepared_by); ?></div>
        </div>

        <?php if(!empty($rows)){ ?>
            <div class="table-wrapper">
                <table class="masterlist-table">
                    <thead>
                        <tr>
                            <th>Child Name</th>
                            <th>Sex</th>
                            <th>Birthdate</th>
                            <th>Age (Months)</th>
                            <th>Guardian</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rows as $row){ ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['sex']); ?></td>
                                <td><?php echo !empty($row['birthdate']) ? htmlspecialchars(date("F d, Y", strtotime($row['birthdate']))) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($row['age_in_months']); ?></td>
                                <td><?php echo !empty($row['guardian_name']) ? htmlspecialchars($row['guardian_name']) : 'N/A'; ?></td>
                                <td><?php echo !empty($row['address']) ? htmlspecialchars($row['address']) : 'N/A'; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="empty-state">No beneficiary records found for this CDC.</div>
        <?php } ?>

    </div>
</div>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var mainContent = document.getElementById('mainContent');

    if (window.innerWidth <= 991) {
        sidebar.classList.toggle('open');
        if (overlay) {
            overlay.classList.toggle('show');
        }
    } else {
        sidebar.classList.toggle('closed');
        mainContent.classList.toggle('full');
    }
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    var overlay = document.getElementById('sidebarOverlay');
    if (overlay) {
        overlay.classList.remove('show');
    }
}
</script>
<script src="../assets/cdw/sidebar.js"></script>
</body>
</html>