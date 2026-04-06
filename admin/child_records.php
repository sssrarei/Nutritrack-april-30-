<?php
include '../includes/auth.php';
include '../config/database.php';

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

/* HELPERS */
function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function normalize_sex($sex)
{
    $sex = strtolower(trim((string)$sex));

    if ($sex === 'f' || $sex === 'female') {
        return 'Female';
    }

    if ($sex === 'm' || $sex === 'male') {
        return 'Male';
    }

    return trim((string)$sex) !== '' ? trim((string)$sex) : '-';
}

function calculate_age_years($birthdate)
{
    if (empty($birthdate) || $birthdate === '0000-00-00') {
        return '-';
    }

    try {
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        $diff = $today->diff($birth);
        return (int) $diff->y;
    } catch (Exception $e) {
        return '-';
    }
}

function buildQueryString($overrides = array())
{
    $params = $_GET;

    foreach ($overrides as $key => $value) {
        $params[$key] = $value;
    }

    return http_build_query($params);
}

/* FILTERS */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$cdc_filter = isset($_GET['cdc_id']) ? trim($_GET['cdc_id']) : '';
$sex_filter = isset($_GET['sex']) ? trim($_GET['sex']) : '';
$show_entries = isset($_GET['show']) ? (int)$_GET['show'] : 20;

$allowed_entries = array(10, 20, 50, 100);
if (!in_array($show_entries, $allowed_entries)) {
    $show_entries = 20;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $show_entries;

/* SUCCESS MESSAGE */
$saved_message = '';
if (isset($_GET['saved']) && $_GET['saved'] == '1') {
    $saved_message = 'Child profile saved successfully.';
}

/* CDC OPTIONS */
$cdc_options = array();
$cdc_option_query = "SELECT cdc_id, cdc_name FROM cdc ORDER BY cdc_name ASC";
$cdc_option_result = mysqli_query($conn, $cdc_option_query);

if ($cdc_option_result && mysqli_num_rows($cdc_option_result) > 0) {
    while ($cdc_row = mysqli_fetch_assoc($cdc_option_result)) {
        $cdc_options[] = $cdc_row;
    }
}

/* --------------------------------------------------------
   GET SAVED CHILD PROFILE SUBMISSIONS
   Source of truth for CSWD child profile list:
   submitted_reports.status = saved_to_child_profile
--------------------------------------------------------- */
$saved_reports = array();

$saved_reports_sql = "
    SELECT
        submitted_report_id,
        report_payload,
        submitted_at
    FROM submitted_reports
    WHERE report_type = 'individual_child'
      AND status = 'saved_to_child_profile'
    ORDER BY submitted_at DESC, submitted_report_id DESC
";

$saved_reports_result = mysqli_query($conn, $saved_reports_sql);

if ($saved_reports_result && mysqli_num_rows($saved_reports_result) > 0) {
    while ($row = mysqli_fetch_assoc($saved_reports_result)) {
        $payload = json_decode($row['report_payload'], true);

        if (!is_array($payload)) {
            continue;
        }

        $payload_child_id = isset($payload['child_id']) ? (int)$payload['child_id'] : 0;

        if ($payload_child_id <= 0) {
            continue;
        }

        /* keep only latest saved submission per child */
        if (!isset($saved_reports[$payload_child_id])) {
            $saved_reports[$payload_child_id] = array(
                'submitted_report_id' => (int)$row['submitted_report_id'],
                'submitted_at' => $row['submitted_at'],
                'child_id' => $payload_child_id
            );
        }
    }
}

/* --------------------------------------------------------
   FETCH LIVE CHILD DATA FOR SAVED CHILD IDS
--------------------------------------------------------- */
$children_all = array();

if (!empty($saved_reports)) {
    $child_ids = array_keys($saved_reports);
    $child_ids = array_map('intval', $child_ids);
    $in_clause = implode(',', $child_ids);

    $child_query = "
        SELECT
            ch.child_id,
            ch.first_name,
            ch.middle_name,
            ch.last_name,
            ch.sex,
            ch.birthdate,
            ch.cdc_id,
            c.cdc_name
        FROM children ch
        LEFT JOIN cdc c ON c.cdc_id = ch.cdc_id
        WHERE ch.child_id IN ($in_clause)
        ORDER BY ch.last_name ASC, ch.first_name ASC
    ";

    $child_result = mysqli_query($conn, $child_query);

    if ($child_result && mysqli_num_rows($child_result) > 0) {
        while ($row = mysqli_fetch_assoc($child_result)) {
            $full_name_parts = array();

            if (!empty($row['first_name'])) {
                $full_name_parts[] = trim($row['first_name']);
            }

            if (!empty($row['middle_name'])) {
                $full_name_parts[] = trim($row['middle_name']);
            }

            if (!empty($row['last_name'])) {
                $full_name_parts[] = trim($row['last_name']);
            }

            $full_name = trim(implode(' ', $full_name_parts));
            if ($full_name === '') {
                $full_name = 'N/A';
            }

            $children_all[] = array(
                'child_id' => (int)$row['child_id'],
                'child_name' => $full_name,
                'sex' => normalize_sex($row['sex']),
                'sex_raw' => strtolower(trim((string)$row['sex'])),
                'age' => calculate_age_years($row['birthdate']),
                'cdc_id' => (int)$row['cdc_id'],
                'cdc_name' => !empty($row['cdc_name']) ? $row['cdc_name'] : '-',
                'submitted_report_id' => $saved_reports[(int)$row['child_id']]['submitted_report_id'],
                'saved_at' => $saved_reports[(int)$row['child_id']]['submitted_at']
            );
        }
    }
}

/* --------------------------------------------------------
   APPLY FILTERS IN PHP
--------------------------------------------------------- */
$filtered_children = array();

foreach ($children_all as $child) {
    $match = true;

    if ($search !== '') {
        $needle = strtolower($search);
        $haystack = strtolower($child['child_name']);

        if (strpos($haystack, $needle) === false) {
            $match = false;
        }
    }

    if ($match && $cdc_filter !== '') {
        if ((int)$child['cdc_id'] !== (int)$cdc_filter) {
            $match = false;
        }
    }

    if ($match && $sex_filter !== '') {
        if (strtolower(trim($child['sex'])) !== strtolower(trim($sex_filter))) {
            $match = false;
        }
    }

    if ($match) {
        $filtered_children[] = $child;
    }
}

/* SORT BY CHILD NAME ASC */
usort($filtered_children, function ($a, $b) {
    return strcasecmp($a['child_name'], $b['child_name']);
});

/* PAGINATION */
$total_records = count($filtered_children);
$total_pages = ($total_records > 0) ? (int)ceil($total_records / $show_entries) : 1;

if ($page > $total_pages) {
    $page = $total_pages;
}

$offset = ($page - 1) * $show_entries;
$children = array_slice($filtered_children, $offset, $show_entries);

$start_entry = 0;
$end_entry = 0;

if ($total_records > 0) {
    $start_entry = $offset + 1;
    $end_entry = min($offset + $show_entries, $total_records);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Records | NutriTrack</title>
    <link rel="stylesheet" href="../assets/admin-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/admin_topbar.php'; ?>
<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="dashboard-wrapper">

        <div class="records-page-shell">
            <div class="records-page-header">
                <h1>CHILD RECORDS</h1>
            </div>

            <?php if ($saved_message !== '') { ?>
                <div style="margin-bottom:16px; padding:12px 16px; border-radius:12px; background:#e8f5e9; color:#2e7d32; font-weight:600;">
                    <?php echo h($saved_message); ?>
                </div>
            <?php } ?>

            <form method="GET" action="child_records.php" class="records-toolbar-card">
                <div class="records-toolbar-left">
                    <div class="records-search-wrap">
                        <span class="records-search-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"></circle>
                                <path d="M20 20L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="search"
                            class="records-search-input"
                            placeholder="Search Child...."
                            value="<?php echo h($search); ?>"
                        >
                    </div>
                </div>

                <div class="records-toolbar-right">
                    <select name="cdc_id" class="records-filter-select" onchange="this.form.submit()">
                        <option value="">Filter by CDC</option>
                        <?php foreach ($cdc_options as $cdc_item) { ?>
                            <option value="<?php echo (int)$cdc_item['cdc_id']; ?>" <?php echo ($cdc_filter == $cdc_item['cdc_id']) ? 'selected' : ''; ?>>
                                <?php echo h($cdc_item['cdc_name']); ?>
                            </option>
                        <?php } ?>
                    </select>

                    <select name="sex" class="records-filter-select" onchange="this.form.submit()">
                        <option value="">Filter by Sex</option>
                        <option value="Male" <?php echo (strtolower(trim($sex_filter)) === 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (strtolower(trim($sex_filter)) === 'female') ? 'selected' : ''; ?>>Female</option>
                    </select>

                    <div class="records-show-wrap">
                        <label for="show">Show:</label>
                        <select name="show" id="show" class="records-show-select" onchange="this.form.submit()">
                            <option value="10" <?php echo ($show_entries == 10) ? 'selected' : ''; ?>>10 entries</option>
                            <option value="20" <?php echo ($show_entries == 20) ? 'selected' : ''; ?>>20 entries</option>
                            <option value="50" <?php echo ($show_entries == 50) ? 'selected' : ''; ?>>50 entries</option>
                            <option value="100" <?php echo ($show_entries == 100) ? 'selected' : ''; ?>>100 entries</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="records-table-card">
                <div class="records-table-wrap">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>Child Name</th>
                                <th>Sex</th>
                                <th>Age</th>
                                <th>CDC</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($children)) { ?>
                                <?php foreach ($children as $child) { ?>
                                    <tr>
                                        <td><?php echo h($child['child_name']); ?></td>
                                        <td><?php echo h($child['sex']); ?></td>
                                        <td><?php echo h($child['age']); ?></td>
                                        <td><?php echo h($child['cdc_name']); ?></td>
                                        <td>
                                            <a href="view_child.php?child_id=<?php echo (int)$child['child_id']; ?>" class="records-view-btn">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="5" class="records-empty">No child records found.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="records-footer">
                    <div class="records-footer-left">
                        Showing <?php echo (int)$start_entry; ?> to <?php echo (int)$end_entry; ?> of <?php echo (int)$total_records; ?> entries
                    </div>

                    <div class="records-footer-right">
                        <?php if ($page > 1) { ?>
                            <a href="?<?php echo buildQueryString(array('page' => $page - 1)); ?>" class="records-page-btn">Prev</a>
                        <?php } ?>

                        <?php if ($page < $total_pages) { ?>
                            <a href="?<?php echo buildQueryString(array('page' => $page + 1)); ?>" class="records-page-btn">Next</a>
                        <?php } ?>
                    </div>
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