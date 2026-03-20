<?php
include '../includes/auth.php';

if($_SESSION['role_id'] != 1){
    header("Location: ../login.php");
    exit();
}
?>

<h1>Admin Dashboard</h1>
<p>Welcome, <?php echo $_SESSION['first_name']; ?>!</p>

<ul>
    <li><a href="add_user.php">Add CDW / Guardian Account</a></li>
    <li><a href="user_list.php">View Users</a></li>
</ul>

<a href="../logout.php">Logout</a>