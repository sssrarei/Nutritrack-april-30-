<?php
include '../includes/auth.php';
include '../config/database.php';
include '../includes/intervention_helper.php';

if ($_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

function decodeReportPayload($payload) {
    if (is_array($payload)) {
        return $payload;
    }

    if (is_string($payload) && !empty($payload)) {
        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : [];
    }

    return [];
}

function determineFinalInterventionCategory($wfa_status, $wflh_status) {
    $candidates = [];

    $mapped_wfa = mapToInterventionCategory(trim((string)$wfa_status));
    $mapped_wflh = mapToInterventionCategory(trim((string)$wflh_status));

    if ($mapped_wfa !== null) {
        $candidates[] = $mapped_wfa;
    }

    if ($mapped_wflh !== null) {
        $candidates[] = $mapped_wflh;
    }

    if (empty($candidates)) {
        return null;
    }

    $priority = [
        'Overweight' => 1,
        'Moderately Wasted' => 2,
        'Obese' => 3,
        'Severely Wasted' => 4
    ];

    $final_category = null;
    $highest_priority = 0;

    foreach ($candidates as $candidate) {
        $candidate_priority = isset($priority[$candidate]) ? $priority[$candidate] : 0;

        if ($candidate_priority > $highest_priority) {
            $highest_priority = $candidate_priority;
            $final_category = $candidate;
        }
    }

    return $final_category;
}

function getLatestSubmittedWMRRowsPerCDC($conn, $selected_cdc = 0) {
    $sql = "SELECT submitted_report_id, cdc_id, submitted_at, report_payload
            FROM submitted_reports
            WHERE LOWER(report_type) = 'wmr'
            ORDER BY cdc_id ASC, submitted_at DESC, submitted_report_id DESC";

    $result = $conn->query($sql);

    $latest_reports = [];
    $rows = [];

    if ($result && $result->num_rows > 0) {
        while ($report = $result->fetch_assoc()) {
            $cdc_id = (int)$report['cdc_id'];

            if ($selected_cdc > 0 && $cdc_id !== $selected_cdc) {
                continue;
            }

            if (isset($latest_reports[$cdc_id])) {
                continue;
            }

            $latest_reports[$cdc_id] = $report;

            $payload = decodeReportPayload($report['report_payload']);
            $submitted_rows = isset($payload['submitted_rows']) && is_array($payload['submitted_rows'])
                ? $payload['submitted_rows']
                : [];

            foreach ($submitted_rows as $row) {
                $row['submitted_report_id'] = $report['submitted_report_id'];
                $row['cdc_id'] = $cdc_id;
                $row['submitted_at'] = $report['submitted_at'];
                $rows[] = $row;
            }
        }
    }

    return $rows;
}

function getChildSubmittedWMRHistory($conn, $child_id, $cdc_id) {
    $sql = "SELECT submitted_report_id, submitted_at, report_payload
            FROM submitted_reports
            WHERE LOWER(report_type) = 'wmr' AND cdc_id = ?
            ORDER BY submitted_at ASC, submitted_report_id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cdc_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];

    while ($report = $result->fetch_assoc()) {
        $payload = decodeReportPayload($report['report_payload']);
        $submitted_rows = isset($payload['submitted_rows']) && is_array($payload['submitted_rows'])
            ? $payload['submitted_rows']
            : [];

        foreach ($submitted_rows as $row) {
            if (!isset($row['child_id']) || (int)$row['child_id'] !== (int)$child_id) {
                continue;
            }

            $final_category = determineFinalInterventionCategory(
                isset($row['wfa_status']) ? $row['wfa_status'] : '',
                isset($row['wflh_status']) ? $row['wflh_status'] : ''
            );

            if ($final_category !== null) {
                $history[] = [
                    'date_recorded' => isset($row['date_recorded']) ? $row['date_recorded'] : $report['submitted_at'],
                    'intervention_category' => $final_category
                ];
            }

            break;
        }
    }

    return $history;
}

$message = '';
$message_type = '';

$selected_cdc = isset($_GET['cdc_id']) ? intval($_GET['cdc_id']) : 0;
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : '';
$show_preview = false;
$preview_children = [];
$preview_guidance_rules = [];
$preview_guidance_text = '';
$optional_note = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['preview_batch'])) {
        $selected_cdc = isset($_POST['cdc_id']) ? intval($_POST['cdc_id']) : 0;
        $selected_category = isset($_POST['category']) ? trim($_POST['category']) : '';

        if (!empty($selected_category)) {
            $show_preview = true;
        }
    }

    if (isset($_POST['confirm_batch'])) {
        $selected_cdc = isset($_POST['cdc_id']) ? intval($_POST['cdc_id']) : 0;
        $selected_category = isset($_POST['category']) ? trim($_POST['category']) : '';
        $optional_note = isset($_POST['optional_note']) ? trim($_POST['optional_note']) : '';
        $reviewed_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        $latest_wmr_rows = getLatestSubmittedWMRRowsPerCDC($conn, $selected_cdc);
        $children_to_save = [];

        foreach ($latest_wmr_rows as $row) {
            $final_category = determineFinalInterventionCategory(
                isset($row['wfa_status']) ? $row['wfa_status'] : '',
                isset($row['wflh_status']) ? $row['wflh_status'] : ''
            );

            if ($final_category !== $selected_category) {
                continue;
            }

            $row['intervention_category'] = $final_category;
            $children_to_save[] = $row;
        }

        if (!empty($children_to_save)) {
            $rules = getInterventionGuidanceRules($selected_category);
            $guidance_text = buildGuidanceText($rules);
            $saved_count = 0;

            foreach ($children_to_save as $child) {
                $child_id = isset($child['child_id']) ? (int)$child['child_id'] : 0;
                $record_id = isset($child['record_id']) ? (int)$child['record_id'] : 0;
                $submitted_report_id = isset($child['submitted_report_id']) ? (int)$child['submitted_report_id'] : null;
                $original_status = '';

                if ($selected_category === 'Moderately Wasted') {
                    if (isset($child['wflh_status']) && $child['wflh_status'] === 'Moderately Wasted') {
                        $original_status = 'Moderately Wasted';
                    } elseif (isset($child['wfa_status']) && $child['wfa_status'] === 'Underweight') {
                        $original_status = 'Underweight';
                    }
                } elseif ($selected_category === 'Severely Wasted') {
                    if (isset($child['wflh_status']) && $child['wflh_status'] === 'Severely Wasted') {
                        $original_status = 'Severely Wasted';
                    } elseif (isset($child['wfa_status']) && $child['wfa_status'] === 'Severely Underweight') {
                        $original_status = 'Severely Underweight';
                    }
                } elseif ($selected_category === 'Overweight') {
                    $original_status = isset($child['wflh_status']) ? $child['wflh_status'] : 'Overweight';
                } elseif ($selected_category === 'Obese') {
                    $original_status = isset($child['wflh_status']) ? $child['wflh_status'] : 'Obese';
                }

                $history = getChildSubmittedWMRHistory($conn, $child_id, (int)$child['cdc_id']);

                $is_at_risk = 0;
                $needs_counseling = 0;
                $needs_referral = 0;
                $status_note = 'Generated from latest submitted WMR';

                if (checkNoImprovementForTwoMonths($history)) {
                    $is_at_risk = 1;
                    $needs_counseling = 1;
                    $needs_referral = 1;
                    $status_note = 'No improvement for 2 consecutive submitted WMR periods';
                }

                $check_sql = "SELECT guidance_id
                              FROM intervention_guidance
                              WHERE child_id = ? AND record_id = ? AND intervention_category = ?
                              LIMIT 1";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("iis", $child_id, $record_id, $selected_category);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $existing = $check_result->fetch_assoc();
                    $guidance_id = (int)$existing['guidance_id'];

                    $update_sql = "UPDATE intervention_guidance
                                   SET submitted_report_id = ?,
                                       original_status = ?,
                                       guidance_text = ?,
                                       optional_note = ?,
                                       is_at_risk = ?,
                                       needs_counseling = ?,
                                       needs_referral = ?,
                                       reviewed_by = ?,
                                       is_reviewed = 1,
                                       sent_to_guardian = 1,
                                       sent_at = NOW(),
                                       status_note = ?,
                                       updated_at = CURRENT_TIMESTAMP
                                   WHERE guidance_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param(
                        "isssiiiisi",
                        $submitted_report_id,
                        $original_status,
                        $guidance_text,
                        $optional_note,
                        $is_at_risk,
                        $needs_counseling,
                        $needs_referral,
                        $reviewed_by,
                        $status_note,
                        $guidance_id
                    );

                    if ($update_stmt->execute()) {
                        $saved_count++;
                    }
                } else {
                    $insert_sql = "INSERT INTO intervention_guidance (
                                        child_id,
                                        record_id,
                                        submitted_report_id,
                                        original_status,
                                        intervention_category,
                                        guidance_text,
                                        optional_note,
                                        is_at_risk,
                                        needs_counseling,
                                        needs_referral,
                                        reviewed_by,
                                        is_reviewed,
                                        sent_to_guardian,
                                        sent_at,
                                        status_note
                                   ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1, NOW(), ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param(
                        "iiissssiiiis",
                        $child_id,
                        $record_id,
                        $submitted_report_id,
                        $original_status,
                        $selected_category,
                        $guidance_text,
                        $optional_note,
                        $is_at_risk,
                        $needs_counseling,
                        $needs_referral,
                        $reviewed_by,
                        $status_note
                    );

                    if ($insert_stmt->execute()) {
                        $saved_count++;
                    }
                }
            }

            if ($saved_count > 0) {
                $message = 'Batch intervention guidance saved and sent to guardian successfully.';
                $message_type = 'success';
            } else {
                $message = 'No intervention guidance records were saved.';
                $message_type = 'error';
            }
        } else {
            $message = 'No children found under the selected category from the latest submitted WMR.';
            $message_type = 'error';
        }
    }
}

$cdc_list = [];
$cdc_sql = "SELECT cdc_id, cdc_name FROM cdc WHERE status = 'Active' ORDER BY cdc_name ASC";
$cdc_result = $conn->query($cdc_sql);
if ($cdc_result && $cdc_result->num_rows > 0) {
    while ($row = $cdc_result->fetch_assoc()) {
        $cdc_list[] = $row;
    }
}

$latest_wmr_rows = getLatestSubmittedWMRRowsPerCDC($conn, $selected_cdc);

$processed_rows = [];
$count_mw = 0;
$count_sw = 0;
$count_ow = 0;
$count_ob = 0;

foreach ($latest_wmr_rows as $row) {
    $final_category = determineFinalInterventionCategory(
        isset($row['wfa_status']) ? $row['wfa_status'] : '',
        isset($row['wflh_status']) ? $row['wflh_status'] : ''
    );

    if ($final_category === null) {
        continue;
    }

    $row['intervention_category'] = $final_category;
    $processed_rows[] = $row;

    if ($final_category === 'Moderately Wasted') {
        $count_mw++;
    } elseif ($final_category === 'Severely Wasted') {
        $count_sw++;
    } elseif ($final_category === 'Overweight') {
        $count_ow++;
    } elseif ($final_category === 'Obese') {
        $count_ob++;
    }
}

$children = [];

if (!empty($selected_category)) {
    foreach ($processed_rows as $row) {
        if ($row['intervention_category'] !== $selected_category) {
            continue;
        }

        $children[] = [
            'submitted_report_id' => isset($row['submitted_report_id']) ? $row['submitted_report_id'] : null,
            'record_id' => isset($row['record_id']) ? $row['record_id'] : 0,
            'child_id' => isset($row['child_id']) ? $row['child_id'] : 0,
            'child_name' => isset($row['child_name']) ? $row['child_name'] : '',
            'cdc_id' => isset($row['cdc_id']) ? $row['cdc_id'] : 0,
            'cdc_name' => isset($row['cdc_name']) ? $row['cdc_name'] : '',
            'date_recorded' => isset($row['date_recorded']) ? $row['date_recorded'] : '',
            'age_months' => isset($row['age_in_months']) ? $row['age_in_months'] : '',
            'height' => isset($row['height']) ? $row['height'] : '',
            'weight' => isset($row['weight']) ? $row['weight'] : '',
            'muac' => isset($row['muac']) ? $row['muac'] : '',
            'hfa_status' => isset($row['hfa_status']) ? $row['hfa_status'] : '',
            'wfa_status' => isset($row['wfa_status']) ? $row['wfa_status'] : '',
            'wflh_status' => isset($row['wflh_status']) ? $row['wflh_status'] : '',
            'intervention_category' => $row['intervention_category']
        ];
    }
}

if ($show_preview && !empty($selected_category)) {
    $preview_children = $children;
    $preview_guidance_rules = getInterventionGuidanceRules($selected_category);
    $preview_guidance_text = buildGuidanceText($preview_guidance_rules);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intervention Guidance</title>
    <link rel="stylesheet" href="../assets/admin-style.css">
    <link rel="stylesheet" href="../assets/intervention_guidance.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="<?php echo (isset($_SESSION['theme_mode']) && $_SESSION['theme_mode'] === 'dark') ? 'dark-mode' : ''; ?>">

<?php include '../includes/admin_sidebar.php'; ?>
<?php include '../includes/admin_topbar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-header">
        <h1>Intervention Guidance</h1>
        <p>Review children grouped under the selected intervention category using the latest submitted WMR per CDC.</p>
    </div>

    <?php if (!empty($message)) : ?>
        <div class="alert <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="status-summary">
        <div class="status-card">Moderately Wasted <span><?php echo $count_mw; ?></span></div>
        <div class="status-card">Severely Wasted <span><?php echo $count_sw; ?></span></div>
        <div class="status-card">Overweight <span><?php echo $count_ow; ?></span></div>
        <div class="status-card">Obese <span><?php echo $count_ob; ?></span></div>
    </div>

    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="cdc_id">CDC</label>
                <select name="cdc_id" id="cdc_id">
                    <option value="">All CDCs</option>
                    <?php foreach ($cdc_list as $cdc) : ?>
                        <option value="<?php echo $cdc['cdc_id']; ?>" <?php echo ($selected_cdc == $cdc['cdc_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cdc['cdc_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="category">Intervention Category</label>
                <select name="category" id="category" required>
                    <option value="">Select Category</option>
                    <option value="Moderately Wasted" <?php echo ($selected_category === 'Moderately Wasted') ? 'selected' : ''; ?>>Moderately Wasted</option>
                    <option value="Severely Wasted" <?php echo ($selected_category === 'Severely Wasted') ? 'selected' : ''; ?>>Severely Wasted</option>
                    <option value="Overweight" <?php echo ($selected_category === 'Overweight') ? 'selected' : ''; ?>>Overweight</option>
                    <option value="Obese" <?php echo ($selected_category === 'Obese') ? 'selected' : ''; ?>>Obese</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <a href="intervention_guidance.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <?php if (empty($selected_category)) : ?>
        <div class="table-card empty-card">
            <div class="empty-state-block">
                Please select an intervention category first to view the children under that group.
            </div>
        </div>
    <?php else : ?>
        <div class="table-card">
            <div class="table-header">
                <h2><?php echo htmlspecialchars($selected_category); ?> Children</h2>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>CDC</th>
                            <th>Child Name</th>
                            <th>Age (Months)</th>
                            <th>Date Measured</th>
                            <th>Height</th>
                            <th>Weight</th>
                            <th>MUAC</th>
                            <th>HFA</th>
                            <th>WFA</th>
                            <th>WFL/H</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($children)) : ?>
                            <?php foreach ($children as $child) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($child['cdc_name']); ?></td>
                                    <td><?php echo htmlspecialchars($child['child_name']); ?></td>
                                    <td><?php echo htmlspecialchars($child['age_months']); ?></td>
                                    <td><?php echo htmlspecialchars(!empty($child['date_recorded']) ? date('M d, Y', strtotime($child['date_recorded'])) : ''); ?></td>
                                    <td><?php echo htmlspecialchars($child['height']); ?></td>
                                    <td><?php echo htmlspecialchars($child['weight']); ?></td>
                                    <td><?php echo htmlspecialchars($child['muac']); ?></td>
                                    <td><?php echo htmlspecialchars($child['hfa_status']); ?></td>
                                    <td><?php echo htmlspecialchars($child['wfa_status']); ?></td>
                                    <td><?php echo htmlspecialchars($child['wflh_status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="10" class="empty-state">No children found under the selected category from the latest submitted WMR.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($children)) : ?>
                <div class="batch-action">
                    <form method="POST">
                        <input type="hidden" name="cdc_id" value="<?php echo htmlspecialchars($selected_cdc); ?>">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                        <button type="submit" name="preview_batch" class="btn btn-generate-main">Generate Guidance</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($show_preview && !empty($preview_children)) : ?>
        <div class="preview-overlay">
            <div class="preview-modal">
                <div class="preview-header">
                    <h2>Generate Batch Intervention Guidance for <?php echo htmlspecialchars($selected_category); ?></h2>
                    <p>Review the children included in this batch and confirm the guidance below.</p>
                </div>

                <div class="preview-table-wrapper">
                    <table class="preview-table">
                        <thead>
                            <tr>
                                <th>Child Name</th>
                                <th>CDC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($preview_children as $child) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($child['child_name']); ?></td>
                                    <td><?php echo htmlspecialchars($child['cdc_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="guidance-section">
                    <h3>Auto-Generated Guidance</h3>
                    <ul>
                        <?php foreach ($preview_guidance_rules as $rule) : ?>
                            <li><?php echo htmlspecialchars($rule); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <form method="POST" class="preview-form">
                    <input type="hidden" name="cdc_id" value="<?php echo htmlspecialchars($selected_cdc); ?>">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">

                    <div class="note-group">
                        <label for="optional_note">Optional Note</label>
                        <textarea name="optional_note" id="optional_note" rows="4" placeholder="Add optional note..."><?php echo htmlspecialchars($optional_note); ?></textarea>
                    </div>

                    <div class="preview-actions">
                        <a href="intervention_guidance.php?cdc_id=<?php echo urlencode($selected_cdc); ?>&category=<?php echo urlencode($selected_category); ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="confirm_batch" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
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