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
$cdc_name = isset($_SESSION['active_cdc_name']) ? $_SESSION['active_cdc_name'] : 'N/A';
$prepared_by = trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
$error = "";
$rows = [];

/*
|--------------------------------------------------------------------------
| PROGRAM INFO
|--------------------------------------------------------------------------
| Baseline  = earliest record
| Midline   = nearest to baseline + 90 days
| Endline   = nearest to baseline + 180 days
|--------------------------------------------------------------------------
*/
$program_title = "6-Month Nutritional Monitoring Terminal Report";
$program_description = "This report summarizes each child's nutritional status across the 180-day feeding and monitoring period. Baseline is taken from the earliest available anthropometric record, Midline is the record nearest to Day 90, and Endline is the record nearest to Day 180.";
$program_duration = "180 Days (6 Months)";

function safe_display($value){
    return (!empty($value) && trim($value) !== '') ? $value : 'N/A';
}

function format_date_display($date){
    if(empty($date) || $date == '0000-00-00'){
        return 'N/A';
    }
    return date("F d, Y", strtotime($date));
}

function get_overall_status($wfa, $hfa, $wflh){
    $priority = [
        'Severely Wasted',
        'Moderately Wasted',
        'Severely Underweight',
        'Underweight',
        'Severely Stunted',
        'Stunted',
        'Obese',
        'Overweight',
        'Normal'
    ];

    $statuses = [
        trim((string)$wfa),
        trim((string)$hfa),
        trim((string)$wflh)
    ];

    foreach($priority as $status){
        if(in_array($status, $statuses, true)){
            return $status;
        }
    }

    return 'No Data';
}

function get_status_rank($status){
    $ranks = [
        'Severely Wasted' => 1,
        'Moderately Wasted' => 2,
        'Severely Underweight' => 3,
        'Underweight' => 4,
        'Severely Stunted' => 5,
        'Stunted' => 6,
        'Normal' => 7,
        'Overweight' => 8,
        'Obese' => 9
    ];

    return isset($ranks[$status]) ? $ranks[$status] : 0;
}

function get_improvement_text($baseline_status, $endline_status){
    $baseline_rank = get_status_rank($baseline_status);
    $endline_rank = get_status_rank($endline_status);

    if($baseline_rank === 0 || $endline_rank === 0){
        return 'No Data';
    }

    if($endline_rank > $baseline_rank){
        return 'Improved';
    }

    if($endline_rank < $baseline_rank){
        return 'Worsened';
    }

    return 'No Change';
}

function get_movement_text($baseline_status, $endline_status){
    if($baseline_status === 'No Data' || $endline_status === 'No Data'){
        return 'No Data';
    }

    if($baseline_status === $endline_status){
        return $baseline_status . " → " . $endline_status;
    }

    return $baseline_status . " → " . $endline_status;
}

function get_improvement_badge_class($text){
    $text = strtolower(trim($text));

    if($text === 'improved'){
        return 'badge-improved';
    }

    if($text === 'worsened'){
        return 'badge-worsened';
    }

    if($text === 'no change'){
        return 'badge-no-change';
    }

    return 'badge-no-data';
}

function get_nearest_record_by_target_date($records, $target_date){
    if(empty($records)){
        return null;
    }

    $nearest = null;
    $min_diff = PHP_INT_MAX;
    $target_ts = strtotime($target_date);

    foreach($records as $record){
        $record_ts = strtotime($record['date_recorded']);
        $diff = abs($record_ts - $target_ts);

        if($diff < $min_diff){
            $min_diff = $diff;
            $nearest = $record;
        }
    }

    return $nearest;
}

/*
|--------------------------------------------------------------------------
| CHILDREN UNDER ACTIVE CDC
|--------------------------------------------------------------------------
*/
$children_sql = "SELECT
                    child_id,
                    first_name,
                    middle_name,
                    last_name
                 FROM children
                 WHERE cdc_id = ?
                 ORDER BY last_name ASC, first_name ASC";

$children_stmt = $conn->prepare($children_sql);

if($children_stmt){
    $children_stmt->bind_param("i", $cdc_id);
    $children_stmt->execute();
    $children_result = $children_stmt->get_result();

    while($child = $children_result->fetch_assoc()){
        $child_id = (int) $child['child_id'];

        $records_sql = "SELECT
                            record_id,
                            child_id,
                            date_recorded,
                            wfa_status,
                            hfa_status,
                            wflh_status
                        FROM anthropometric_records
                        WHERE child_id = ?
                        ORDER BY date_recorded ASC, record_id ASC";

        $records_stmt = $conn->prepare($records_sql);

        if($records_stmt){
            $records_stmt->bind_param("i", $child_id);
            $records_stmt->execute();
            $records_result = $records_stmt->get_result();

            $records = [];
            while($record = $records_result->fetch_assoc()){
                $records[] = $record;
            }

            $records_stmt->close();

            if(count($records) === 0){
                continue;
            }

            $baseline = $records[0];
            $baseline_date = $baseline['date_recorded'];

            $midline_target = date('Y-m-d', strtotime($baseline_date . ' +90 days'));
            $endline_target = date('Y-m-d', strtotime($baseline_date . ' +180 days'));

            $midline = get_nearest_record_by_target_date($records, $midline_target);
            $endline = get_nearest_record_by_target_date($records, $endline_target);

            $baseline_status = get_overall_status(
                $baseline['wfa_status'],
                $baseline['hfa_status'],
                $baseline['wflh_status']
            );

            $midline_status = $midline ? get_overall_status(
                $midline['wfa_status'],
                $midline['hfa_status'],
                $midline['wflh_status']
            ) : 'No Data';

            $endline_status = $endline ? get_overall_status(
                $endline['wfa_status'],
                $endline['hfa_status'],
                $endline['wflh_status']
            ) : 'No Data';

            $improvement = get_improvement_text($baseline_status, $endline_status);
            $movement = get_movement_text($baseline_status, $endline_status);

            $middle_name = !empty($child['middle_name']) ? ' ' . $child['middle_name'] : '';
            $full_name = trim($child['first_name'] . $middle_name . ' ' . $child['last_name']);

            $rows[] = [
                'child_name' => $full_name,
                'baseline_date' => $baseline['date_recorded'],
                'baseline_status' => $baseline_status,
                'midline_date' => $midline ? $midline['date_recorded'] : '',
                'midline_status' => $midline_status,
                'endline_date' => $endline ? $endline['date_recorded'] : '',
                'endline_status' => $endline_status,
                'movement' => $movement,
                'improvement' => $improvement
            ];
        } else {
            $error = "Failed to prepare anthropometric records query.";
            break;
        }
    }

    $children_stmt->close();
} else {
    $error = "Failed to prepare children query.";
}

$total_children = count($rows);
$improved_count = 0;
$no_change_count = 0;
$worsened_count = 0;

foreach($rows as $row){
    if($row['improvement'] === 'Improved'){
        $improved_count++;
    } elseif($row['improvement'] === 'No Change'){
        $no_change_count++;
    } elseif($row['improvement'] === 'Worsened'){
        $worsened_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Report | NutriTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/cdw-style.css">
    <link rel="stylesheet" href="../assets/terminal_report.css">
</head>
<body>

<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-header">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1 class="page-title">Terminal Report</h1>
        <div class="page-subtitle">
            Final nutritional comparison report for the 6-month feeding and monitoring period.
        </div>
    </div>

    <div class="content-card">
        <div class="top-actions">
            <button type="button" class="btn btn-submit">Submit Report</button>
        </div>

        <?php if(!empty($error)){ ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="meta-box">
            <div class="meta-grid">
                <div class="meta-item"><strong>CDC:</strong> <?php echo htmlspecialchars($cdc_name); ?></div>
                <div class="meta-item"><strong>Prepared by:</strong> <?php echo htmlspecialchars($prepared_by); ?></div>
                <div class="meta-item"><strong>Program Duration:</strong> <?php echo htmlspecialchars($program_duration); ?></div>
                <div class="meta-item"><strong>Date Generated:</strong> <?php echo date("F d, Y"); ?></div>
            </div>
        </div>

        <div class="program-box">
            <div class="program-title"><?php echo htmlspecialchars($program_title); ?></div>
            <div class="program-text"><?php echo htmlspecialchars($program_description); ?></div>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Total Children</div>
                <div class="summary-value"><?php echo $total_children; ?></div>
            </div>

            <div class="summary-card">
                <div class="summary-label">Improved</div>
                <div class="summary-value"><?php echo $improved_count; ?></div>
            </div>

            <div class="summary-card">
                <div class="summary-label">No Change</div>
                <div class="summary-value"><?php echo $no_change_count; ?></div>
            </div>

            <div class="summary-card">
                <div class="summary-label">Worsened</div>
                <div class="summary-value"><?php echo $worsened_count; ?></div>
            </div>
        </div>

        <?php if(!empty($rows)){ ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Child Name</th>
                            <th>Baseline Date</th>
                            <th>Baseline Status</th>
                            <th>Midline Date</th>
                            <th>Midline Status</th>
                            <th>Endline Date</th>
                            <th>Endline Status</th>
                            <th>Status Movement</th>
                            <th>Improvement Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rows as $row){ ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['child_name']); ?></td>
                                <td><?php echo htmlspecialchars(format_date_display($row['baseline_date'])); ?></td>
                                <td><span class="status-chip"><?php echo htmlspecialchars($row['baseline_status']); ?></span></td>
                                <td><?php echo htmlspecialchars(format_date_display($row['midline_date'])); ?></td>
                                <td><span class="status-chip"><?php echo htmlspecialchars($row['midline_status']); ?></span></td>
                                <td><?php echo htmlspecialchars(format_date_display($row['endline_date'])); ?></td>
                                <td><span class="status-chip"><?php echo htmlspecialchars($row['endline_status']); ?></span></td>
                                <td><span class="movement-text"><?php echo htmlspecialchars($row['movement']); ?></span></td>
                                <td>
                                    <span class="badge <?php echo get_improvement_badge_class($row['improvement']); ?>">
                                        <?php echo htmlspecialchars($row['improvement']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="no-data">No terminal report data found.</p>
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