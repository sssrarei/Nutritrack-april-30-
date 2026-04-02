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
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

$error = "";
$rows = [];
$prepared_by = trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

$sql = "SELECT 
            ar.record_id,
            c.child_id,
            CONCAT(c.first_name, ' ', c.last_name) AS child_name,
            cd.cdc_name,
            ar.date_recorded,
            c.birthdate,
            ar.height,
            ar.weight,
            ar.muac,
            ar.wfa_status,
            ar.hfa_status,
            ar.wflh_status,
            CONCAT(u.first_name, ' ', u.last_name) AS recorded_by_name
        FROM anthropometric_records ar
        INNER JOIN children c ON ar.child_id = c.child_id
        INNER JOIN cdc cd ON c.cdc_id = cd.cdc_id
        LEFT JOIN users u ON ar.recorded_by = u.user_id
        WHERE c.cdc_id = ?";

$types = "i";
$params = [$cdc_id];

if($date_from !== ''){
    $sql .= " AND DATE(ar.date_recorded) >= ?";
    $types .= "s";
    $params[] = $date_from;
}

if($date_to !== ''){
    $sql .= " AND DATE(ar.date_recorded) <= ?";
    $types .= "s";
    $params[] = $date_to;
}

$sql .= " ORDER BY ar.date_recorded DESC, c.last_name ASC, c.first_name ASC";

$stmt = $conn->prepare($sql);

if($stmt){
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){
        $birth = new DateTime($row['birthdate']);
        $measured = new DateTime(date('Y-m-d', strtotime($row['date_recorded'])));
        $diff = $birth->diff($measured);
        $age_in_months = ($diff->y * 12) + $diff->m;

        $row['age_in_months'] = $age_in_months;
        $rows[] = $row;
    }

    $stmt->close();
} else {
    $error = "Failed to prepare WMR query.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weight Monitoring Report | NutriTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/cdw-style.css">
    <style>
        *{
            box-sizing:border-box;
            margin:0;
            padding:0;
        }

        body{
            font-family:'Inter', sans-serif;
            background:#eef0f3;
            color:#333;
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

        .content-card{
            background:#ffffff;
            border:1px solid #dcdcdc;
            border-radius:14px;
            padding:20px;
        }

        .filter-grid{
            display:grid;
            grid-template-columns:repeat(2, 1fr);
            gap:14px;
            margin-bottom:18px;
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

        .btn-generate{
            background:#2E7D32;
            color:#fff;
        }

        .btn-submit{
            background:#3498db;
            color:#fff;
        }

        .btn-reset{
            background:#f5f5f5;
            color:#555;
            border:1px solid #d6d6d6;
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

        .prepared-by{
            margin-bottom:18px;
            padding:12px 14px;
            background:#f8fbf8;
            border:1px solid #dfe9df;
            border-radius:10px;
        }

        .prepared-by-label{
            font-size:12px;
            color:#777;
            margin-bottom:4px;
            font-weight:500;
        }

        .prepared-by-value{
            font-size:14px;
            color:#2f2f2f;
            font-weight:700;
        }

        .report-list{
            display:flex;
            flex-direction:column;
            gap:18px;
        }

        .date-group-card{
            border:1px solid #e3e3e3;
            border-radius:14px;
            background:#fafafa;
            overflow:hidden;
        }

        .date-group-header{
            background:#2E7D32;
            color:#fff;
            padding:14px 18px;
        }

        .date-group-title{
            font-family:'Poppins', sans-serif;
            font-size:18px;
            font-weight:700;
        }

        .date-group-body{
            padding:18px;
        }

        .child-block{
            border:1px solid #ebebeb;
            border-radius:12px;
            background:#ffffff;
            padding:16px;
            margin-bottom:14px;
        }

        .child-block:last-child{
            margin-bottom:0;
        }

        .child-name{
            font-family:'Poppins', sans-serif;
            font-size:17px;
            font-weight:700;
            color:#2f2f2f;
            margin-bottom:12px;
        }

        .report-grid{
            display:grid;
            grid-template-columns:repeat(4, 1fr);
            gap:14px;
        }

        .report-item{
            background:#ffffff;
            border:1px solid #ebebeb;
            border-radius:10px;
            padding:12px 14px;
        }

        .report-label{
            font-size:12px;
            color:#777;
            margin-bottom:5px;
            font-weight:500;
        }

        .report-value{
            font-size:14px;
            color:#2f2f2f;
            font-weight:600;
            line-height:1.4;
            word-break:break-word;
        }

        .no-data{
            padding:14px 2px 0;
            font-size:13px;
            color:#777;
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

            .filter-grid{
                grid-template-columns:1fr 1fr;
            }

            .report-grid{
                grid-template-columns:1fr 1fr;
            }
        }

        @media (max-width: 768px){
            .filter-grid{
                grid-template-columns:1fr;
            }

            .report-grid{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-header">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1 class="page-title">Weight Monitoring Report</h1>
        <div class="page-subtitle">
            Active CDC: <?php echo htmlspecialchars($_SESSION['active_cdc_name']); ?>
        </div>
    </div>

    <div class="content-card">
        <?php if(!empty($error)){ ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="GET">
            <div class="filter-grid">
                <div class="form-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>

                <div class="form-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-generate">Generate Report</button>
                <button type="button" class="btn btn-submit">Submit Report</button>
                <a href="wmr_report.php" class="btn btn-reset">Reset</a>
            </div>
        </form>

        <div class="prepared-by">
            <div class="prepared-by-label">Prepared by</div>
            <div class="prepared-by-value"><?php echo htmlspecialchars($prepared_by); ?></div>
        </div>

        <?php if(!empty($rows)){ ?>
            <?php
            $grouped_rows = [];

            foreach($rows as $row){
                $date_key = date('Y-m-d', strtotime($row['date_recorded']));
                $grouped_rows[$date_key][] = $row;
            }
            ?>

            <div class="report-list">
                <?php foreach($grouped_rows as $date_key => $items){ ?>
                    <div class="date-group-card">
                        <div class="date-group-header">
                            <div class="date-group-title">
                                Date Measured: <?php echo date("F d, Y", strtotime($date_key)); ?>
                            </div>
                        </div>

                        <div class="date-group-body">
                            <?php foreach($items as $row){ ?>
                                <div class="child-block">
                                    <div class="child-name"><?php echo htmlspecialchars($row['child_name']); ?></div>

                                    <div class="report-grid">
                                        <div class="report-item">
                                            <div class="report-label">Age (Months)</div>
                                            <div class="report-value"><?php echo htmlspecialchars($row['age_in_months']); ?></div>
                                        </div>

                                        <div class="report-item">
                                            <div class="report-label">Height (cm)</div>
                                            <div class="report-value"><?php echo htmlspecialchars($row['height']); ?></div>
                                        </div>

                                        <div class="report-item">
                                            <div class="report-label">Weight (kg)</div>
                                            <div class="report-value"><?php echo htmlspecialchars($row['weight']); ?></div>
                                        </div>

                                        <div class="report-item">
                                            <div class="report-label">MUAC (cm)</div>
                                            <div class="report-value"><?php echo htmlspecialchars($row['muac']); ?></div>
                                        </div>

                                        <div class="report-item">
                                            <div class="report-label">WFA</div>
                                            <div class="report-value"><?php echo htmlspecialchars($row['wfa_status']); ?></div>
                                        </div>

                                        <div class="report-item">
                                            <div class="report-label">HFA</div>
                                            <div class="report-value"><?php echo htmlspecialchars($row['hfa_status']); ?></div>
                                        </div>

                                        <div class="report-item">
                                            <div class="report-label">WFL/H</div>
                                            <div class="report-value"><?php echo htmlspecialchars($row['wflh_status']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="no-data">No report data found.</p>
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
</script>

</body>
</html>