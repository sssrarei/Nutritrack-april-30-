<?php
include '../includes/auth.php';

if($_SESSION['role_id'] != 3){
    header("Location: ../login.php");
    exit();
}
?>

<h1>Guardian Dashboard</h1>
<p>Welcome, <?php echo $_SESSION['first_name']; ?>!</p>
<a href="../logout.php">Logout</a>