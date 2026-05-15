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

$active_cdc_id = (int) $_SESSION['active_cdc_id'];
$user_id = (int) $_SESSION['user_id'];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$feeding_date = isset($_GET['feeding_date']) && !empty($_GET['feeding_date']) ? $_GET['feeding_date'] : date('Y-m-d');
$saved = isset($_GET['saved']) ? 1 : 0;

$success = "";
$error = "";

if($saved){
    $success = "Feeding records saved successfully.";
}

function format_child_name($first_name, $middle_name, $last_name){
    return trim(
        $first_name . ' ' .
        (!empty($middle_name) ? $middle_name . ' ' : '') .
        $last_name
    );
}

function build_menu_key($food_group_id, $food_item_id){
    return $food_group_id . '_' . $food_item_id;
}

function build_feeding_summary($conn, $feeding_record_id){
    $summary_lines = [];

    $stmt = $conn->prepare("
        SELECT fg.food_group_name, fi.food_item_name, fri.measurement_text
        FROM feeding_record_items fri
        INNER JOIN food_groups fg ON fri.food_group_id = fg.food_group_id
        INNER JOIN food_items fi ON fri.food_item_id = fi.food_item_id
        WHERE fri.feeding_record_id = ?
        ORDER BY fg.food_group_name ASC, fi.food_item_name ASC
    ");
    $stmt->bind_param("i", $feeding_record_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result && $result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $summary_lines[] = $row['food_group_name'] . ": " . $row['measurement_text'] . " " . $row['food_item_name'];
        }
    }

    return $summary_lines;
}

/* =========================================================
   LOAD FOOD GROUPS
========================================================= */
$food_groups = [];
$food_groups_result = $conn->query("
    SELECT food_group_id, food_group_name
    FROM food_groups
    ORDER BY food_group_name ASC
");

if($food_groups_result && $food_groups_result->num_rows > 0){
    while($row = $food_groups_result->fetch_assoc()){
        $food_groups[] = $row;
    }
}

/* =========================================================
   LOAD FOOD ITEMS GROUPED
========================================================= */
$food_items_by_group = [];

$food_items_result = $conn->query("
    SELECT food_item_id, food_group_id, food_item_name
    FROM food_items
    ORDER BY food_item_name ASC
");

if($food_items_result && $food_items_result->num_rows > 0){
    while($row = $food_items_result->fetch_assoc()){
        $group_id = (int) $row['food_group_id'];

        if(!isset($food_items_by_group[$group_id])){
            $food_items_by_group[$group_id] = [];
        }

        $food_items_by_group[$group_id][] = $row;
    }
}

/* =========================================================
   LOAD CHILDREN UNDER ACTIVE CDC
========================================================= */
$children = [];

$children_sql = "
    SELECT child_id, first_name, middle_name, last_name, sex
    FROM children
    WHERE cdc_id = ?
";

if($search !== ""){
    $children_sql .= " AND (
        first_name LIKE ? OR
        middle_name LIKE ? OR
        last_name LIKE ?
    )";
}

$children_sql .= " ORDER BY first_name ASC, last_name ASC";

$children_stmt = $conn->prepare($children_sql);

if($search !== ""){
    $search_param = "%" . $search . "%";
    $children_stmt->bind_param("isss", $active_cdc_id, $search_param, $search_param, $search_param);
} else {
    $children_stmt->bind_param("i", $active_cdc_id);
}

$children_stmt->execute();
$children_result = $children_stmt->get_result();

if($children_result && $children_result->num_rows > 0){
    while($row = $children_result->fetch_assoc()){
        $children[] = $row;
    }
}

/* =========================================================
   DEFAULT / LOADED DATA
========================================================= */
$selected_meal_rows = [];
$existing_records = [];
$existing_measurements = [];
$default_measurements = [];

/* =========================================================
   LOAD SAVED DATA FOR SELECTED DATE
========================================================= */
$record_stmt = $conn->prepare("
    SELECT fr.feeding_record_id, fr.child_id, fr.attendance, fr.remarks
    FROM feeding_records fr
    INNER JOIN children c ON fr.child_id = c.child_id
    WHERE fr.feeding_date = ? AND c.cdc_id = ?
");
$record_stmt->bind_param("si", $feeding_date, $active_cdc_id);
$record_stmt->execute();
$record_result = $record_stmt->get_result();

if($record_result && $record_result->num_rows > 0){
    while($row = $record_result->fetch_assoc()){
        $existing_records[$row['child_id']] = $row;
    }
}

$meal_stmt = $conn->prepare("
    SELECT DISTINCT
        fri.food_group_id,
        fg.food_group_name,
        fri.food_item_id,
        fi.food_item_name
    FROM feeding_record_items fri
    INNER JOIN feeding_records fr ON fri.feeding_record_id = fr.feeding_record_id
    INNER JOIN children c ON fr.child_id = c.child_id
    INNER JOIN food_groups fg ON fri.food_group_id = fg.food_group_id
    INNER JOIN food_items fi ON fri.food_item_id = fi.food_item_id
    WHERE fr.feeding_date = ? AND c.cdc_id = ?
    ORDER BY fg.food_group_name ASC, fi.food_item_name ASC
");
$meal_stmt->bind_param("si", $feeding_date, $active_cdc_id);
$meal_stmt->execute();
$meal_result = $meal_stmt->get_result();

if($meal_result && $meal_result->num_rows > 0){
    while($row = $meal_result->fetch_assoc()){
        $selected_meal_rows[] = [
            'food_group_id' => (int) $row['food_group_id'],
            'food_item_id' => (int) $row['food_item_id'],
            'food_group_name' => $row['food_group_name'],
            'food_item_name' => $row['food_item_name']
        ];
    }
}

$measurement_stmt = $conn->prepare("
    SELECT
        fr.child_id,
        fri.food_group_id,
        fri.food_item_id,
        fri.measurement_text
    FROM feeding_record_items fri
    INNER JOIN feeding_records fr ON fri.feeding_record_id = fr.feeding_record_id
    INNER JOIN children c ON fr.child_id = c.child_id
    WHERE fr.feeding_date = ? AND c.cdc_id = ?
    ORDER BY fr.child_id ASC, fri.feeding_item_id ASC
");
$measurement_stmt->bind_param("si", $feeding_date, $active_cdc_id);
$measurement_stmt->execute();
$measurement_result = $measurement_stmt->get_result();

if($measurement_result && $measurement_result->num_rows > 0){
    while($row = $measurement_result->fetch_assoc()){
        $child_id = (int) $row['child_id'];
        $menu_key = build_menu_key((int)$row['food_group_id'], (int)$row['food_item_id']);

        if(!isset($existing_measurements[$child_id])){
            $existing_measurements[$child_id] = [];
        }

        $existing_measurements[$child_id][$menu_key] = $row['measurement_text'];

        if(!isset($default_measurements[$menu_key]) || $default_measurements[$menu_key] === ''){
            $default_measurements[$menu_key] = $row['measurement_text'];
        }
    }
}

if(empty($selected_meal_rows)){
    $selected_meal_rows[] = [
        'food_group_id' => 0,
        'food_item_id' => 0,
        'food_group_name' => '',
        'food_item_name' => ''
    ];
}

/* =========================================================
   SAVE / UPDATE
========================================================= */
if(isset($_POST['save_feeding'])){
    $feeding_date = trim($_POST['feeding_date']);
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';

    $child_ids = isset($_POST['child_ids']) ? $_POST['child_ids'] : [];
    $attendance_list = isset($_POST['attendance']) ? $_POST['attendance'] : [];
    $remarks_list = isset($_POST['remarks']) ? $_POST['remarks'] : [];
    $posted_measurements = isset($_POST['measurements']) ? $_POST['measurements'] : [];

    $food_group_ids = isset($_POST['food_group_id']) ? $_POST['food_group_id'] : [];
    $food_item_ids = isset($_POST['food_item_id']) ? $_POST['food_item_id'] : [];
    $default_measurement_texts = isset($_POST['default_measurement_text']) ? $_POST['default_measurement_text'] : [];

    $selected_meal_rows = [];
    $existing_measurements = $posted_measurements;
    $default_measurements = [];

    for($i = 0; $i < count($food_group_ids); $i++){
        $food_group_id = isset($food_group_ids[$i]) ? (int) $food_group_ids[$i] : 0;
        $food_item_id = isset($food_item_ids[$i]) ? (int) $food_item_ids[$i] : 0;
        $default_measurement = isset($default_measurement_texts[$i]) ? trim($default_measurement_texts[$i]) : '';

        if($food_group_id > 0 && $food_item_id > 0){
            $menu_key = build_menu_key($food_group_id, $food_item_id);

            if(!isset($selected_meal_rows[$menu_key])){
                $food_group_name = '';
                $food_item_name = '';

                foreach($food_groups as $fg){
                    if((int)$fg['food_group_id'] === $food_group_id){
                        $food_group_name = $fg['food_group_name'];
                        break;
                    }
                }

                if(isset($food_items_by_group[$food_group_id])){
                    foreach($food_items_by_group[$food_group_id] as $fi){
                        if((int)$fi['food_item_id'] === $food_item_id){
                            $food_item_name = $fi['food_item_name'];
                            break;
                        }
                    }
                }

                $selected_meal_rows[$menu_key] = [
                    'food_group_id' => $food_group_id,
                    'food_item_id' => $food_item_id,
                    'food_group_name' => $food_group_name,
                    'food_item_name' => $food_item_name
                ];

                $default_measurements[$menu_key] = $default_measurement;
            }
        }
    }

    $selected_meal_rows = array_values($selected_meal_rows);

    if(empty($feeding_date)){
        $error = "Feeding date is required.";
    } elseif(empty($child_ids)){
        $error = "No pupils found under the selected CDC.";
    } else {
        $has_present = false;

        foreach($child_ids as $child_id){
            $child_id = (int) $child_id;
            $attendance_value = isset($attendance_list[$child_id]) ? $attendance_list[$child_id] : 'Absent';

            if($attendance_value === 'Present'){
                $has_present = true;
                break;
            }
        }

        if($has_present && empty($selected_meal_rows)){
            $error = "Please add at least one meal item for the selected date.";
        }

        if(empty($error)){
            foreach($selected_meal_rows as $meal_row){
                $menu_key = build_menu_key($meal_row['food_group_id'], $meal_row['food_item_id']);
                $default_measurement = isset($default_measurements[$menu_key]) ? trim($default_measurements[$menu_key]) : '';

                if($default_measurement === ''){
                    $error = "Please complete all default measurement/serving fields in the Meal Setup for the Day section.";
                    break;
                }
            }
        }

        if(empty($error) && $has_present){
            foreach($child_ids as $child_id){
                $child_id = (int) $child_id;
                $attendance_value = isset($attendance_list[$child_id]) ? $attendance_list[$child_id] : 'Absent';

                if($attendance_value === 'Present'){
                    foreach($selected_meal_rows as $meal_row){
                        $menu_key = build_menu_key($meal_row['food_group_id'], $meal_row['food_item_id']);
                        $measurement_value = isset($posted_measurements[$child_id][$menu_key]) ? trim($posted_measurements[$child_id][$menu_key]) : '';

                        if($measurement_value === ''){
                            $error = "Please complete all editable serving fields for pupils marked as Present.";
                            break 2;
                        }
                    }
                }
            }
        }

        if(empty($error)){
            $conn->begin_transaction();

            try{
                $check_child_stmt = $conn->prepare("
                    SELECT child_id
                    FROM children
                    WHERE child_id = ? AND cdc_id = ?
                    AND is_deleted = 0
                    LIMIT 1
                ");

                $check_record_stmt = $conn->prepare("
                    SELECT feeding_record_id
                    FROM feeding_records
                    WHERE child_id = ? AND feeding_date = ?
                    LIMIT 1
                ");

                $insert_record_stmt = $conn->prepare("
                    INSERT INTO feeding_records (
                        child_id, feeding_date, attendance, remarks, recorded_by
                    ) VALUES (?, ?, ?, ?, ?)
                ");

                $update_record_stmt = $conn->prepare("
                    UPDATE feeding_records
                    SET attendance = ?, remarks = ?, recorded_by = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE feeding_record_id = ?
                ");

                $delete_items_stmt = $conn->prepare("
                    DELETE FROM feeding_record_items
                    WHERE feeding_record_id = ?
                ");

                $insert_item_stmt = $conn->prepare("
                    INSERT INTO feeding_record_items (
                        feeding_record_id, food_group_id, food_item_id, measurement_text, quantity
                    ) VALUES (?, ?, ?, ?, 1.00)
                ");

                foreach($child_ids as $child_id){
                    $child_id = (int) $child_id;

                    $check_child_stmt->bind_param("ii", $child_id, $active_cdc_id);
                    $check_child_stmt->execute();
                    $check_child_result = $check_child_stmt->get_result();

                    if($check_child_result->num_rows == 0){
                        continue;
                    }

                    $attendance_value = isset($attendance_list[$child_id]) ? $attendance_list[$child_id] : 'Absent';
                    $remarks_value = isset($remarks_list[$child_id]) ? trim($remarks_list[$child_id]) : '';

                    if($attendance_value === 'Absent' && $remarks_value === ''){
                        $remarks_value = 'Absent';
                    }

                    $check_record_stmt->bind_param("is", $child_id, $feeding_date);
                    $check_record_stmt->execute();
                    $check_record_result = $check_record_stmt->get_result();

                    if($check_record_result && $check_record_result->num_rows > 0){
                        $existing_record = $check_record_result->fetch_assoc();
                        $feeding_record_id = (int) $existing_record['feeding_record_id'];

                        $update_record_stmt->bind_param(
                            "ssii",
                            $attendance_value,
                            $remarks_value,
                            $user_id,
                            $feeding_record_id
                        );
                        $update_record_stmt->execute();

                        $delete_items_stmt->bind_param("i", $feeding_record_id);
                        $delete_items_stmt->execute();
                    } else {
                        $insert_record_stmt->bind_param(
                            "isssi",
                            $child_id,
                            $feeding_date,
                            $attendance_value,
                            $remarks_value,
                            $user_id
                        );
                        $insert_record_stmt->execute();

                        $feeding_record_id = $conn->insert_id;
                    }

                    if($attendance_value === 'Present'){
                        foreach($selected_meal_rows as $meal_row){
                            $menu_key = build_menu_key($meal_row['food_group_id'], $meal_row['food_item_id']);
                            $measurement_value = trim($posted_measurements[$child_id][$menu_key]);

                            $insert_item_stmt->bind_param(
                                "iiis",
                                $feeding_record_id,
                                $meal_row['food_group_id'],
                                $meal_row['food_item_id'],
                                $measurement_value
                            );
                            $insert_item_stmt->execute();
                        }
                    }
                }

                $conn->commit();

                $redirect_url = "feeding_list.php?feeding_date=" . urlencode($feeding_date) . "&saved=1";
                if($search !== ""){
                    $redirect_url .= "&search=" . urlencode($search);
                }

                header("Location: " . $redirect_url);
                exit();

            } catch(Exception $e){
                $conn->rollback();
                $error = "Error saving feeding records: " . $e->getMessage();
            }
        }
    }

    if(empty($selected_meal_rows)){
        $selected_meal_rows[] = [
            'food_group_id' => 0,
            'food_item_id' => 0,
            'food_group_name' => '',
            'food_item_name' => ''
        ];
    }
}

/* =========================================================
   RECENT FEEDING RECORDS
========================================================= */
$recent_records = [];

$recent_stmt = $conn->prepare("
    SELECT
        fr.feeding_record_id,
        fr.feeding_date,
        fr.attendance,
        fr.remarks,
        c.first_name,
        c.middle_name,
        c.last_name
    FROM feeding_records fr
    INNER JOIN children c ON fr.child_id = c.child_id
    WHERE c.cdc_id = ?
    ORDER BY fr.feeding_date DESC, fr.feeding_record_id DESC
    LIMIT 20
");
$recent_stmt->bind_param("i", $active_cdc_id);
$recent_stmt->execute();
$recent_result = $recent_stmt->get_result();

if($recent_result && $recent_result->num_rows > 0){
    while($row = $recent_result->fetch_assoc()){
        $recent_records[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplementary Feeding | NutriTrack</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/cdw/cdw-style.css?v=6">
    <link rel="stylesheet" href="../assets/cdw/feeding.css?v=6">
    <link rel="stylesheet" href="../assets/cdw/cdw-topbar-notification.css?v=6">
</head>
<?php include __DIR__ . '/../includes/auth.php'; ?>
<body class="<?php echo themeClass(); ?>">

<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-wrapper">

        <div class="page-header">
            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            <h1 class="page-title">Supplementary Feeding</h1>
            <div class="page-subtitle">
                Record and update feeding data by selected date for pupils under your active CDC.
            </div>
        </div>

        <div class="cdc-banner">
            <div class="cdc-banner-title">
                <?php echo htmlspecialchars(isset($_SESSION['active_cdc_name']) ? strtoupper($_SESSION['active_cdc_name']) : 'NO ACTIVE CDC'); ?>
            </div>
            <?php if(isset($_SESSION['active_cdc_barangay']) && !empty($_SESSION['active_cdc_barangay'])){ ?>
                <div class="cdc-banner-subtitle">
                    <?php echo htmlspecialchars($_SESSION['active_cdc_barangay']); ?>
                </div>
            <?php } ?>
        </div>

        <?php if(!empty($success)){ ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>

        <?php if(!empty($error)){ ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="POST" id="feedingForm">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">

            <div class="content-card">
                <div class="card-header">Supplementary Feeding Form</div>
                <div class="card-body">
                    <div class="form-grid feeding-top-grid">
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input
                                type="date"
                                name="feeding_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($feeding_date); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Search Pupils</label>
                            <input
                                type="text"
                                id="searchPupilInput"
                                class="form-control"
                                placeholder="Search pupils"
                                value="<?php echo htmlspecialchars($search); ?>"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">Meal Setup for the Day</div>
                <div class="card-body">
                    <div class="card-note">
                        Set the meal items and default serving for the selected date. The default serving will automatically appear per pupil below, but you can still edit it per pupil if needed.
                    </div>

                    <div id="food-items-wrapper">
                        <?php foreach($selected_meal_rows as $meal_row){ ?>
                            <?php
                                $menu_key = build_menu_key($meal_row['food_group_id'], $meal_row['food_item_id']);
                                $default_value = isset($default_measurements[$menu_key]) ? $default_measurements[$menu_key] : '';
                            ?>
                            <div class="food-row">
                                <div class="form-group">
                                    <label class="form-label">Food Group</label>
                                    <select name="food_group_id[]" class="form-control food-group-select">
                                        <option value="">Select Food Group</option>
                                        <?php foreach($food_groups as $group){ ?>
                                            <option value="<?php echo $group['food_group_id']; ?>" <?php echo ((int)$meal_row['food_group_id'] === (int)$group['food_group_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($group['food_group_name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Specific Food Item</label>
                                    <select name="food_item_id[]" class="form-control food-item-select">
                                        <option value="">Select Food Item</option>
                                        <?php
                                            $current_group_id = (int) $meal_row['food_group_id'];
                                            if($current_group_id > 0 && isset($food_items_by_group[$current_group_id])){
                                                foreach($food_items_by_group[$current_group_id] as $item){
                                        ?>
                                            <option value="<?php echo $item['food_item_id']; ?>" <?php echo ((int)$meal_row['food_item_id'] === (int)$item['food_item_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($item['food_item_name']); ?>
                                            </option>
                                        <?php
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Default Measurement / Serving</label>
                                    <input
                                        type="text"
                                        name="default_measurement_text[]"
                                        class="form-control default-measurement-input"
                                        value="<?php echo htmlspecialchars($default_value); ?>"
                                        placeholder="Example: 2pcs, 1 cup, 1/2 cup"
                                    >
                                </div>

                                <div class="form-group remove-col">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="remove-food-btn">Remove</button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="food-actions">
                        <button type="button" id="add-food-row-btn" class="btn-secondary">Add Another Food Item</button>
                    </div>

                    <div class="summary-box">
                        <div class="summary-title">Feeding Summary</div>
                        <div id="feeding-summary-preview" class="summary-preview">
                            No feeding summary yet.
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">Pupils Feeding Attendance and Editable Servings</div>
                <div class="card-body">
                    <?php if(!empty($children)){ ?>
                        <div class="pupil-feed-list" id="pupilFeedList">
                            <?php foreach($children as $child){ ?>
                                <?php
                                    $child_id = (int) $child['child_id'];
                                    $child_full_name = format_child_name($child['first_name'], $child['middle_name'], $child['last_name']);
                                    $saved_attendance = isset($existing_records[$child_id]['attendance']) ? $existing_records[$child_id]['attendance'] : 'Present';
                                    $saved_remarks = isset($existing_records[$child_id]['remarks']) ? $existing_records[$child_id]['remarks'] : '';
                                ?>
                                <div class="pupil-feed-card" data-child-id="<?php echo $child_id; ?>" data-child-name="<?php echo htmlspecialchars(strtolower($child_full_name)); ?>">
                                    <input type="hidden" name="child_ids[]" value="<?php echo $child_id; ?>">

                                    <div class="pupil-feed-top">
                                        <div>
                                            <div class="pupil-feed-name"><?php echo htmlspecialchars($child_full_name); ?></div>
                                            <div class="pupil-feed-meta">Sex: <?php echo htmlspecialchars($child['sex']); ?></div>
                                        </div>

                                        <div class="attendance-wrap">
                                            <label class="small-label">Attendance</label>
                                            <select name="attendance[<?php echo $child_id; ?>]" class="table-select attendance-select">
                                                <option value="Present" <?php echo $saved_attendance == 'Present' ? 'selected' : ''; ?>>Present</option>
                                                <option value="Absent" <?php echo $saved_attendance == 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="meal-inputs-block">
                                        <div class="meal-inputs-title">Editable Serving per Meal Item</div>
                                        <div class="meal-inputs-grid"></div>
                                    </div>

                                    <div class="remarks-block">
                                        <label class="small-label">Remarks</label>
                                        <input
                                            type="text"
                                            name="remarks[<?php echo $child_id; ?>]"
                                            class="table-input remarks-input"
                                            value="<?php echo htmlspecialchars($saved_remarks); ?>"
                                            placeholder="Optional remarks"
                                        >
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="save_feeding" class="btn-save">Save Feeding Records</button>
                        </div>
                    <?php } else { ?>
                        <div class="no-records">No pupils found under the selected CDC.</div>
                    <?php } ?>
                </div>
            </div>
        </form>

        <div class="content-card">
            <div class="card-header">Recent Feeding Records</div>
            <div class="card-body">
                <?php if(!empty($recent_records)){ ?>
                    <div class="recent-records-list">
                        <?php foreach($recent_records as $record){ ?>
                            <?php
                                $recent_full_name = format_child_name(
                                    $record['first_name'],
                                    $record['middle_name'],
                                    $record['last_name']
                                );
                                $summary_lines = build_feeding_summary($conn, $record['feeding_record_id']);
                            ?>
                            <div class="recent-record-card">
                                <div class="recent-record-top">
                                    <div>
                                        <div class="recent-record-name"><?php echo htmlspecialchars($recent_full_name); ?></div>
                                        <div class="recent-record-date"><?php echo date("F d, Y", strtotime($record['feeding_date'])); ?></div>
                                    </div>
                                    <div>
                                        <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $record['attendance'])); ?>">
                                            <?php echo htmlspecialchars($record['attendance']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="recent-record-section">
                                    <div class="recent-section-title">Feeding Summary</div>
                                    <?php if(!empty($summary_lines)){ ?>
                                        <ul class="summary-list">
                                            <?php foreach($summary_lines as $line){ ?>
                                                <li><?php echo htmlspecialchars($line); ?></li>
                                            <?php } ?>
                                        </ul>
                                    <?php } else { ?>
                                        <div class="no-summary">No feeding summary available.</div>
                                    <?php } ?>
                                </div>

                                <div class="recent-record-section">
                                    <div class="recent-section-title">Remarks</div>
                                    <div class="remarks-text">
                                        <?php echo !empty($record['remarks']) ? htmlspecialchars($record['remarks']) : 'No remarks'; ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="no-records">No feeding records found yet.</div>
                <?php } ?>
            </div>
        </div>

    </div>
</div>

<script>
const foodItemsByGroup = <?php echo json_encode($food_items_by_group); ?>;
const existingMeasurements = <?php echo json_encode($existing_measurements); ?>;

function buildMenuKey(groupId, itemId) {
    return String(groupId) + "_" + String(itemId);
}

function populateFoodItems(groupSelect, foodItemSelect, selectedValue = "") {
    const groupId = groupSelect.value;
    foodItemSelect.innerHTML = '<option value="">Select Food Item</option>';

    if (groupId && foodItemsByGroup[groupId]) {
        foodItemsByGroup[groupId].forEach(function(item) {
            const option = document.createElement('option');
            option.value = item.food_item_id;
            option.textContent = item.food_item_name;

            if (String(selectedValue) === String(item.food_item_id)) {
                option.selected = true;
            }

            foodItemSelect.appendChild(option);
        });
    }

    updateSummaryPreview();
}

function getSelectedMealRows() {
    const rows = document.querySelectorAll('.food-row');
    let selected = [];
    let usedKeys = {};

    rows.forEach(function(row) {
        const groupSelect = row.querySelector('.food-group-select');
        const foodItemSelect = row.querySelector('.food-item-select');
        const defaultMeasurementInput = row.querySelector('.default-measurement-input');

        const groupId = groupSelect.value;
        const itemId = foodItemSelect.value;
        const defaultMeasurement = defaultMeasurementInput.value.trim();

        if (groupId !== '' && itemId !== '') {
            const menuKey = buildMenuKey(groupId, itemId);

            if (!usedKeys[menuKey]) {
                usedKeys[menuKey] = true;

                selected.push({
                    key: menuKey,
                    groupId: groupId,
                    itemId: itemId,
                    groupText: groupSelect.options[groupSelect.selectedIndex].text,
                    itemText: foodItemSelect.options[foodItemSelect.selectedIndex].text,
                    defaultMeasurement: defaultMeasurement
                });
            }
        }
    });

    return selected;
}

function updateSummaryPreview() {
    const selectedMeals = getSelectedMealRows();
    const preview = document.getElementById('feeding-summary-preview');

    if (selectedMeals.length === 0) {
        preview.innerHTML = 'No feeding summary yet.';
        return;
    }

    let html = '<ul class="preview-list">';
    selectedMeals.forEach(function(meal) {
        const defaultText = meal.defaultMeasurement !== '' ? meal.defaultMeasurement : '(no default serving)';
        html += '<li>' + meal.groupText + ': ' + defaultText + ' ' + meal.itemText + '</li>';
    });
    html += '</ul>';

    preview.innerHTML = html;
}

function renderMealInputsForAllChildren() {
    const selectedMeals = getSelectedMealRows();

    document.querySelectorAll('.pupil-feed-card').forEach(function(card) {
        const childId = card.getAttribute('data-child-id');
        const container = card.querySelector('.meal-inputs-grid');
        const attendanceSelect = card.querySelector('.attendance-select');
        const remarksInput = card.querySelector('.remarks-input');
        const isAbsent = attendanceSelect.value === 'Absent';

        const oldInputs = container.querySelectorAll('.measurement-input');
        const manualValues = {};

        oldInputs.forEach(function(input) {
            const key = input.getAttribute('data-menu-key');
            manualValues[key] = {
                value: input.value,
                custom: input.getAttribute('data-custom') === '1'
            };
        });

        container.innerHTML = '';

        if (selectedMeals.length === 0) {
            container.innerHTML = '<div class="no-meal-items">No meal items selected yet.</div>';
        } else {
            selectedMeals.forEach(function(meal) {
                const wrapper = document.createElement('div');
                wrapper.className = 'meal-measurement-item';

                const label = document.createElement('label');
                label.className = 'small-label';
                label.textContent = meal.groupText + ': ' + meal.itemText;

                const input = document.createElement('input');
                input.type = 'text';
                input.name = `measurements[${childId}][${meal.key}]`;
                input.className = 'table-input measurement-input';
                input.setAttribute('data-menu-key', meal.key);
                input.placeholder = 'Example: 2pcs, 1 cup, 1/2 cup';

                let inputValue = meal.defaultMeasurement;
                let isCustom = false;

                if (manualValues[meal.key] && manualValues[meal.key].custom) {
                    inputValue = manualValues[meal.key].value;
                    isCustom = true;
                } else if (
                    existingMeasurements[childId] &&
                    typeof existingMeasurements[childId][meal.key] !== 'undefined'
                ) {
                    inputValue = existingMeasurements[childId][meal.key];
                    isCustom = existingMeasurements[childId][meal.key] !== meal.defaultMeasurement;
                }

                input.value = inputValue;
                input.setAttribute('data-custom', isCustom ? '1' : '0');

                input.addEventListener('input', function() {
                    if (input.value === meal.defaultMeasurement) {
                        input.setAttribute('data-custom', '0');
                    } else {
                        input.setAttribute('data-custom', '1');
                    }
                });

                if (isAbsent) {
                    input.disabled = true;
                    input.value = '';
                }

                wrapper.appendChild(label);
                wrapper.appendChild(input);
                container.appendChild(wrapper);
            });
        }

        if (isAbsent) {
            remarksInput.value = 'Absent';
            remarksInput.disabled = true;
        } else {
            if (remarksInput.value === 'Absent') {
                remarksInput.value = '';
            }
            remarksInput.disabled = false;
        }
    });

    updateSummaryPreview();
}

function attachFoodRowEvents(row) {
    const groupSelect = row.querySelector('.food-group-select');
    const foodItemSelect = row.querySelector('.food-item-select');
    const defaultMeasurementInput = row.querySelector('.default-measurement-input');
    const removeBtn = row.querySelector('.remove-food-btn');

    groupSelect.addEventListener('change', function() {
        populateFoodItems(groupSelect, foodItemSelect);
        renderMealInputsForAllChildren();
    });

    foodItemSelect.addEventListener('change', function() {
        renderMealInputsForAllChildren();
    });

    defaultMeasurementInput.addEventListener('input', function() {
        renderMealInputsForAllChildren();
    });

    removeBtn.addEventListener('click', function() {
        const wrapper = document.getElementById('food-items-wrapper');
        const rows = wrapper.querySelectorAll('.food-row');

        if (rows.length > 1) {
            row.remove();
        } else {
            groupSelect.value = '';
            foodItemSelect.innerHTML = '<option value="">Select Food Item</option>';
            defaultMeasurementInput.value = '';
        }

        renderMealInputsForAllChildren();
    });
}

document.querySelectorAll('.food-row').forEach(function(row) {
    attachFoodRowEvents(row);
});

document.getElementById('add-food-row-btn').addEventListener('click', function() {
    const wrapper = document.getElementById('food-items-wrapper');

    let groupOptions = '<option value="">Select Food Group</option>';
    <?php foreach($food_groups as $group){ ?>
        groupOptions += '<option value="<?php echo $group["food_group_id"]; ?>"><?php echo htmlspecialchars($group["food_group_name"], ENT_QUOTES); ?></option>';
    <?php } ?>

    const row = document.createElement('div');
    row.className = 'food-row';
    row.innerHTML = `
        <div class="form-group">
            <label class="form-label">Food Group</label>
            <select name="food_group_id[]" class="form-control food-group-select">
                ${groupOptions}
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Specific Food Item</label>
            <select name="food_item_id[]" class="form-control food-item-select">
                <option value="">Select Food Item</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Default Measurement / Serving</label>
            <input type="text" name="default_measurement_text[]" class="form-control default-measurement-input" placeholder="Example: 2pcs, 1 cup, 1/2 cup">
        </div>

        <div class="form-group remove-col">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="remove-food-btn">Remove</button>
        </div>
    `;

    wrapper.appendChild(row);
    attachFoodRowEvents(row);
    renderMealInputsForAllChildren();
});

document.querySelectorAll('.attendance-select').forEach(function(select) {
    select.addEventListener('change', function() {
        renderMealInputsForAllChildren();
    });
});

const searchPupilInput = document.getElementById('searchPupilInput');
if (searchPupilInput) {
    searchPupilInput.addEventListener('input', function() {
        const keyword = this.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.pupil-feed-card');

        cards.forEach(function(card) {
            const childName = card.getAttribute('data-child-name');

            if (childName.includes(keyword)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
}

function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');

    if (window.innerWidth <= 991) {
        sidebar.classList.toggle('open');
    } else {
        sidebar.classList.toggle('closed');
        mainContent.classList.toggle('full');
    }
}

renderMealInputsForAllChildren();
</script>
<script src="../assets/cdw/sidebar.js"></script>
</body>
</html>