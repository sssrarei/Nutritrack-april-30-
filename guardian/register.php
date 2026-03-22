<?php
include '../config/database.php';

$message = "";
$error = "";

if(isset($_POST['register'])){
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $relationship_to_child = trim($_POST['relationship_to_child']);
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $access_code = trim($_POST['access_code']);

    if(
        empty($first_name) || empty($last_name) || empty($relationship_to_child) ||
        empty($address) || empty($contact_number) || empty($email) ||
        empty($password) || empty($confirm_password) || empty($access_code)
    ){
        $error = "Please fill in all required fields.";
    } elseif($password != $confirm_password){
        $error = "Password and Confirm Password do not match.";
    } else {
        // Check email
        $check_email = $conn->query("SELECT * FROM users WHERE email = '$email'");
        if($check_email && $check_email->num_rows > 0){
            $error = "Email already exists.";
        } else {
            // Check access code
            $child_query = $conn->query("SELECT * FROM children WHERE access_code = '$access_code'");

            if(!$child_query || $child_query->num_rows == 0){
                $error = "Invalid child access code.";
            } else {
                $child = $child_query->fetch_assoc();
                $child_id = $child['child_id'];

                // Check if child already linked
                $check_link = $conn->query("SELECT * FROM parent_child_links WHERE child_id = '$child_id'");

                if($check_link && $check_link->num_rows > 0){
                    $error = "This child is already linked to a guardian.";
                } else {
                    // Create guardian user account
                    $user_sql = "INSERT INTO users (role_id, first_name, last_name, email, password)
                                 VALUES (3, '$first_name', '$last_name', '$email', '$password')";

                    if($conn->query($user_sql)){
                        $parent_id = $conn->insert_id;

                        // Link guardian to child
                        $link_sql = "INSERT INTO parent_child_links (parent_id, child_id)
                                     VALUES ('$parent_id', '$child_id')";

                        if($conn->query($link_sql)){
                            $message = "Guardian registration successful! You can now login.";
                        } else {
                            $error = "Guardian account created, but linking failed: " . $conn->error;
                        }
                    } else {
                        $error = "Error: " . $conn->error;
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Guardian Registration</title>
</head>
<body>

<h2>Guardian Registration</h2>

<?php if(!empty($error)){ ?>
    <p style="color:red;"><strong><?php echo $error; ?></strong></p>
<?php } ?>

<?php if(!empty($message)){ ?>
    <p style="color:green;"><strong><?php echo $message; ?></strong></p>
<?php } ?>

<form method="POST">
    <label>First Name:</label>
    <input type="text" name="first_name" required>
    <br><br>

    <label>Last Name:</label>
    <input type="text" name="last_name" required>
    <br><br>

    <label>Relationship to the Child:</label>
    <select name="relationship_to_child" required>
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

    <label>Address:</label>
    <input type="text" name="address" required>
    <br><br>

    <label>Contact Number:</label>
    <input type="text" name="contact_number" required>
    <br><br>

    <label>Email:</label>
    <input type="email" name="email" required>
    <br><br>

    <label>Password:</label>
    <input type="password" name="password" required>
    <br><br>

    <label>Confirm Password:</label>
    <input type="password" name="confirm_password" required>
    <br><br>

    <label>Child Access Code:</label>
    <input type="text" name="access_code" required>
    <br><br>

    <button type="submit" name="register">Register</button>
</form>

<br>
<p><a href="../login.php">Back to Login</a></p>

</body>
</html>