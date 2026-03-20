<?php
session_start();
include 'config/database.php';

if(isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if($result && $result->num_rows > 0){
        $user = $result->fetch_assoc();

        if($password == $user['password']){
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];

            if($user['role_id'] == 1){
                header("Location: admin/dashboard.php");
                exit();
            } elseif($user['role_id'] == 2){
                header("Location: cdw/dashboard.php");
                exit();
            } else {
                header("Location: guardian/dashboard.php");
                exit();
            }
        } else {
            $error = "Wrong password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<h2>Login</h2>

<?php
if(isset($error)){
    echo "<p style='color:red;'>$error</p>";
}
?>

<form method="POST">
    Email: <input type="email" name="email" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    <button type="submit" name="login">Login</button>
</form>