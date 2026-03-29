<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config/database.php';

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if($email == "" || $password == ""){
        $error = "Please enter your email and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result && $result->num_rows > 0){
            $user = $result->fetch_assoc();

            // TEMPORARY: plain password check muna
            // para hindi masira ang current system mo
            if($password == $user['password']){

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];

                // Update last active
                $update_active = $conn->prepare("UPDATE users SET last_active = NOW() WHERE user_id = ?");
                $update_active->bind_param("i", $user['user_id']);
                $update_active->execute();

                if($user['role_id'] == 1){
                    header("Location: admin/dashboard.php");
                    exit();
                } elseif($user['role_id'] == 2){
                    header("Location: cdw/dashboard.php");
                    exit();
                } elseif($user['role_id'] == 3){
                    header("Location: guardian/dashboard.php");
                    exit();
                } else {
                    $error = "Invalid role.";
                }

            } else {
                $error = "Wrong password.";
            }
        } else {
            $error = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - NutriTrack</title>
    <style>
        body{
            margin:0;
            font-family: Arial, sans-serif;
            background:#ececf1;
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
        }

        .login-box{
            width:400px;
            background:#fff;
            border:1px solid #d0d0d0;
            padding:30px;
            box-sizing:border-box;
        }

        .login-title{
            margin:0 0 20px 0;
            font-size:30px;
            font-weight:bold;
            text-align:center;
            color:#333;
        }

        .error-message{
            background:#fdeaea;
            color:#b30000;
            border:1px solid #efb0b0;
            padding:10px 12px;
            margin-bottom:16px;
            font-size:14px;
            font-weight:bold;
        }

        .form-group{
            margin-bottom:16px;
        }

        label{
            display:block;
            margin-bottom:8px;
            font-weight:bold;
            color:#333;
        }

        input[type="email"],
        input[type="password"]{
            width:100%;
            padding:12px;
            border:1px solid #a8a8a8;
            font-size:15px;
            box-sizing:border-box;
        }

        .login-btn{
            width:100%;
            background:#3498db;
            color:#fff;
            border:none;
            padding:12px;
            font-size:16px;
            font-weight:bold;
            cursor:pointer;
            margin-top:6px;
        }

        .register-link{
            margin-top:18px;
            text-align:center;
            font-size:14px;
        }

        .register-link a{
            color:#3498db;
            text-decoration:none;
            font-weight:bold;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <h2 class="login-title">Login</h2>

        <?php if(isset($error)){ ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" name="login" class="login-btn">Login</button>
        </form>

        <div class="register-link">
            <a href="guardian/register.php">Register as Guardian</a>
        </div>
    </div>

</body>
</html>