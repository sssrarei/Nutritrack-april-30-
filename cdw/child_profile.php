<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 2){
    header("Location: ../login.php");
    exit();
}

if(!isset($_SESSION['active_cdc_id'])){
    die("Please select an active CDC first from the dashboard.");
}

if(!isset($_GET['child_id']) || empty($_GET['child_id'])){
    die("No child selected.");
}

$active_cdc_id = (int) $_SESSION['active_cdc_id'];
$child_id = (int) $_GET['child_id'];

$sql = "
    SELECT 
        children.*,
        cdc.cdc_name,
        cdc.address AS cdc_address,

        guardians.first_name AS guardian_first_name,
        guardians.middle_name AS guardian_middle_name,
        guardians.last_name AS guardian_last_name,
        guardians.relationship_to_child,
        guardians.contact_number AS guardian_contact_number,
        guardians.email AS guardian_email,
        guardians.address AS guardian_address,

        child_health_information.vaccination_card_file_path,
        child_health_information.allergies,
        child_health_information.comorbidities,
        child_health_information.medical_history_file_path

    FROM children
    INNER JOIN cdc ON children.cdc_id = cdc.cdc_id
    LEFT JOIN guardians ON children.child_id = guardians.child_id
    LEFT JOIN child_health_information ON children.child_id = child_health_information.child_id
    WHERE children.child_id = ?
    AND children.cdc_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $child_id, $active_cdc_id);
$stmt->execute();
$result = $stmt->get_result();

if(!$result){
    die("Query error: " . $conn->error);
}

if($result->num_rows == 0){
    die("Child not found or not assigned to the active CDC.");
}

$child = $result->fetch_assoc();

$child_full_name = trim(
    $child['first_name'] . ' ' .
    $child['middle_name'] . ' ' .
    $child['last_name']
);

$guardian_full_name = trim(
    ($child['guardian_first_name'] ?? '') . ' ' .
    ($child['guardian_last_name'] ?? '')
);

if(empty($guardian_full_name) && !empty($child['guardian_name'])){
    $guardian_full_name = $child['guardian_name'];
}

if(empty($guardian_full_name)){
    $guardian_full_name = "No guardian linked yet";
}

$age = "N/A";
$age_months = "N/A";

if(!empty($child['birthdate']) && $child['birthdate'] != '0000-00-00'){
    $birthdate = new DateTime($child['birthdate']);
    $today = new DateTime();
    $diff = $today->diff($birthdate);

    $age = $diff->y . " year(s) old";
    $age_months = ($diff->y * 12) + $diff->m;
}

$access_code = !empty($child['access_code']) ? $child['access_code'] : 'N/A';
$sex = !empty($child['sex']) ? $child['sex'] : 'N/A';
$birthdate_display = (!empty($child['birthdate']) && $child['birthdate'] != '0000-00-00')
    ? date("F d, Y", strtotime($child['birthdate']))
    : 'N/A';

$child_address = !empty($child['address']) ? $child['address'] : 'N/A';
$cdc_name = !empty($child['cdc_name']) ? $child['cdc_name'] : 'N/A';
$cdc_address = !empty($child['cdc_address']) ? $child['cdc_address'] : 'N/A';

$relationship_to_child = !empty($child['relationship_to_child']) ? $child['relationship_to_child'] : 'N/A';
$guardian_contact_number = !empty($child['guardian_contact_number']) ? $child['guardian_contact_number'] : (!empty($child['contact_number']) ? $child['contact_number'] : 'N/A');
$guardian_email = !empty($child['guardian_email']) ? $child['guardian_email'] : 'N/A';
$guardian_address = !empty($child['guardian_address']) ? $child['guardian_address'] : $child_address;

$vaccination_records = !empty($child['vaccination_card_file_path']) ? $child['vaccination_card_file_path'] : 'N/A';
$allergies = !empty($child['allergies']) ? $child['allergies'] : 'N/A';
$comorbidities = !empty($child['comorbidities']) ? $child['comorbidities'] : 'N/A';
$medical_history = !empty($child['medical_history_file_path']) ? $child['medical_history_file_path'] : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Profile | NutriTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/cdw-style.css">
    <link rel="stylesheet" href="../assets/child_profile.css">
</head>
<body>

<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<div class="main-content" id="mainContent">
    <a href="child_list.php" class="back-link">← Back to Pupil List</a>

    <div class="profile-header">
        <div class="profile-title-wrap">
            <span class="section-label">Child Profile</span>
            <h1 class="profile-title"><?php echo strtoupper(htmlspecialchars($child['first_name'] . " " . $child['last_name'])); ?></h1>
            <div class="profile-subtext">
                Active CDC: <?php echo htmlspecialchars($_SESSION['active_cdc_name']); ?>
            </div>
        </div>

        <div class="access-code-badge">
            Access Code: <?php echo htmlspecialchars($access_code); ?>
        </div>
    </div>

    <div class="content-grid">
        <div class="info-card">
            <h3 class="card-title">Child Information</h3>

            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">Child Name</span>
                    <div class="info-value"><?php echo htmlspecialchars($child_full_name); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Child Development Center</span>
                    <div class="info-value"><?php echo htmlspecialchars($cdc_name); ?> - <?php echo htmlspecialchars($cdc_address); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Sex</span>
                    <div class="info-value"><?php echo htmlspecialchars($sex); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Birthdate</span>
                    <div class="info-value"><?php echo htmlspecialchars($birthdate_display); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Age</span>
                    <div class="info-value"><?php echo htmlspecialchars($age); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Age in Months</span>
                    <div class="info-value">
                        <?php echo ($age_months === 'N/A') ? 'N/A' : htmlspecialchars($age_months) . ' month(s)'; ?>
                    </div>
                </div>

                <div class="info-row">
                    <span class="info-label">Address</span>
                    <div class="info-value"><?php echo htmlspecialchars($child_address); ?></div>
                </div>
            </div>

            <h4 class="sub-section-title">Child Health Information</h4>

            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">Vaccination Records</span>
                    <div class="info-value"><?php echo htmlspecialchars($vaccination_records); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Allergies</span>
                    <div class="info-value"><?php echo htmlspecialchars($allergies); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Comorbidities</span>
                    <div class="info-value"><?php echo htmlspecialchars($comorbidities); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Medical History</span>
                    <div class="info-value"><?php echo htmlspecialchars($medical_history); ?></div>
                </div>
            </div>

            <div class="card-actions">
                <a href="edit_child.php?child_id=<?php echo $child['child_id']; ?>" class="btn-edit">Edit Child Information</a>
            </div>
        </div>

        <div class="info-card">
            <h3 class="card-title">Guardian Information</h3>

            <div class="info-list">
                <div class="info-row">
                    <span class="info-label">Parent/Guardian Name</span>
                    <div class="info-value"><?php echo htmlspecialchars($guardian_full_name); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Relationship to the Child</span>
                    <div class="info-value"><?php echo htmlspecialchars($relationship_to_child); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Guardian Address</span>
                    <div class="info-value"><?php echo htmlspecialchars($guardian_address); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Contact Number</span>
                    <div class="info-value"><?php echo htmlspecialchars($guardian_contact_number); ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Email</span>
                    <div class="info-value"><?php echo htmlspecialchars($guardian_email); ?></div>
                </div>
            </div>

            <div class="card-actions">
                <a href="edit_guardian.php?child_id=<?php echo $child['child_id']; ?>" class="btn-edit">Edit Guardian Information</a>
            </div>
        </div>

        <div class="side-action-card">
            <div>
                <h3 class="monitoring-title">Nutritional Monitoring</h3>
                <p class="monitoring-text">
                    Open this child’s nutritional monitoring page to view nutritional status, feeding, milk feeding, deworming, and growth monitoring records.
                </p>
            </div>

            <a href="nutritional_monitoring.php?child_id=<?php echo $child['child_id']; ?>" class="btn-monitoring">
                Open Nutritional Monitoring
            </a>
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