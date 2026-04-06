<?php
include '../includes/auth.php';
include '../config/database.php';
checkRole(3);

$current_page = 'dashboard';
$user_id = $_SESSION['user_id'];

$child = null;
$latest_record = null;
$monthly_measurements = [];
$error_message = '';

$child_sql = "
    SELECT c.*
    FROM parent_child_links pcl
    INNER JOIN children c ON pcl.child_id = c.child_id
    WHERE pcl.parent_id = ?
    LIMIT 1
";

if ($stmt = $conn->prepare($child_sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $child = $result->fetch_assoc();
    $stmt->close();
} else {
    $error_message = "Failed to load child information.";
}

if ($child) {
    $latest_sql = "
        SELECT *
        FROM anthropometric_records
        WHERE child_id = ?
        ORDER BY date_recorded DESC, record_id DESC
        LIMIT 1
    ";

    if ($stmt = $conn->prepare($latest_sql)) {
        $stmt->bind_param("i", $child['child_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $latest_record = $result->fetch_assoc();
        $stmt->close();
    }

    $measurements_sql = "
        SELECT date_recorded, weight, height, wfa_status, hfa_status, wflh_status
        FROM anthropometric_records
        WHERE child_id = ?
        ORDER BY date_recorded DESC, record_id DESC
        LIMIT 6
    ";

    if ($stmt = $conn->prepare($measurements_sql)) {
        $stmt->bind_param("i", $child['child_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $monthly_measurements[] = $row;
        }

        $stmt->close();
    }
}

function formatChildName($child) {
    $middle = !empty($child['middle_name']) ? ' ' . $child['middle_name'] . ' ' : ' ';
    return trim($child['first_name'] . $middle . $child['last_name']);
}

function calculateAgeText($birthdate) {
    if (empty($birthdate)) {
        return 'N/A';
    }

    $birth = new DateTime($birthdate);
    $today = new DateTime();
    $diff = $birth->diff($today);

    $years = $diff->y;
    $months = $diff->m;
    $total_months = ($years * 12) + $months;

    return $years . ' years (' . $total_months . ' months)';
}

function getPriorityStatusFromRecord($record) {
    if (!$record) {
        return 'No record yet';
    }

    $wflh = isset($record['wflh_status']) ? trim($record['wflh_status']) : '';
    $hfa  = isset($record['hfa_status']) ? trim($record['hfa_status']) : '';
    $wfa  = isset($record['wfa_status']) ? trim($record['wfa_status']) : '';

    $priority_wflh = [
        'Severely Wasted',
        'Moderately Wasted',
        'Wasted',
        'Overweight',
        'Obese'
    ];

    $priority_hfa = [
        'Severely Stunted',
        'Stunted'
    ];

    $priority_wfa = [
        'Severely Underweight',
        'Underweight'
    ];

    if (in_array($wflh, $priority_wflh, true)) {
        return $wflh;
    }

    if (in_array($hfa, $priority_hfa, true)) {
        return $hfa;
    }

    if (in_array($wfa, $priority_wfa, true)) {
        return $wfa;
    }

    if (!empty($wflh) && strtolower($wflh) === 'normal') {
        return 'Normal';
    }

    if (!empty($hfa) && strtolower($hfa) === 'normal') {
        return 'Normal';
    }

    if (!empty($wfa) && strtolower($wfa) === 'normal') {
        return 'Normal';
    }

    if (!empty($wflh)) {
        return $wflh;
    }

    if (!empty($hfa)) {
        return $hfa;
    }

    if (!empty($wfa)) {
        return $wfa;
    }

    return 'No record yet';
}

function getStatusClass($status) {
    $status = strtolower(trim($status));

    if ($status === 'normal') {
        return 'status-normal';
    }

    if ($status === 'no record yet' || $status === '') {
        return 'status-neutral';
    }

    return 'status-alert';
}

function getStatusNote($status) {
    $status = strtolower(trim($status));

    if ($status === 'normal') {
        return 'Child is in normal nutritional condition.';
    }

    if ($status === 'underweight') {
        return 'Child may need better regular meals.';
    }

    if ($status === 'severely underweight') {
        return 'Child needs close nutritional attention.';
    }

    if ($status === 'stunted') {
        return 'Child may have delayed growth for age.';
    }

    if ($status === 'severely stunted') {
        return 'Child may have serious long-term growth delay.';
    }

    if ($status === 'moderately wasted' || $status === 'wasted') {
        return 'Child may have low weight compared to height.';
    }

    if ($status === 'severely wasted') {
        return 'Child may be in a critical nutritional condition.';
    }

    if ($status === 'overweight') {
        return 'Child may need better food balance.';
    }

    if ($status === 'obese') {
        return 'Child may be at risk for health problems.';
    }

    return 'No nutritional record available yet.';
}

$age_text = $child ? calculateAgeText($child['birthdate']) : 'N/A';
$current_status = getPriorityStatusFromRecord($latest_record);
$status_class = getStatusClass($current_status);
$status_note = getStatusNote($current_status);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian Dashboard | NutriTrack</title>
    <link rel="stylesheet" href="../assets/guardian-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/guardian_topbar.php'; ?>
<?php include '../includes/guardian_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <?php if (!empty($error_message)) { ?>
        <div class="empty-state"><?php echo htmlspecialchars($error_message); ?></div>
    <?php } elseif (!$child) { ?>
        <div class="empty-state">
            No child is linked to this guardian account yet.
        </div>
    <?php } else { ?>
        <div class="dashboard-grid">

            <div class="left-column">

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Child Information & Nutritional Status</h2>
                    </div>
                    <div class="card-body">
                        <div class="child-hero">
                            <div class="child-info-block">
                                <h3 class="child-name"><?php echo htmlspecialchars(formatChildName($child)); ?></h3>

                                <div class="child-meta">
                                    <div class="meta-box">
                                        <div class="meta-label">Age</div>
                                        <div class="meta-value"><?php echo htmlspecialchars($age_text); ?></div>
                                    </div>

                                    <div class="meta-box">
                                        <div class="meta-label">Sex</div>
                                        <div class="meta-value"><?php echo htmlspecialchars(ucfirst($child['sex'])); ?></div>
                                    </div>

                                    <div class="meta-box">
                                        <div class="meta-label">Birthdate</div>
                                        <div class="meta-value"><?php echo htmlspecialchars(date('F d, Y', strtotime($child['birthdate']))); ?></div>
                                    </div>

                                    <div class="meta-box">
                                        <div class="meta-label">Address</div>
                                        <div class="meta-value"><?php echo htmlspecialchars($child['address']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="status-panel">
                                <div class="status-badge <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($current_status); ?>
                                </div>
                                <div class="status-note <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($status_note); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Monthly Measurements</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($monthly_measurements)) { ?>
                            <div class="table-wrap">
                                <table class="measurements-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Weight</th>
                                            <th>Height</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($monthly_measurements as $row) {
                                            $row_status = getPriorityStatusFromRecord($row);
                                            $row_class = (strtolower($row_status) === 'normal') ? 'normal' : ((strtolower($row_status) === 'no record yet') ? '' : 'alert');
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($row['date_recorded']))); ?></td>
                                                <td><?php echo htmlspecialchars(number_format((float)$row['weight'], 2)); ?> kg</td>
                                                <td><?php echo htmlspecialchars(number_format((float)$row['height'], 2)); ?> cm</td>
                                                <td class="table-status <?php echo $row_class; ?>">
                                                    <?php echo htmlspecialchars($row_status); ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <div class="empty-state">No measurement records available yet.</div>
                        <?php } ?>
                    </div>
                </div>

            </div>

            <div class="right-column">

                <div class="card guide-card">
                    <div class="card-header">
                        <h2 class="card-title">Child Health Status Guide</h2>
                    </div>
                    <div class="card-body">
                        <div class="guide-list">

                            <div class="guide-item">
                                <div class="guide-title normal">Normal</div>
                                <div class="guide-text">
                                    Child has a healthy weight and height for age.
                                </div>
                            </div>

                            <div class="guide-item">
                                <div class="guide-title alert">Underweight</div>
                                <div class="guide-text">
                                    Child weighs less than expected for age and may need better regular meals.
                                </div>
                            </div>

                            <div class="guide-item">
                                <div class="guide-title alert">Severely Underweight</div>
                                <div class="guide-text">
                                    Child is far below expected weight for age and needs close attention.
                                </div>
                            </div>

                            <div class="guide-item">
                                <div class="guide-title alert">Overweight</div>
                                <div class="guide-text">
                                    Child weighs more than expected for age and may need better food balance.
                                </div>
                            </div>

                            <div class="guide-item">
                                <div class="guide-title alert">Obese</div>
                                <div class="guide-text">
                                    Child has excessive body weight for their age. This can lead to serious health issues if not managed early.
                                </div>
                            </div>

                            <div class="guide-item">
                                <div class="guide-title alert">Stunted</div>
                                <div class="guide-text">
                                    Child is shorter than normal for their age and may have delayed growth.
                                </div>
                            </div>

                            <div class="guide-item">
                                <div class="guide-title alert">Severely Stunted</div>
                                <div class="guide-text">
                                    Child is much shorter than expected for their age.
                                </div>
                            </div>

                            <div class="guide-item">
                                <div class="guide-title alert">Moderately Wasted</div>
                                <div class="guide-text">
                                    Child has low weight for their height. This may be due to recent or short-term lack of proper nutrition.
                                </div>
                            </div>

                            <div class="guide-item">
                                <div class="guide-title alert">Severely Wasted</div>
                                <div class="guide-text">
                                    Child has very low weight for their height. This is a critical condition and needs urgent care.
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php } ?>
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