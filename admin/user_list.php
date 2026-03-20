<?php
include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 1){
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT users.*, roles.role_name 
        FROM users
        JOIN roles ON users.role_id = roles.role_id
        ORDER BY users.user_id DESC";

$result = $conn->query($sql);
?>

<h2>User List</h2>
<a href="add_user.php">Add User</a> |
<a href="dashboard.php">Back to Dashboard</a>
<br><br>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
    </tr>

    <?php
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            echo "<tr>";
            echo "<td>".$row['user_id']."</td>";
            echo "<td>".$row['first_name']." ".$row['last_name']."</td>";
            echo "<td>".$row['email']."</td>";
            echo "<td>".$row['role_name']."</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No users found</td></tr>";
    }
    ?>
</table>