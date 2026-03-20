<?php
include '../config/database.php';

$sql = "SELECT * FROM children";
$result = $conn->query($sql);
?>

<h2>Child List</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Birthdate</th>
        <th>Sex</th>
        <th>Guardian</th>
        <th>Contact</th>
        <th>Action</th>
    </tr>

<?php
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<tr>";
        echo "<td>".$row['child_id']."</td>";
        echo "<td>".$row['first_name']." ".$row['last_name']."</td>";
        echo "<td>".$row['birthdate']."</td>";
        echo "<td>".$row['sex']."</td>";
        echo "<td>".$row['guardian_name']."</td>";
        echo "<td>".$row['contact_number']."</td>";
        echo "<td>
                <a href='edit_child.php?id=".$row['child_id']."'>Edit</a>
                <a href='delete_child.php?id=".$row['child_id']."' onclick=\"return confirm('Are you sure?')\">Delete</a>
                </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No records found</td></tr>";
}
?>
</table>