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

function safe_value($value, $fallback = 'N/A')
{
    return (isset($value) && trim((string)$value) !== '') ? $value : $fallback;
}

function format_date_value($date, $fallback = 'N/A')
{
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return $fallback;
    }

    $timestamp = strtotime($date);
    if (!$timestamp) {
        return $fallback;
    }

    return date('F d, Y', $timestamp);
}

function format_datetime_value($date, $fallback = 'N/A')
{
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return $fallback;
    }

    $timestamp = strtotime($date);
    if (!$timestamp) {
        return $fallback;
    }

    return date('F d, Y h:i A', $timestamp);
}

function normalize_sex($sex)
{
    $sex = strtolower(trim((string)$sex));

    if ($sex === 'm' || $sex === 'male') {
        return 'Male';
    }

    if ($sex === 'f' || $sex === 'female') {
        return 'Female';
    }

    return trim((string)$sex) !== '' ? ucfirst(trim((string)$sex)) : 'N/A';
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('No submitted report selected.');
}

$submitted_report_id = (int) $_GET['id'];

$report_sql = "
    SELECT
        sr.submitted_report_id,
        sr.report_type,
        sr.cdc_id,
        sr.submitted_by,
        sr.date_from,
        sr.date_to,
        sr.submitted_at,
        sr.status,
        sr.report_payload,
        c.cdc_name,
        c.barangay,
        c.address AS cdc_address,
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
    WHERE sr.submitted_report_id = ?
    LIMIT 1
";

$stmt_report = mysqli_prepare($conn, $report_sql);
if (!$stmt_report) {
    die('Failed to prepare submitted report query.');
}

mysqli_stmt_bind_param($stmt_report, "i", $submitted_report_id);
mysqli_stmt_execute($stmt_report);
$result_report = mysqli_stmt_get_result($stmt_report);

if (!$result_report || mysqli_num_rows($result_report) === 0) {
    die('Submitted report not found.');
}

$report = mysqli_fetch_assoc($result_report);
mysqli_stmt_close($stmt_report);

if ($report['report_type'] !== 'masterlist') {
    die('The selected submitted report is not a Masterlist of Beneficiaries report.');
}

$payload = json_decode($report['report_payload'], true);
if (!is_array($payload)) {
    $payload = [];
}

$rows = [];
if (isset($payload['submitted_rows']) && is_array($payload['submitted_rows'])) {
    $rows = $payload['submitted_rows'];
}

$prepared_by = safe_value($payload['prepared_by'] ?? '', $report['submitted_by_name']);
$total_records = isset($payload['total_records']) ? (int)$payload['total_records'] : count($rows);

$coverage_text = 'All Dates';
if (!empty($report['date_from']) && !empty($report['date_to'])) {
    $coverage_text = format_date_value($report['date_from']) . ' - ' . format_date_value($report['date_to']);
} elseif (!empty($report['date_from'])) {
    $coverage_text = 'From ' . format_date_value($report['date_from']);
} elseif (!empty($report['date_to'])) {
    $coverage_text = 'Up to ' . format_date_value($report['date_to']);
}

$status = strtolower(trim((string)$report['status']));
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masterlist of Beneficiaries | NutriTrack</title>
    <link rel="stylesheet" href="../assets/admin/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <style>
        .report-view-wrapper {
            padding: 24px;
        }

        .report-header-card,
        .summary-card,
        .table-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            margin-bottom: 24px;
        }

        .report-header-card {
            padding: 24px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #163b68;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .report-header h1 {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #163b68;
        }

        .report-header p {
            margin: 8px 0 0;
            font-size: 14px;
            color: #64748b;
        }

        .summary-card {
            padding: 22px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(180px, 1fr));
            gap: 16px;
        }

        .summary-item {
            background: #f8fbff;
            border: 1px solid #e3edf8;
            border-radius: 14px;
            padding: 16px;
        }

        .summary-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 15px;
            font-weight: 700;
            color: #163b68;
            line-height: 1.5;
        }

        .table-header {
            padding: 20px 22px;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
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

        .record-count,
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .record-count {
            background: #edf4ff;
            color: #163b68;
        }

        .status-badge {
            text-transform: capitalize;
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

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .report-table {
            width: 100%;
            min-width: 1200px;
            border-collapse: collapse;
        }

        .report-table thead th {
            background: #f8fafc;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            text-align: left;
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .report-table tbody td {
            padding: 14px 16px;
            font-size: 14px;
            color: #1e293b;
            border-bottom: 1px solid #eef2f7;
            vertical-align: top;
        }

        .report-table tbody tr:hover {
            background: #fafcff;
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

        .payload-preview {
            padding: 20px 22px 24px;
            border-top: 1px solid #eef2f7;
        }

        .payload-preview pre {
            margin: 0;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            overflow: auto;
            font-size: 12px;
            color: #334155;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
        }

        @media (max-width: 1200px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .report-view-wrapper {
                padding: 16px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/admin_topbar.php'; ?>
<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="report-view-wrapper">

        <div class="report-header-card">
            <a href="monitoring_reports.php" class="back-link">← Back to Monitoring Reports</a>
            <div class="report-header">
                <h1>Masterlist of Beneficiaries</h1>
                <p>Submitted report snapshot for CSWD review.</p>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">CDC</div>
                    <div class="summary-value"><?php echo h(safe_value($payload['cdc_name'] ?? '', $report['cdc_name'])); ?></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Coverage</div>
                    <div class="summary-value"><?php echo h($coverage_text); ?></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Submitted By</div>
                    <div class="summary-value"><?php echo h(safe_value($report['submitted_by_name'])); ?></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Prepared By</div>
                    <div class="summary-value"><?php echo h($prepared_by); ?></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Submitted At</div>
                    <div class="summary-value"><?php echo h(format_datetime_value($report['submitted_at'])); ?></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Status</div>
                    <div class="summary-value">
                        <span class="status-badge <?php echo h($status_class); ?>">
                            <?php echo h(safe_value($report['status'])); ?>
                        </span>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Barangay</div>
                    <div class="summary-value"><?php echo h(safe_value($report['barangay'])); ?></div>
                </div>

                <div class="summary-item">
                    <div class="summary-label">Total Rows</div>
                    <div class="summary-value"><?php echo (int)$total_records; ?></div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div>
                    <h2>Masterlist Table</h2>
                    <p>Submitted beneficiary rows saved in this report snapshot.</p>
                </div>
                <span class="record-count"><?php echo (int)$total_records; ?> record(s)</span>
            </div>

            <?php if (!empty($rows)): ?>
                <div class="table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Child Name</th>
                                <th>Sex</th>
                                <th>Birthdate</th>
                                <th>Age in Months</th>
                                <th>Guardian Name</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $index => $row): ?>
                                <tr>
                                    <td><?php echo (int)($index + 1); ?></td>
                                    <td><?php echo h(safe_value($row['full_name'] ?? '')); ?></td>
                                    <td><?php echo h(normalize_sex($row['sex'] ?? '')); ?></td>
                                    <td><?php echo h(format_date_value($row['birthdate'] ?? '')); ?></td>
                                    <td><?php echo h(safe_value($row['age_in_months'] ?? '')); ?></td>
                                    <td><?php echo h(safe_value($row['guardian_name'] ?? '')); ?></td>
                                    <td><?php echo h(safe_value($row['address'] ?? '')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No masterlist rows found</h3>
                    <p>This submitted report does not contain readable beneficiary row data in the payload.</p>
                </div>

                <?php if (!empty($payload)): ?>
                    <div class="payload-preview">
                        <pre><?php echo h(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
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
    if (window.innerWidth > 991 && sidebar && sidebarOverlay) {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    }
});
</script>

</body>
</html>