<?php
include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 2){
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['theme_mode'])) {
    $_SESSION['theme_mode'] = 'light';
}

$theme_mode = $_SESSION['theme_mode'];
$user_id = $_SESSION['user_id'];
$error = "";

// Kunin lahat ng assigned CDC ng logged-in CDW
$cdc_result = $conn->query("
    SELECT c.cdc_id, c.cdc_name, c.barangay
    FROM cdw_assignments ca
    JOIN cdc c ON ca.cdc_id = c.cdc_id
    WHERE ca.user_id = '$user_id'
    ORDER BY c.cdc_name ASC
");

// Switch active CDC
if(isset($_POST['switch_cdc'])){
    $selected_cdc_id = $_POST['cdc_id'];

    $check = $conn->query("
        SELECT * FROM cdw_assignments
        WHERE user_id = '$user_id' AND cdc_id = '$selected_cdc_id'
    ");

    if($check && $check->num_rows > 0){
        $_SESSION['active_cdc_id'] = $selected_cdc_id;

        $cdc_info = $conn->query("
            SELECT cdc_name, barangay
            FROM cdc
            WHERE cdc_id = '$selected_cdc_id'
        ");

        if($cdc_info && $cdc_info->num_rows > 0){
            $cdc_row = $cdc_info->fetch_assoc();
            $_SESSION['active_cdc_name'] = $cdc_row['cdc_name'];
            $_SESSION['active_cdc_barangay'] = $cdc_row['barangay'];
        }

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid CDC selection.";
    }
}

/*
|--------------------------------------------------------------------------
| DASHBOARD COUNTS
|--------------------------------------------------------------------------
| - Only children under the currently selected / switched CDC
| - Total Child Enrolled in = total children under active CDC
| - Nutritional counts = latest anthropometric record only per child
| - Option A:
|   Normal only if wfa_status, hfa_status, and wflh_status are all Normal
|--------------------------------------------------------------------------
*/

$total_children_count = 0;
$normal_count = 0;
$underweight_count = 0;
$severely_underweight_count = 0;
$overweight_count = 0;
$obese_count = 0;
$stunted_count = 0;
$severely_stunted_count = 0;
$moderately_wasted_count = 0;
$severely_wasted_count = 0;

/*
|--------------------------------------------------------------------------
| FOOD GROUP GRAPH DATA
|--------------------------------------------------------------------------
| - Active/switched CDC only
| - Count of food group entries from feeding_record_items
| - NO milk_feeding_records involved
| - Show all food groups even if zero
|--------------------------------------------------------------------------
*/
$food_group_data = [];
$food_group_max = 1;

/*
|--------------------------------------------------------------------------
| NUTRITIONAL STATUS GRAPH DATA
|--------------------------------------------------------------------------
| - Based on current dashboard counts
|--------------------------------------------------------------------------
*/
$nutritional_graph_data = [];

if(isset($_SESSION['active_cdc_id']) && !empty($_SESSION['active_cdc_id'])){
    $active_cdc_id = (int) $_SESSION['active_cdc_id'];

    // Total children enrolled in active CDC
    $total_sql = "
        SELECT COUNT(*) AS total_children_count
        FROM children
        WHERE cdc_id = '$active_cdc_id'
    ";
    $total_result = mysqli_query($conn, $total_sql);
    if($total_result){
        $total_row = mysqli_fetch_assoc($total_result);
        $total_children_count = $total_row['total_children_count'] ?? 0;
    }

    // Latest nutritional status counts per child under active CDC only
    $summary_sql = "
        SELECT
            SUM(
                CASE
                    WHEN ar.wfa_status = 'Normal'
                     AND ar.hfa_status = 'Normal'
                     AND ar.wflh_status = 'Normal'
                    THEN 1 ELSE 0
                END
            ) AS normal_count,

            SUM(CASE WHEN ar.wfa_status = 'Underweight' THEN 1 ELSE 0 END) AS underweight_count,
            SUM(CASE WHEN ar.wfa_status = 'Severely Underweight' THEN 1 ELSE 0 END) AS severely_underweight_count,

            SUM(CASE WHEN ar.hfa_status = 'Stunted' THEN 1 ELSE 0 END) AS stunted_count,
            SUM(CASE WHEN ar.hfa_status = 'Severely Stunted' THEN 1 ELSE 0 END) AS severely_stunted_count,

            SUM(CASE WHEN ar.wflh_status = 'Moderately Wasted' THEN 1 ELSE 0 END) AS moderately_wasted_count,
            SUM(CASE WHEN ar.wflh_status = 'Severely Wasted' THEN 1 ELSE 0 END) AS severely_wasted_count,
            SUM(CASE WHEN ar.wflh_status = 'Overweight' THEN 1 ELSE 0 END) AS overweight_count,
            SUM(CASE WHEN ar.wflh_status = 'Obese' THEN 1 ELSE 0 END) AS obese_count

        FROM anthropometric_records ar
        INNER JOIN children c ON ar.child_id = c.child_id

        INNER JOIN (
            SELECT ar2.child_id, MAX(ar2.date_recorded) AS latest_date
            FROM anthropometric_records ar2
            INNER JOIN children c2 ON ar2.child_id = c2.child_id
            WHERE c2.cdc_id = '$active_cdc_id'
            GROUP BY ar2.child_id
        ) latest
            ON ar.child_id = latest.child_id
            AND ar.date_recorded = latest.latest_date

        INNER JOIN (
            SELECT ar3.child_id, ar3.date_recorded, MAX(ar3.record_id) AS latest_record_id
            FROM anthropometric_records ar3
            INNER JOIN children c3 ON ar3.child_id = c3.child_id
            WHERE c3.cdc_id = '$active_cdc_id'
            GROUP BY ar3.child_id, ar3.date_recorded
        ) latest_id
            ON ar.child_id = latest_id.child_id
            AND ar.date_recorded = latest_id.date_recorded
            AND ar.record_id = latest_id.latest_record_id

        WHERE c.cdc_id = '$active_cdc_id'
    ";

    $summary_result = mysqli_query($conn, $summary_sql);

    if($summary_result){
        $summary = mysqli_fetch_assoc($summary_result);

        $normal_count = $summary['normal_count'] ?? 0;
        $underweight_count = $summary['underweight_count'] ?? 0;
        $severely_underweight_count = $summary['severely_underweight_count'] ?? 0;
        $overweight_count = $summary['overweight_count'] ?? 0;
        $obese_count = $summary['obese_count'] ?? 0;
        $stunted_count = $summary['stunted_count'] ?? 0;
        $severely_stunted_count = $summary['severely_stunted_count'] ?? 0;
        $moderately_wasted_count = $summary['moderately_wasted_count'] ?? 0;
        $severely_wasted_count = $summary['severely_wasted_count'] ?? 0;
    }

    // Food group consumption graph data
    // NOTE: This counts ONLY food group entries from feeding modules.
    // It does NOT use milk_feeding_records.
    $food_sql = "
        SELECT
            fg.food_group_id,
            fg.food_group_name,
            SUM(
                CASE
                    WHEN c.cdc_id = '$active_cdc_id' THEN 1
                    ELSE 0
                END
            ) AS total_count
        FROM food_groups fg
        LEFT JOIN feeding_record_items fri
            ON fg.food_group_id = fri.food_group_id
        LEFT JOIN feeding_records fr
            ON fri.feeding_record_id = fr.feeding_record_id
        LEFT JOIN children c
            ON fr.child_id = c.child_id
        GROUP BY fg.food_group_id, fg.food_group_name
        ORDER BY fg.food_group_id ASC
    ";

    $food_result = mysqli_query($conn, $food_sql);

    if($food_result){
        while($row = mysqli_fetch_assoc($food_result)){
            $food_group_data[] = $row;
            if((int)$row['total_count'] > $food_group_max){
                $food_group_max = (int)$row['total_count'];
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Build Nutritional Status Graph Data
|--------------------------------------------------------------------------
*/
$nutritional_graph_data = [
    ['label' => 'Normal', 'count' => (int)$normal_count, 'class' => 'bar-mix-1'],
    ['label' => 'Underweight', 'count' => (int)$underweight_count, 'class' => 'bar-mix-2'],
    ['label' => 'Severely Underweight', 'count' => (int)$severely_underweight_count, 'class' => 'bar-mix-3'],
    ['label' => 'Overweight', 'count' => (int)$overweight_count, 'class' => 'bar-mix-4'],
    ['label' => 'Obese', 'count' => (int)$obese_count, 'class' => 'bar-mix-5'],
    ['label' => 'Stunted', 'count' => (int)$stunted_count, 'class' => 'bar-mix-6'],
    ['label' => 'Severely Stunted', 'count' => (int)$severely_stunted_count, 'class' => 'bar-mix-7'],
    ['label' => 'Moderately Wasted', 'count' => (int)$moderately_wasted_count, 'class' => 'bar-mix-8'],
    ['label' => 'Severely Wasted', 'count' => (int)$severely_wasted_count, 'class' => 'bar-mix-9']
];

$nutritional_graph_max = 1;
foreach($nutritional_graph_data as $item){
    if($item['count'] > $nutritional_graph_max){
        $nutritional_graph_max = $item['count'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>CDW Dashboard | NutriTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/cdw-style.css">
    <link rel="stylesheet" href="../assets/cdw-dashboard.css">
    <link rel="stylesheet" href="../assets/admin-topbar-notification.css">
</head>
<body class="<?php echo ($theme_mode === 'dark') ? 'dark-mode' : ''; ?>">
<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<div class="main-content" id="mainContent">

    <div class="page-card">
        <?php if(!empty($error)){ ?>
            <p class="error-msg"><?php echo $error; ?></p>
        <?php } ?>

        <div class="cdc-switch-area">
            <div class="cdc-title">
                <?php
                if(isset($_SESSION['active_cdc_name']) && !empty($_SESSION['active_cdc_name'])){
                    echo htmlspecialchars($_SESSION['active_cdc_name']);
                } else {
                    echo "NO ACTIVE CDC";
                }
                ?>
            </div>

            <form method="POST" class="cdc-form">
                <select name="cdc_id" required>
                    <option value="">Select CDC</option>
                    <?php
                    if($cdc_result && $cdc_result->num_rows > 0){
                        while($cdc = $cdc_result->fetch_assoc()){
                    ?>
                        <option value="<?php echo $cdc['cdc_id']; ?>"
                            <?php
                            if(isset($_SESSION['active_cdc_id']) && $_SESSION['active_cdc_id'] == $cdc['cdc_id']){
                                echo "selected";
                            }
                            ?>>
                            <?php echo htmlspecialchars($cdc['cdc_name'] . " - " . $cdc['barangay']); ?>
                        </option>
                    <?php
                        }
                    }
                    ?>
                </select>
                <button type="submit" name="switch_cdc">Switch</button>
            </form>

            <?php if(isset($_SESSION['active_cdc_name'])){ ?>
                <div class="active-cdc-text">
                    Active CDC:
                    <?php echo htmlspecialchars($_SESSION['active_cdc_name']); ?>
                    <?php
                    if(isset($_SESSION['active_cdc_barangay']) && !empty($_SESSION['active_cdc_barangay'])){
                        echo " - " . htmlspecialchars($_SESSION['active_cdc_barangay']);
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="page-card">
        <div class="cards-grid">
            <div class="card">
                <div class="card-title title-blue">Total Child Enrolled in</div>
                <div class="card-body">
                    <div class="card-count"><?php echo $total_children_count; ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title title-normal">Normal</div>
                <div class="card-body">
                    <div class="card-count"><?php echo $normal_count; ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Underweight</div>
                <div class="card-body">
                    <div class="card-count"><?php echo $underweight_count; ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Severely Underweight</div>
                <div class="card-body">
                    <div class="card-count"><?php echo $severely_underweight_count; ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Overweight</div>
                <div class="card-body">
                    <div class="card-count"><?php echo $overweight_count; ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Obese</div>
                <div class="card-body">
                    <div class="card-count"><?php echo $obese_count; ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Stunted</div>
                <div class="card-body">
                    <div class="card-count"><?php echo $stunted_count; ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Severely Stunted</div>
                <div class="card-body">
                    <div class="card-count"><?php echo $severely_stunted_count; ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Moderately Wasted</div>
                <div class="card-body">
                    <div class="card-count"><?php echo $moderately_wasted_count; ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title title-alert">Severely Wasted</div>
                <div class="card-body">
                    <div class="card-count"><?php echo $severely_wasted_count; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="chart-section">
        <div class="chart-box">
            <div class="chart-title">
                Summary of Food Group Consumption
                <?php echo isset($_SESSION['active_cdc_name']) ? htmlspecialchars(strtoupper($_SESSION['active_cdc_name'])) : ''; ?>
            </div>

            <div class="fake-chart">
                <?php foreach($food_group_data as $index => $fg){ 
                    $height = 0;
                    if($food_group_max > 0){
                        $height = ((int)$fg['total_count'] / $food_group_max) * 100;
                    }

                    $bar_class = 'bar-green-' . (($index % 8) + 1);
                ?>
                    <div class="fake-bar <?php echo $bar_class; ?>" style="height: <?php echo $height; ?>%;"></div>
                <?php } ?>
            </div>

            <div class="chart-labels food-chart-labels" style="grid-template-columns:repeat(<?php echo count($food_group_data) > 0 ? count($food_group_data) : 1; ?>, 1fr);">
                <?php foreach($food_group_data as $fg){ ?>
                    <div class="food-label"><?php echo htmlspecialchars($fg['food_group_name']); ?></div>
                <?php } ?>
            </div>
        </div>

        <div class="chart-box">
            <div class="chart-title">
                Graphical Representation of the Nutritional Status of Children
                <?php echo isset($_SESSION['active_cdc_name']) ? htmlspecialchars(strtoupper($_SESSION['active_cdc_name'])) : ''; ?>
            </div>

            <div class="fake-chart">
                <?php foreach($nutritional_graph_data as $item){ 
                    $height = 0;
                    if($nutritional_graph_max > 0){
                        $height = ($item['count'] / $nutritional_graph_max) * 100;
                    }
                ?>
                    <div class="fake-bar <?php echo $item['class']; ?>" style="height: <?php echo $height; ?>%;"></div>
                <?php } ?>
            </div>

            <div class="chart-labels nutri">
                <?php foreach($nutritional_graph_data as $item){ ?>
                    <div class="food-label"><?php echo htmlspecialchars($item['label']); ?></div>
                <?php } ?>
            </div>
        </div>
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