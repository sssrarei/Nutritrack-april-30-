<?php
include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 1){
    header("Location: ../login.php");
    exit();
}

if(isset($_POST['save'])){
    $role_id = $_POST['role_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $check = "SELECT * FROM users WHERE email='$email'";
    $check_result = $conn->query($check);

    if($check_result->num_rows > 0){
        $message = "Email already exists.";
    } else {
        $sql = "INSERT INTO users (role_id, first_name, last_name, email, password)
                VALUES ('$role_id', '$first_name', '$last_name', '$email', '$password')";

        if($conn->query($sql)){
            $message = "User added successfully.";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<h2>Add User</h2>

<?php
if(isset($message)){
    echo "<p>$message</p>";
}
?>

<form method="POST">
    Role:
    <select name="role_id" required>
        <option value="">Select Role</option>
        <option value="2">CDW</option>
        <option value="3">Guardian</option>
    </select><br><br>

    First Name: <input type="text" name="first_name" required><br><br>
    Last Name: <input type="text" name="last_name" required><br><br>
    Email: <input type="email" name="email" required><br><br>
    Password: <input type="text" name="password" required><br><br>

    <button type="submit" name="save">Save User</button>
</form>

<br>
<a href="dashboard.php">Back to Dashboard</a>