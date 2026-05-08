<?php
include '../includes/auth.php';
include '../config/database.php';

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$cdc_id = isset($_GET['cdc_id']) ? trim($_GET['cdc_id']) : '';
$report_type = isset($_GET['report_type']) ? trim($_GET['report_type']) : '';

$cdc_list = [];
$cdc_sql = "SELECT cdc_id, cdc_name, barangay FROM cdc ORDER BY cdc_name ASC";
$cdc_result = mysqli_query($conn, $cdc_sql);
if ($cdc_result) {
    while ($cdc_row = mysqli_fetch_assoc($cdc_result)) {
        $cdc_list[] = $cdc_row;
    }
}

$report_type_options = [
    'masterlist' => 'Masterlist of Beneficiaries',
    'individual_child' => 'Individual Child Report',
    'feeding_attendance' => 'Feeding Attendance Report',
    'wmr' => 'Weight Monitoring Report (WMR)',
    'nutritional_status_summary' => 'Nutritional Status Summary',
    'terminal_report' => 'Terminal Report'
];

$sql = "
    SELECT
        sr.submitted_report_id,
        sr.report_type,
        sr.cdc_id,
        sr.submitted_by,
        sr.date_from,
        sr.date_to,
        sr.submitted_at,
        sr.status,
        c.cdc_name,
        c.barangay,
        CONCAT(
            COALESCE(u.first_name, ''),
            CASE
                WHEN u.first_name IS NOT NULL AND u.first_name != '' AND u.last_name IS NOT NULL AND u.last_name != '' THEN ' '
                ELSE ''
            END,
            COALESCE(u.last_name, '')
        ) AS submitted_by_name
    FROM submitted_reports sr
    INNER JOIN cdc c ON sr.cdc_id = c.cdc_id
    INNER JOIN users u ON sr.submitted_by = u.user_id
    WHERE 1=1
";

$types = '';
$params = [];

if ($cdc_id !== '') {
    $sql .= " AND sr.cdc_id = ? ";
    $types .= 'i';
    $params[] = (int)$cdc_id;
}

if ($report_type !== '') {
    $sql .= " AND sr.report_type = ? ";
    $types .= 's';
    $params[] = $report_type;
}

if ($date_from !== '') {
    $sql .= " AND DATE(sr.submitted_at) >= ? ";
    $types .= 's';
    $params[] = $date_from;
}

if ($date_to !== '') {
    $sql .= " AND DATE(sr.submitted_at) <= ? ";
    $types .= 's';
    $params[] = $date_to;
}

$sql .= " ORDER BY sr.submitted_at DESC, sr.submitted_report_id DESC ";

$stmt = mysqli_prepare($conn, $sql);
$report_rows = [];
$error_message = '';

if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $report_rows[] = $row;
        }
    } else {
        $error_message = 'Failed to load submitted reports.';
    }

    mysqli_stmt_close($stmt);
} else {
    $error_message = 'Failed to prepare submitted reports query.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Reports | NutriTrack</title>
    <link rel="stylesheet" href="../assets/admin/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <style>
        .reports-wrapper {
            padding: 24px;
        }

        .reports-header-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            margin-bottom: 24px;
        }

        .reports-header h1 {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #163b68;
        }

        .reports-header p {
            margin: 8px 0 0;
            font-size: 14px;
            color: #64748b;
        }

        .filter-card,
        .table-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .filter-card {
            padding: 20px;
            margin-bottom: 24px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(180px, 1fr));
            gap: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .form-control {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #dbe2ea;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            background: #fff;
            outline: none;
            transition: 0.2s ease;
        }

        .form-control:focus {
            border-color: #163b68;
            box-shadow: 0 0 0 3px rgba(22, 59, 104, 0.10);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .filter-btn {
            border: none;
            border-radius: 10px;
            padding: 11px 18px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s ease;
        }

        .filter-btn.primary {
            background: #163b68;
            color: #fff;
        }

        .filter-btn.primary:hover {
            background: #102f54;
        }

        .filter-btn.secondary {
            background: #eef2f7;
            color: #1e293b;
        }

        .filter-btn.secondary:hover {
            background: #e2e8f0;
        }

        .table-header {
            padding: 20px 22px;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .table-header h2 {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #163b68;
        }

        .table-header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #64748b;
        }

        .record-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: #edf4ff;
            color: #163b68;
            font-size: 12px;
            font-weight: 700;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .reports-table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
        }

        .reports-table thead th {
            background: #f8fafc;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            text-align: left;
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .reports-table tbody td {
            padding: 15px 16px;
            font-size: 14px;
            color: #1e293b;
            border-bottom: 1px solid #eef2f7;
            vertical-align: top;
        }

        .reports-table tbody tr:hover {
            background: #fafcff;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-submitted {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-reviewed {
            background: #dcfce7;
            color: #166534;
        }

        .status-approved {
            background: #ede9fe;
            color: #6d28d9;
        }

        .status-returned {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-default {
            background: #e5e7eb;
            color: #374151;
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 14px;
            border-radius: 10px;
            background: #163b68;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .view-btn:hover {
            background: #102f54;
        }

        .empty-state {
            padding: 56px 24px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }

        .empty-state h3 {
            margin: 0 0 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            color: #163b68;
        }

        .error-message {
            margin: 18px 22px 0;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 14px;
        }

        @media (max-width: 1100px) {
            .filter-grid {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .reports-wrapper {
                padding: 16px;
            }
        }

        @media (max-width: 640px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/admin_topbar.php'; ?>
<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="reports-wrapper">

        <div class="reports-header-card">
            <div class="reports-header">
                <h1>Monitoring Reports</h1>
                <p>Review all reports submitted by Child Development Workers.</p>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="">
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="date_from">Submitted Date From</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" value="<?php echo h($date_from); ?>">
                    </div>

                    <div class="form-group">
                        <label for="date_to">Submitted Date To</label>
                        <input type="date" id="date_to" name="date_to" class="form-control" value="<?php echo h($date_to); ?>">
                    </div>

                    <div class="form-group">
                        <label for="cdc_id">Child Development Center</label>
                        <select id="cdc_id" name="cdc_id" class="form-control">
                            <option value="">All CDCs</option>
                            <?php foreach ($cdc_list as $cdc): ?>
                                <option value="<?php echo (int)$cdc['cdc_id']; ?>" <?php echo ($cdc_id !== '' && (int)$cdc_id === (int)$cdc['cdc_id']) ? 'selected' : ''; ?>>
                                    <?php echo h($cdc['cdc_name']); ?><?php echo !empty($cdc['barangay']) ? ' - ' . h($cdc['barangay']) : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="report_type">Report Type</label>
                        <select id="report_type" name="report_type" class="form-control">
                            <option value="">All Report Types</option>
                            <?php foreach ($report_type_options as $value => $label): ?>
                                <option value="<?php echo h($value); ?>" <?php echo ($report_type === $value) ? 'selected' : ''; ?>>
                                    <?php echo h($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="filter-btn primary">Filter Reports</button>
                    <a href="monitoring_reports.php" class="filter-btn secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div>
                    <h2>Submitted Reports</h2>
                    <p>Only submitted report snapshots are shown here.</p>
                </div>
                <div>
                    <span class="record-count"><?php echo count($report_rows); ?> record(s)</span>
                </div>
            </div>

            <?php if ($error_message !== ''): ?>
                <div class="error-message"><?php echo h($error_message); ?></div>
            <?php endif; ?>

            <?php if (empty($report_rows) && $error_message === ''): ?>
                <div class="empty-state">
                    <h3>No submitted reports found</h3>
                    <p>There are no matching submitted reports for the selected filters.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Submitted Date</th>
                                <th>Report Type</th>
                                <th>CDC</th>
                                <th>Submitted By</th>
                                <th>Coverage</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_rows as $row): ?>
                                <?php
                                    $status = strtolower(trim((string)$row['status']));
                                    $status_class = 'status-default';

                                    if ($status === 'submitted') {
                                        $status_class = 'status-submitted';
                                    } elseif ($status === 'reviewed') {
                                        $status_class = 'status-reviewed';
                                    } elseif ($status === 'approved') {
                                        $status_class = 'status-approved';
                                    } elseif ($status === 'returned') {
                                        $status_class = 'status-returned';
                                    }

                                    $coverage_text = 'All Dates';
                                    if (!empty($row['date_from']) && !empty($row['date_to'])) {
                                        $coverage_text = date('M d, Y', strtotime($row['date_from'])) . ' - ' . date('M d, Y', strtotime($row['date_to']));
                                    } elseif (!empty($row['date_from'])) {
                                        $coverage_text = 'From ' . date('M d, Y', strtotime($row['date_from']));
                                    } elseif (!empty($row['date_to'])) {
                                        $coverage_text = 'Up to ' . date('M d, Y', strtotime($row['date_to']));
                                    }

                                    $display_report_type = isset($report_type_options[$row['report_type']])
                                        ? $report_type_options[$row['report_type']]
                                        : ucwords(str_replace('_', ' ', $row['report_type']));

                                    $view_link = 'view_submitted_report.php?id=' . (int)$row['submitted_report_id'];

                                    if ($row['report_type'] === 'masterlist') {
                                        $view_link = 'view_masterlist_report.php?id=' . (int)$row['submitted_report_id'];
                                    } elseif ($row['report_type'] === 'feeding_attendance') {
                                        $view_link = 'view_feeding_attendance_report.php?id=' . (int)$row['submitted_report_id'];
                                    } elseif ($row['report_type'] === 'wmr') {
                                        $view_link = 'view_wmr_report.php?id=' . (int)$row['submitted_report_id'];
                                    } elseif ($row['report_type'] === 'nutritional_status_summary') {
                                        $view_link = 'view_nutritional_status_summary_report.php?id=' . (int)$row['submitted_report_id'];
                                    } elseif ($row['report_type'] === 'terminal_report') {
                                        $view_link = 'view_terminal_report.php?id=' . (int)$row['submitted_report_id'];
                                    } elseif ($row['report_type'] === 'individual_child') {
                                        $view_link = 'view_submitted_report.php?id=' . (int)$row['submitted_report_id'];
                                    }
                                ?>
                                <tr>
                                    <td><?php echo h(date('M d, Y h:i A', strtotime($row['submitted_at']))); ?></td>
                                    <td><?php echo h($display_report_type); ?></td>
                                    <td><?php echo h($row['cdc_name']); ?></td>
                                    <td><?php echo h($row['submitted_by_name']); ?></td>
                                    <td><?php echo h($coverage_text); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo h($status_class); ?>">
                                            <?php echo h($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo h($view_link); ?>" class="view-btn">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>



</body>
</html>