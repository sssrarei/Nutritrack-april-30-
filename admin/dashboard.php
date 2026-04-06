<?php
include '../includes/auth.php';
include '../config/database.php';

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

function decodeReportPayload($payload) {
    if (is_array($payload)) {
        return $payload;
    }

    if (is_string($payload) && !empty($payload)) {
        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : array();
    }

    return array();
}

function getLatestSubmittedWMRPerCDC($conn) {
    $reports = array();

    $sql = "
        SELECT submitted_report_id, cdc_id, submitted_at, report_type, report_payload
        FROM submitted_reports
        WHERE LOWER(report_type) = 'wmr'
        ORDER BY cdc_id ASC, submitted_at DESC, submitted_report_id DESC
    ";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        $seen_cdc = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $cdc_id = (int)$row['cdc_id'];

            if (isset($seen_cdc[$cdc_id])) {
                continue;
            }

            $seen_cdc[$cdc_id] = true;
            $reports[] = $row;
        }
    }

    return $reports;
}

$total_cdcs = 0;
$total_cdws = 0;
$total_children = 0;
$at_risk_children = 0;
$submitted_reports = 0;
$pending_reviews = 0;

$status_summary = array(
    'Normal' => 0,
    'Underweight' => 0,
    'Severely Underweight' => 0,
    'Stunted' => 0,
    'Moderately Wasted' => 0,
    'Severely Wasted' => 0,
    'Overweight' => 0,
    'Obese' => 0
);

$recent_reports = array();
$intervention_alerts = array();

$cdc_query = "SELECT COUNT(*) AS total FROM cdc";
$cdc_result = mysqli_query($conn, $cdc_query);
if ($cdc_result && mysqli_num_rows($cdc_result) > 0) {
    $cdc_row = mysqli_fetch_assoc($cdc_result);
    $total_cdcs = (int)$cdc_row['total'];
}

$cdw_query = "SELECT COUNT(*) AS total FROM users WHERE role_id = 2";
$cdw_result = mysqli_query($conn, $cdw_query);
if ($cdw_result && mysqli_num_rows($cdw_result) > 0) {
    $cdw_row = mysqli_fetch_assoc($cdw_result);
    $total_cdws = (int)$cdw_row['total'];
}

$children_query = "SELECT COUNT(*) AS total FROM children";
$children_result = mysqli_query($conn, $children_query);
if ($children_result && mysqli_num_rows($children_result) > 0) {
    $children_row = mysqli_fetch_assoc($children_result);
    $total_children = (int)$children_row['total'];
}

$submitted_reports_query = "SELECT COUNT(*) AS total FROM submitted_reports";
$submitted_reports_result = mysqli_query($conn, $submitted_reports_query);
if ($submitted_reports_result && mysqli_num_rows($submitted_reports_result) > 0) {
    $submitted_reports_row = mysqli_fetch_assoc($submitted_reports_result);
    $submitted_reports = (int)$submitted_reports_row['total'];
}

$pending_reviews_query = "SELECT COUNT(*) AS total FROM submitted_reports WHERE LOWER(status) = 'pending'";
$pending_reviews_result = mysqli_query($conn, $pending_reviews_query);
if ($pending_reviews_result && mysqli_num_rows($pending_reviews_result) > 0) {
    $pending_reviews_row = mysqli_fetch_assoc($pending_reviews_result);
    $pending_reviews = (int)$pending_reviews_row['total'];
}

$at_risk_query = "SELECT COUNT(DISTINCT child_id) AS total FROM intervention_guidance WHERE is_at_risk = 1";
$at_risk_result = mysqli_query($conn, $at_risk_query);
if ($at_risk_result && mysqli_num_rows($at_risk_result) > 0) {
    $at_risk_row = mysqli_fetch_assoc($at_risk_result);
    $at_risk_children = (int)$at_risk_row['total'];
}

/*
|--------------------------------------------------------------------------
| Nutritional Status Summary
| Latest submitted WMR per CDC only
|--------------------------------------------------------------------------
*/
$latest_wmr_reports = getLatestSubmittedWMRPerCDC($conn);

foreach ($latest_wmr_reports as $wmr_report) {
    $payload = decodeReportPayload($wmr_report['report_payload']);
    $submitted_rows = isset($payload['submitted_rows']) && is_array($payload['submitted_rows'])
        ? $payload['submitted_rows']
        : array();

    foreach ($submitted_rows as $row) {
        $wfa_status = isset($row['wfa_status']) ? trim($row['wfa_status']) : '';
        $hfa_status = isset($row['hfa_status']) ? trim($row['hfa_status']) : '';
        $wflh_status = isset($row['wflh_status']) ? trim($row['wflh_status']) : '';

        if ($wfa_status === 'Severely Underweight') {
            $status_summary['Severely Underweight']++;
        } elseif ($wfa_status === 'Underweight') {
            $status_summary['Underweight']++;
        }

        if ($hfa_status === 'Stunted' || $hfa_status === 'Severely Stunted') {
            $status_summary['Stunted']++;
        }

        if ($wflh_status === 'Moderately Wasted') {
            $status_summary['Moderately Wasted']++;
        }

        if ($wflh_status === 'Severely Wasted') {
            $status_summary['Severely Wasted']++;
        }

        if ($wflh_status === 'Overweight') {
            $status_summary['Overweight']++;
        }

        if ($wflh_status === 'Obese') {
            $status_summary['Obese']++;
        }

        $has_nutritional_concern =
            ($wfa_status === 'Underweight' || $wfa_status === 'Severely Underweight') ||
            ($hfa_status === 'Stunted' || $hfa_status === 'Severely Stunted') ||
            ($wflh_status === 'Moderately Wasted' || $wflh_status === 'Severely Wasted' || $wflh_status === 'Overweight' || $wflh_status === 'Obese');

        if (!$has_nutritional_concern) {
            $status_summary['Normal']++;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Recent Reports
|--------------------------------------------------------------------------
*/
$recent_reports_query = "
    SELECT 
        sr.submitted_report_id,
        sr.report_type,
        sr.cdc_id,
        sr.submitted_at,
        sr.status,
        c.cdc_name,
        CONCAT(u.first_name, ' ', u.last_name) AS submitted_by_name
    FROM submitted_reports sr
    LEFT JOIN cdc c ON sr.cdc_id = c.cdc_id
    LEFT JOIN users u ON sr.submitted_by = u.user_id
    ORDER BY sr.submitted_at DESC, sr.submitted_report_id DESC
    LIMIT 5
";
$recent_reports_result = mysqli_query($conn, $recent_reports_query);

if ($recent_reports_result) {
    while ($row = mysqli_fetch_assoc($recent_reports_result)) {
        $report_type = strtoupper($row['report_type']);
        $cdc_name = !empty($row['cdc_name']) ? $row['cdc_name'] : 'Unknown CDC';
        $submitted_by_name = !empty($row['submitted_by_name']) ? $row['submitted_by_name'] : 'Unknown User';
        $submitted_at = !empty($row['submitted_at']) ? date("F d, Y g:i A", strtotime($row['submitted_at'])) : '—';
        $status = !empty($row['status']) ? ucfirst($row['status']) : 'Submitted';

        $recent_reports[] = array(
            'title' => $report_type . ' Report',
            'description' => $cdc_name,
            'meta' => 'Submitted by ' . $submitted_by_name . ' • ' . $submitted_at . ' • ' . $status
        );
    }
}

/*
|--------------------------------------------------------------------------
| Intervention Alerts
|--------------------------------------------------------------------------
*/
$intervention_alerts_query = "
    SELECT 
        ig.guidance_id,
        ig.child_id,
        ig.intervention_category,
        ig.is_at_risk,
        ig.needs_counseling,
        ig.needs_referral,
        ig.status_note,
        ig.updated_at,
        ig.created_at,
        c.cdc_name,
        ch.first_name,
        ch.last_name
    FROM intervention_guidance ig
    LEFT JOIN children ch ON ig.child_id = ch.child_id
    LEFT JOIN cdc c ON ch.cdc_id = c.cdc_id
    WHERE ig.is_at_risk = 1
       OR ig.needs_counseling = 1
       OR ig.needs_referral = 1
    ORDER BY ig.updated_at DESC, ig.guidance_id DESC
    LIMIT 5
";
$intervention_alerts_result = mysqli_query($conn, $intervention_alerts_query);

if ($intervention_alerts_result) {
    while ($row = mysqli_fetch_assoc($intervention_alerts_result)) {
        $child_name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        if ($child_name === '') {
            $child_name = 'Unknown Child';
        }

        $description = !empty($row['status_note']) ? $row['status_note'] : 'Intervention alert available.';
        $meta_time = !empty($row['updated_at']) ? $row['updated_at'] : $row['created_at'];

        $intervention_alerts[] = array(
            'title' => $child_name . ' • ' . $row['intervention_category'],
            'description' => $description,
            'meta' => (!empty($row['cdc_name']) ? $row['cdc_name'] : 'Unknown CDC') . ' • ' .
                      (!empty($meta_time) ? date("F d, Y g:i A", strtotime($meta_time)) : '—')
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSWD Dashboard | NutriTrack</title>
    <link rel="stylesheet" href="../assets/admin-style.css">
    <link rel="stylesheet" href="../assets/admin-topbar-notification.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/admin_topbar.php'; ?>
<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="dashboard-wrapper">

        <div class="page-header-card">
            <div class="page-header">
                <h1>Dashboard Overview</h1>
                <p>Centralized monitoring summary for CSWD Administrator.</p>
            </div>

            <div class="summary-grid">
                <div class="summary-box box-navy">
                    <div class="summary-box-header">Total CDCs</div>
                    <div class="summary-box-value"><?php echo $total_cdcs; ?></div>
                </div>

                <div class="summary-box box-navy">
                    <div class="summary-box-header">Total CDWs</div>
                    <div class="summary-box-value"><?php echo $total_cdws; ?></div>
                </div>

                <div class="summary-box box-navy">
                    <div class="summary-box-header">Total Children</div>
                    <div class="summary-box-value"><?php echo $total_children; ?></div>
                </div>

                <div class="summary-box box-red">
                    <div class="summary-box-header">At-Risk Children</div>
                    <div class="summary-box-value"><?php echo $at_risk_children; ?></div>
                </div>

                <div class="summary-box box-green">
                    <div class="summary-box-header">Submitted Reports</div>
                    <div class="summary-box-value"><?php echo $submitted_reports; ?></div>
                </div>

                <div class="summary-box box-red">
                    <div class="summary-box-header">Pending Reviews</div>
                    <div class="summary-box-value"><?php echo $pending_reviews; ?></div>
                </div>
            </div>
        </div>

        <div class="panel-grid">
            <div class="panel-card">
                <h2 class="panel-title">Nutritional Status Summary</h2>

                <div class="status-list">
                    <div class="status-item">
                        <div class="status-item-left">
                            <h4>Normal</h4>
                            <p>Children with normal nutritional status</p>
                        </div>
                        <div class="status-badge"><?php echo $status_summary['Normal']; ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-item-left">
                            <h4>Underweight</h4>
                            <p>Children classified as underweight</p>
                        </div>
                        <div class="status-badge"><?php echo $status_summary['Underweight']; ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-item-left">
                            <h4>Severely Underweight</h4>
                            <p>Children needing closer monitoring</p>
                        </div>
                        <div class="status-badge"><?php echo $status_summary['Severely Underweight']; ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-item-left">
                            <h4>Stunted</h4>
                            <p>Height-for-age concern</p>
                        </div>
                        <div class="status-badge"><?php echo $status_summary['Stunted']; ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-item-left">
                            <h4>Moderately Wasted</h4>
                            <p>Children classified as moderately wasted</p>
                        </div>
                        <div class="status-badge"><?php echo $status_summary['Moderately Wasted']; ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-item-left">
                            <h4>Severely Wasted</h4>
                            <p>Children needing closer monitoring for severe wasting</p>
                        </div>
                        <div class="status-badge"><?php echo $status_summary['Severely Wasted']; ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-item-left">
                            <h4>Overweight</h4>
                            <p>Children classified as overweight</p>
                        </div>
                        <div class="status-badge"><?php echo $status_summary['Overweight']; ?></div>
                    </div>

                    <div class="status-item">
                        <div class="status-item-left">
                            <h4>Obese</h4>
                            <p>Children classified as obese</p>
                        </div>
                        <div class="status-badge"><?php echo $status_summary['Obese']; ?></div>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <h2 class="panel-title">Recent Reports</h2>

                <?php if (!empty($recent_reports)) { ?>
                    <div class="report-list">
                        <?php foreach ($recent_reports as $report) { ?>
                            <div class="report-item">
                                <h4><?php echo htmlspecialchars($report['title']); ?></h4>
                                <p><?php echo htmlspecialchars($report['description']); ?></p>
                                <div class="item-meta"><?php echo htmlspecialchars($report['meta']); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="empty-box">
                        No recent report data is connected yet.
                        <br><br>
                        This panel is ready for:
                        Masterlist, WMR, Feeding Attendance, Nutritional Status Summary, and Terminal Reports.
                    </div>
                <?php } ?>
            </div>

            <div class="panel-card">
                <h2 class="panel-title">Intervention Alerts</h2>

                <?php if (!empty($intervention_alerts)) { ?>
                    <div class="alert-list">
                        <?php foreach ($intervention_alerts as $alert) { ?>
                            <div class="alert-item">
                                <h4><?php echo htmlspecialchars($alert['title']); ?></h4>
                                <p><?php echo htmlspecialchars($alert['description']); ?></p>
                                <div class="item-meta"><?php echo htmlspecialchars($alert['meta']); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="empty-box">
                        No intervention alerts available yet.
                        <br><br>
                        This panel is reserved for:
                        At-Risk children, no-improvement-for-2-months alerts, and advisory intervention guidance.
                    </div>
                <?php } ?>
            </div>

            <div class="panel-card">
                <h2 class="panel-title">Admin Quick Access</h2>

                <div class="quick-actions">
                    <a href="child_records.php" class="quick-btn navy">Child Records</a>
                    <a href="monitoring_reports.php" class="quick-btn navy">Monitoring Reports</a>
                    <a href="add_cdc.php" class="quick-btn navy">CDC Management</a>
                    <a href="add_user.php" class="quick-btn navy">User Management</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const mainContent = document.getElementById('mainContent');

function handleDesktopToggle() {
    sidebar.classList.toggle('hidden');
    mainContent.classList.toggle('full');
}

function handleMobileToggle() {
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
    if (window.innerWidth > 991) {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    }
});
</script>

</body>
</html>