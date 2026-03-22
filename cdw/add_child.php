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

$cdc_id = $_SESSION['active_cdc_id'];
$success = "";
$error = "";

if(isset($_POST['save'])){
    // CHILD INFORMATION
    $child_name = trim($_POST['child_name']);
    $birthdate = $_POST['birthdate'];
    $sex = isset($_POST['sex']) ? trim($_POST['sex']) : '';
    $address = trim($_POST['address']);
    $religion = trim($_POST['religion']);

    // HEALTH INFORMATION
    $allergies = trim($_POST['allergies']);
    $comorbidities = trim($_POST['comorbidities']);

    // GUARDIAN INFORMATION (OPTIONAL)
    $guardian_name = trim($_POST['guardian_name']);
    $relationship_to_child = trim($_POST['relationship_to_child']);
    $contact_number = trim($_POST['contact_number']);
    $guardian_email = trim($_POST['guardian_email']);

    if(empty($child_name) || empty($birthdate) || empty($sex)){
        $error = "Please fill in all required child information fields.";
    } else {
        // Split child full name
        $child_parts = preg_split('/\s+/', $child_name);

        $first_name = "";
        $middle_name = "";
        $last_name = "";

        if(count($child_parts) == 1){
            $first_name = $child_parts[0];
        } elseif(count($child_parts) == 2){
            $first_name = $child_parts[0];
            $last_name = $child_parts[1];
        } else {
            $first_name = array_shift($child_parts);
            $last_name = array_pop($child_parts);
            $middle_name = implode(" ", $child_parts);
        }

        // Generate unique access code
        $access_code = "CH-" . rand(1000, 9999);
        $check_code = $conn->query("SELECT child_id FROM children WHERE access_code = '$access_code'");

        while($check_code && $check_code->num_rows > 0){
            $access_code = "CH-" . rand(1000, 9999);
            $check_code = $conn->query("SELECT child_id FROM children WHERE access_code = '$access_code'");
        }

        // FILE UPLOADS
        $vaccination_card_path = "";
        $medical_history_path = "";

        if(isset($_FILES['vaccination_card']) && $_FILES['vaccination_card']['error'] == 0){
            $vacc_name = time() . "_vacc_" . basename($_FILES['vaccination_card']['name']);
            $vacc_target = "../uploads/vaccination_cards/" . $vacc_name;

            if(move_uploaded_file($_FILES['vaccination_card']['tmp_name'], $vacc_target)){
                $vaccination_card_path = "uploads/vaccination_cards/" . $vacc_name;
            }
        }

        if(isset($_FILES['medical_history_file']) && $_FILES['medical_history_file']['error'] == 0){
            $med_name = time() . "_med_" . basename($_FILES['medical_history_file']['name']);
            $med_target = "../uploads/medical_history/" . $med_name;

            if(move_uploaded_file($_FILES['medical_history_file']['tmp_name'], $med_target)){
                $medical_history_path = "uploads/medical_history/" . $med_name;
            }
        }

        // SAVE CHILD
        $child_sql = "INSERT INTO children
            (first_name, middle_name, last_name, birthdate, sex, address, religion, guardian_name, contact_number, cdc_id, access_code)
            VALUES
            ('$first_name', '$middle_name', '$last_name', '$birthdate', '$sex', '$address', '$religion', '$guardian_name', '$contact_number', '$cdc_id', '$access_code')";

        if($conn->query($child_sql)){
            $child_id = $conn->insert_id;

            // SAVE GUARDIAN ONLY IF MAY INPUT
            if(!empty($guardian_name)){
                $guardian_parts = preg_split('/\s+/', $guardian_name);

                $guardian_first_name = "";
                $guardian_middle_name = "";
                $guardian_last_name = "";

                if(count($guardian_parts) == 1){
                    $guardian_first_name = $guardian_parts[0];
                } elseif(count($guardian_parts) == 2){
                    $guardian_first_name = $guardian_parts[0];
                    $guardian_last_name = $guardian_parts[1];
                } else {
                    $guardian_first_name = array_shift($guardian_parts);
                    $guardian_last_name = array_pop($guardian_parts);
                    $guardian_middle_name = implode(" ", $guardian_parts);
                }

                $guardian_sql = "INSERT INTO guardians
                    (child_id, first_name, middle_name, last_name, relationship_to_child, contact_number, email, address)
                    VALUES
                    ('$child_id', '$guardian_first_name', '$guardian_middle_name', '$guardian_last_name', '$relationship_to_child', '$contact_number', '$guardian_email', '$address')";

                $guardian_saved = $conn->query($guardian_sql);

                if(!$guardian_saved){
                    $error = "Child saved, but guardian information failed: " . $conn->error;
                }
            }

            // SAVE HEALTH INFORMATION
            if(empty($error)){
                $health_sql = "INSERT INTO child_health_information
                    (child_id, vaccination_card_file_path, allergies, comorbidities, medical_history_file_path)
                    VALUES
                    ('$child_id', '$vaccination_card_path', '$allergies', '$comorbidities', '$medical_history_path')";

                $health_saved = $conn->query($health_sql);

                if(!$health_saved){
                    $error = "Child saved, but health information failed: " . $conn->error;
                } else {
                    $success = "Child Profile Registration successful! Child Access Code: " . $access_code;
                }
            }
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Child Profile Registration</title>
</head>
<body>

<p><a href="child_list.php">Back to Pupil List</a></p>

<h2>Child Profile Registration</h2>
<p><strong>Active CDC:</strong> <?php echo $_SESSION['active_cdc_name']; ?></p>

<?php if(!empty($success)){ ?>
    <p style="color:green;"><strong><?php echo $success; ?></strong></p>
<?php } ?>

<?php if(!empty($error)){ ?>
    <p style="color:red;"><strong><?php echo $error; ?></strong></p>
<?php } ?>

<form method="POST" enctype="multipart/form-data">

    <h3>Child Information</h3>

    <label>Child Name:</label>
    <input type="text" name="child_name" placeholder="First name Middle name Last name" required>
    <br><br>

    <label>Birthdate:</label>
    <input type="date" name="birthdate" required>
    <br><br>

    <label>Sex:</label>
    <input type="radio" name="sex" value="Male" required> Male
    <input type="radio" name="sex" value="Female" required> Female
    <br><br>

    <label>Address:</label>
    <input type="text" name="address">
    <br><br>

    <label>Religion:</label>
    <input type="text" name="religion">
    <br><br>

    <hr>

    <h3>Health Information <span style="color:gray;">(Optional)</span></h3>

    <label>Vaccination Card:</label>
    <input type="file" name="vaccination_card">
    <br><br>

    <label>Allergies:</label>
    <input type="text" name="allergies">
    <br><br>

    <label>Comorbidities:</label>
    <input type="text" name="comorbidities">
    <br><br>

    <label>Medical History:</label>
    <input type="file" name="medical_history_file">
    <br><br>

    <hr>

    <h3>Guardian Information <span style="color:gray;">(Optional)</span></h3>

    <label>Parent/Guardian Name:</label>
    <input type="text" name="guardian_name">
    <br><br>

    <label>Relationship to Child:</label>
    <select name="relationship_to_child">
        <option value="">Select Relationship</option>
        <option value="Mother">Mother</option>
        <option value="Father">Father</option>
        <option value="Grandmother">Grandmother</option>
        <option value="Grandfather">Grandfather</option>
        <option value="Guardian">Guardian</option>
        <option value="Aunt">Aunt</option>
        <option value="Uncle">Uncle</option>
        <option value="Sibling">Sibling</option>
        <option value="Other">Other</option>
    </select>
    <br><br>

    <label>Contact Number:</label>
    <input type="text" name="contact_number">
    <br><br>

    <label>Email:</label>
    <input type="email" name="guardian_email">
    <br><br>

    <button type="button" onclick="window.location.href='child_list.php'">Cancel</button>
    <button type="submit" name="save">Save Pupil</button>
</form>

</body>
</html>