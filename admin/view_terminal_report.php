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

function get_first_existing_value($row, $keys, $fallback = 'N/A')
{
    if (!is_array($row)) {
        return $fallback;
    }

    foreach ($keys as $key) {
        if (isset($row[$key]) && trim((string)$row[$key]) !== '') {
            return $row[$key];
        }
    }

    return $fallback;
}

function get_first_existing_numeric_or_text($row, $keys, $fallback = 'N/A')
{
    if (!is_array($row)) {
        return $fallback;
    }

    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== '') {
            return $row[$key];
        }
    }

    return $fallback;
}

function build_child_name_from_row($row)
{
    if (!is_array($row)) {
        return 'N/A';
    }

    $full_name = get_first_existing_value($row, [
        'child_name',
        'full_name',
        'name',
        'beneficiary_name'
    ], '');

    if ($full_name !== '') {
        return $full_name;
    }

    $parts = [];

    $first = get_first_existing_value($row, ['first_name', 'child_first_name'], '');
    $middle = get_first_existing_value($row, ['middle_name', 'child_middle_name'], '');
    $last = get_first_existing_value($row, ['last_name', 'child_last_name'], '');

    if ($first !== '') $parts[] = trim($first);
    if ($middle !== '') $parts[] = trim($middle);
    if ($last !== '') $parts[] = trim($last);

    $built = trim(implode(' ', $parts));
    return $built !== '' ? $built : 'N/A';
}

function status_class($status)
{
    $status = strtolower(trim((string)$status));

    if ($status === 'normal') return 'status-normal';
    if ($status === 'underweight') return 'status-alert';
    if ($status === 'severely underweight') return 'status-alert';
    if ($status === 'overweight') return 'status-alert';
    if ($status === 'obese') return 'status-alert';
    if ($status === 'stunted') return 'status-alert';
    if ($status === 'severely stunted') return 'status-alert';
    if ($status === 'moderately wasted') return 'status-alert';
    if ($status === 'wasted') return 'status-alert';
    if ($status === 'severely wasted') return 'status-alert';

    return '';
}

function extract_report_rows($payload)
{
    if (!is_array($payload)) {
        return [];
    }

    $candidate_keys = [
        'rows',
        'report_rows',
        'records',
        'terminal_rows',
        'terminal_report_rows',
        'terminal_data',
        'report_data',
        'data',
        'entries',
        'items'
    ];

    foreach ($candidate_keys as $key) {
        if (isset($payload[$key]) && is_array($payload[$key])) {
            return array_values($payload[$key]);
        }
    }

    $is_list = array_keys($payload) === range(0, count($payload) - 1);
    if ($is_list) {
        return $payload;
    }

    return [];
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

if ($report['report_type'] !== 'terminal_report') {
    die('The selected submitted report is not a Terminal Report.');
}

$payload = json_decode($report['report_payload'], true);
if (!is_array($payload)) {
    $payload = [];
}

$rows = extract_report_rows($payload);

$prepared_by = get_first_existing_value($payload, [
    'prepared_by',
    'preparedBy',
    'submitted_by_name',
    'teacher_name',
    'cdw_name'
], $report['submitted_by_name']);

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
    <title>Terminal Report | NutriTrack</title>
    <link rel="stylesheet" href="../assets/admin-style.css">
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
            min-width: 1500px;
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

        .status-normal {
            color: #2E7D32;
            font-weight: 700;
        }

        .status-alert {
            color: #C0392B;
            font-weight: 700;
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
                <h1>Terminal Report</h1>
                <p>Submitted report snapshot for CSWD review.</p>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">CDC</div>
                    <div class="summary-value"><?php echo h(safe_value($report['cdc_name'])); ?></div>
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
                    <div class="summary-value"><?php echo h(safe_value($prepared_by)); ?></div>
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
                    <div class="summary-value"><?php echo count($rows); ?></div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div>
                    <h2>Terminal Report Table</h2>
                    <p>Submitted terminal report rows saved in this report snapshot.</p>
                </div>
                <span class="record-count"><?php echo count($rows); ?> record(s)</span>
            </div>

            <?php if (!empty($rows)): ?>
                <div class="table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Child Name</th>
                                <th>Sex</th>
                                <th>Baseline Date</th>
                                <th>Baseline WFA</th>
                                <th>Baseline HFA</th>
                                <th>Baseline WFL/H</th>
                                <th>Midline Date</th>
                                <th>Midline WFA</th>
                                <th>Midline HFA</th>
                                <th>Midline WFL/H</th>
                                <th>Endline Date</th>
                                <th>Endline WFA</th>
                                <th>Endline HFA</th>
                                <th>Endline WFL/H</th>
                                <th>Improvement</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $index => $row): ?>
                                <?php
                                    $child_name = build_child_name_from_row($row);
                                    $sex = normalize_sex(get_first_existing_value($row, ['sex', 'gender'], 'N/A'));

                                    $baseline_date = get_first_existing_value($row, ['baseline_date', 'baseline_recorded_date'], 'N/A');
                                    $baseline_wfa = get_first_existing_value($row, ['baseline_wfa_status', 'baseline_wfa'], 'N/A');
                                    $baseline_hfa = get_first_existing_value($row, ['baseline_hfa_status', 'baseline_hfa'], 'N/A');
                                    $baseline_wflh = get_first_existing_value($row, ['baseline_wflh_status', 'baseline_wflh', 'baseline_wfh_status', 'baseline_wfh'], 'N/A');

                                    $midline_date = get_first_existing_value($row, ['midline_date', 'midline_recorded_date'], 'N/A');
                                    $midline_wfa = get_first_existing_value($row, ['midline_wfa_status', 'midline_wfa'], 'N/A');
                                    $midline_hfa = get_first_existing_value($row, ['midline_hfa_status', 'midline_hfa'], 'N/A');
                                    $midline_wflh = get_first_existing_value($row, ['midline_wflh_status', 'midline_wflh', 'midline_wfh_status', 'midline_wfh'], 'N/A');

                                    $endline_date = get_first_existing_value($row, ['endline_date', 'endline_recorded_date'], 'N/A');
                                    $endline_wfa = get_first_existing_value($row, ['endline_wfa_status', 'endline_wfa'], 'N/A');
                                    $endline_hfa = get_first_existing_value($row, ['endline_hfa_status', 'endline_hfa'], 'N/A');
                                    $endline_wflh = get_first_existing_value($row, ['endline_wflh_status', 'endline_wflh', 'endline_wfh_status', 'endline_wfh'], 'N/A');

                                    $improvement = get_first_existing_value($row, [
                                        'improvement',
                                        'improvement_assessment',
                                        'overall_assessment',
                                        'remarks'
                                    ], 'N/A');
                                ?>
                                <tr>
                                    <td><?php echo (int)($index + 1); ?></td>
                                    <td><?php echo h($child_name); ?></td>
                                    <td><?php echo h($sex); ?></td>

                                    <td><?php echo h($baseline_date !== 'N/A' ? format_date_value($baseline_date, 'N/A') : 'N/A'); ?></td>
                                    <td class="<?php echo h(status_class($baseline_wfa)); ?>"><?php echo h($baseline_wfa); ?></td>
                                    <td class="<?php echo h(status_class($baseline_hfa)); ?>"><?php echo h($baseline_hfa); ?></td>
                                    <td class="<?php echo h(status_class($baseline_wflh)); ?>"><?php echo h($baseline_wflh); ?></td>

                                    <td><?php echo h($midline_date !== 'N/A' ? format_date_value($midline_date, 'N/A') : 'N/A'); ?></td>
                                    <td class="<?php echo h(status_class($midline_wfa)); ?>"><?php echo h($midline_wfa); ?></td>
                                    <td class="<?php echo h(status_class($midline_hfa)); ?>"><?php echo h($midline_hfa); ?></td>
                                    <td class="<?php echo h(status_class($midline_wflh)); ?>"><?php echo h($midline_wflh); ?></td>

                                    <td><?php echo h($endline_date !== 'N/A' ? format_date_value($endline_date, 'N/A') : 'N/A'); ?></td>
                                    <td class="<?php echo h(status_class($endline_wfa)); ?>"><?php echo h($endline_wfa); ?></td>
                                    <td class="<?php echo h(status_class($endline_hfa)); ?>"><?php echo h($endline_hfa); ?></td>
                                    <td class="<?php echo h(status_class($endline_wflh)); ?>"><?php echo h($endline_wflh); ?></td>

                                    <td><?php echo h($improvement); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No terminal report rows found</h3>
                    <p>This submitted report does not contain readable terminal report row data in the payload.</p>
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