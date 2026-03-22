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

// Kunin ang child + CDC + optional guardian + optional health info
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
    WHERE children.child_id = '$child_id'
    AND children.cdc_id = '$active_cdc_id'
    LIMIT 1
";

$result = $conn->query($sql);

if(!$result){
    die("Query error: " . $conn->error);
}

if($result->num_rows == 0){
    die("Child not found or not assigned to the active CDC.");
}

$child = $result->fetch_assoc();

// Child full name
$child_full_name = trim(
    $child['first_name'] . ' ' .
    $child['middle_name'] . ' ' .
    $child['last_name']
);

// Guardian full name (FIRST + LAST only)
$guardian_full_name = trim(
    ($child['guardian_first_name'] ?? '') . ' ' .
    ($child['guardian_last_name'] ?? '')
);

// Fallback sa lumang children.guardian_name kung wala pa sa guardians table
if(empty($guardian_full_name) && !empty($child['guardian_name'])){
    $guardian_full_name = $child['guardian_name'];
}

// Final fallback
if(empty($guardian_full_name)){
    $guardian_full_name = "No guardian linked yet";
}

// Age compute
$age = "N/A";
if(!empty($child['birthdate']) && $child['birthdate'] != '0000-00-00'){
    $birthdate = new DateTime($child['birthdate']);
    $today = new DateTime();
    $diff = $today->diff($birthdate);
    $age = $diff->y;
}

// Safe fallbacks
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
<html>
<head>
    <title>Child Profile</title>
</head>
<body>

<p><a href="child_list.php">Back to Pupil List</a></p>

<h2>
    CHILD PROFILE :
    <strong><?php echo strtoupper(htmlspecialchars($child['first_name'] . " " . $child['last_name'])); ?></strong>
    <span style="background: #f4c430; padding: 4px 12px; margin-left: 10px;">
        <?php echo htmlspecialchars($access_code); ?>
    </span>
</h2>

<hr>

<table width="100%" border="0" cellpadding="10">
    <tr>
        <!-- LEFT SIDE -->
        <td width="45%" valign="top">
            <p><strong>Childname:</strong> <?php echo htmlspecialchars($child_full_name); ?></p>
            <p><strong>Child Development Center:</strong> <?php echo htmlspecialchars($cdc_name); ?> - <?php echo htmlspecialchars($cdc_address); ?></p>
            <p><strong>Sex:</strong> <?php echo htmlspecialchars($sex); ?></p>
            <p><strong>Birthdate:</strong> <?php echo htmlspecialchars($birthdate_display); ?></p>
            <p><strong>Age:</strong> <?php echo htmlspecialchars($age); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($child_address); ?></p>

            <p>
                <a href="edit_child.php?child_id=<?php echo $child['child_id']; ?>">
                    <button type="button">Edit</button>
                </a>
            </p>

            <br>

            <h3>Guardian Information</h3>
            <p><strong>Parent/Guardian Name:</strong> <?php echo htmlspecialchars($guardian_full_name); ?></p>
            <p><strong>Relationship to the Child:</strong> <?php echo htmlspecialchars($relationship_to_child); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($guardian_address); ?></p>
            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($guardian_contact_number); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($guardian_email); ?></p>

            <p>
                <a href="edit_child.php?child_id=<?php echo $child['child_id']; ?>">
                    <button type="button">Edit</button>
                </a>
            </p>
        </td>

        <!-- MIDDLE -->
        <td width="25%" valign="top">
            <p><strong>Vaccination Records:</strong> <?php echo htmlspecialchars($vaccination_records); ?></p>
            <p><strong>Allergies:</strong> <?php echo htmlspecialchars($allergies); ?></p>
            <p><strong>Comorbidities:</strong> <?php echo htmlspecialchars($comorbidities); ?></p>
            <p><strong>Medical History:</strong> <?php echo htmlspecialchars($medical_history); ?></p>
        </td>

        <!-- RIGHT -->
        <td width="30%" valign="top" align="center">
            <br><br><br>
            <a href="nutritional_monitoring.php?child_id=<?php echo $child['child_id']; ?>">
                <button type="button" style="padding: 15px 40px;">NUTRITIONAL MONITORING</button>
            </a>
        </td>
    </tr>
</table>

</body>
</html>