<?php
include '../includes/auth.php';

if($_SESSION['role_id'] != 3){
    header("Location: ../login.php");
    exit();
}
?>

<h1>Guardian Dashboard</h1>
<p>Welcome, <?php echo $_SESSION['first_name']; ?>!</p>

<h3>Child Information</h3>
<p>Child information will appear here.</p>

<h3>Nutritional Status</h3>
<p>Nutritional status details will appear here.</p>

<h3>Notifications</h3>
<p>Notifications will appear here.</p>

<a href="../logout.php">Logout</a>