<?php
include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 1){
    header("Location: ../login.php");
    exit();
}

$message = "";
$error = "";

// Kunin lahat ng CDC para sa checkbox list
$cdc_result = $conn->query("SELECT * FROM cdc ORDER BY cdc_name ASC");

if(isset($_POST['save_user'])){
    $role_id = $_POST['role_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $selected_cdcs = isset($_POST['cdc_ids']) ? $_POST['cdc_ids'] : [];

    if(empty($role_id) || empty($first_name) || empty($last_name) || empty($email) || empty($password)){
        $error = "Please fill in all required fields.";
    } else {
        // Check if email already exists
        $check_email = $conn->query("SELECT * FROM users WHERE email = '$email'");

        if($check_email && $check_email->num_rows > 0){
            $error = "Email already exists.";
        } else {
            // Kapag CDW, required na may at least 1 assigned CDC
            if($role_id == 2 && count($selected_cdcs) == 0){
                $error = "Please assign at least one CDC for this CDW.";
            } else {
                // Save user first
                $sql = "INSERT INTO users (role_id, first_name, last_name, email, password)
                        VALUES ('$role_id', '$first_name', '$last_name', '$email', '$password')";

                if($conn->query($sql)){
                    $new_user_id = $conn->insert_id;

                    // Kapag CDW, save CDC assignments
                    if($role_id == 2){
                        foreach($selected_cdcs as $cdc_id){
                            $cdc_id = (int)$cdc_id;

                            $assign_sql = "INSERT INTO cdw_assignments (user_id, cdc_id)
                                           VALUES ('$new_user_id', '$cdc_id')";
                            $conn->query($assign_sql);
                        }
                    }

                    $message = "User added successfully!";

                    // Clear form values after save
                    $role_id = "";
                    $first_name = "";
                    $last_name = "";
                    $email = "";
                    $password = "";
                    $selected_cdcs = [];
                } else {
                    $error = "Error: " . $conn->error;
                }
            }
        }
    }
}
?>

<h1>Add User</h1>

<?php if(!empty($message)){ ?>
    <p style="color:green;"><?php echo $message; ?></p>
<?php } ?>

<?php if(!empty($error)){ ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php } ?>

<form method="POST">
    <label>Role:</label>
    <select name="role_id" id="role_id" required onchange="toggleCdcSection()">
        <option value="">Select Role</option>
        <option value="2" <?php if(isset($role_id) && $role_id == 2) echo "selected"; ?>>CDW</option>
        <option value="3" <?php if(isset($role_id) && $role_id == 3) echo "selected"; ?>>Guardian</option>
    </select>

    <br><br>

    <label>First Name:</label>
    <input type="text" name="first_name" value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>" required>

    <br><br>

    <label>Last Name:</label>
    <input type="text" name="last_name" value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>" required>

    <br><br>

    <label>Email:</label>
    <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>

    <br><br>

    <label>Password:</label>
    <input type="text" name="password" value="<?php echo isset($password) ? htmlspecialchars($password) : ''; ?>" required>

    <br><br>

    <div id="cdc_section" style="display:none; border:1px solid #ccc; padding:10px; width:350px;">
        <strong>Assign CDC(s) to CDW:</strong><br><br>

        <?php
        if($cdc_result && $cdc_result->num_rows > 0){
            while($cdc = $cdc_result->fetch_assoc()){
                $checked = "";
                if(isset($selected_cdcs) && in_array($cdc['cdc_id'], $selected_cdcs)){
                    $checked = "checked";
                }
                ?>
                <label>
                    <input type="checkbox" name="cdc_ids[]" value="<?php echo $cdc['cdc_id']; ?>" <?php echo $checked; ?>>
                    <?php echo $cdc['cdc_name']; ?><?php if(!empty($cdc['barangay'])) echo " - " . $cdc['barangay']; ?>
                </label>
                <br>
                <?php
            }
        } else {
            echo "<p style='color:red;'>No CDC records found. Please add CDC first.</p>";
        }
        ?>
    </div>

    <br>

    <button type="submit" name="save_user">Save User</button>
</form>

<br><br>
<a href="dashboard.php">Back to Dashboard</a>

<script>
function toggleCdcSection() {
    var role = document.getElementById("role_id").value;
    var cdcSection = document.getElementById("cdc_section");

    if(role == "2"){
        cdcSection.style.display = "block";
    } else {
        cdcSection.style.display = "none";
    }
}

window.onload = function() {
    toggleCdcSection();
};
</script>