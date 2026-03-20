<?php
include '../config/database.php';

$id = $_GET['id'];

if(isset($_POST['update'])){
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $birthdate = $_POST['birthdate'];
    $sex = $_POST['sex'];
    $address = $_POST['address'];
    $guardian_name = $_POST['guardian_name'];
    $contact_number = $_POST['contact_number'];

    $sql = "UPDATE children SET
        first_name='$first_name',
        middle_name='$middle_name',
        last_name='$last_name',
        birthdate='$birthdate',
        sex='$sex',
        address='$address',
        guardian_name='$guardian_name',
        contact_number='$contact_number'
        WHERE child_id=$id";

    if($conn->query($sql)){
        echo "Child updated successfully! <a href='child_list.php'>Back to List</a>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

$sql = "SELECT * FROM children WHERE child_id=$id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>

<h2>Edit Child</h2>
<form method="POST">
    First Name: <input type="text" name="first_name" value="<?php echo $row['first_name']; ?>" required><br><br>
    Middle Name: <input type="text" name="middle_name" value="<?php echo $row['middle_name']; ?>"><br><br>
    Last Name: <input type="text" name="last_name" value="<?php echo $row['last_name']; ?>" required><br><br>
    Birthdate: <input type="date" name="birthdate" value="<?php echo $row['birthdate']; ?>" required><br><br>

    Sex:
    <select name="sex">
        <option value="Male" <?php if($row['sex']=='Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if($row['sex']=='Female') echo 'selected'; ?>>Female</option>
    </select><br><br>

    Address: <input type="text" name="address" value="<?php echo $row['address']; ?>"><br><br>
    Guardian Name: <input type="text" name="guardian_name" value="<?php echo $row['guardian_name']; ?>"><br><br>
    Contact Number: <input type="text" name="contact_number" value="<?php echo $row['contact_number']; ?>"><br><br>

    <button name="update">Update</button>
</form>