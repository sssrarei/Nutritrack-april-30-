<?php
include '../config/database.php';

if(isset($_POST['save'])){
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $birthdate = $_POST['birthdate'];
    $sex = $_POST['sex'];
    $address = $_POST['address'];
    $guardian_name = $_POST['guardian_name'];
    $contact_number = $_POST['contact_number'];

    $sql = "INSERT INTO children 
    (first_name, middle_name, last_name, birthdate, sex, address, guardian_name, contact_number)
    VALUES 
    ('$first_name','$middle_name','$last_name','$birthdate','$sex','$address','$guardian_name','$contact_number')";

    if($conn->query($sql)){
        echo "Child added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<h2>Add Child</h2>
<form method="POST">
    First Name: <input type="text" name="first_name" required><br><br>
    Middle Name: <input type="text" name="middle_name"><br><br>
    Last Name: <input type="text" name="last_name" required><br><br>
    Birthdate: <input type="date" name="birthdate" required><br><br>

    Sex:
    <select name="sex">
        <option>Male</option>
        <option>Female</option>
    </select><br><br>

    Address: <input type="text" name="address"><br><br>
    Guardian Name: <input type="text" name="guardian_name"><br><br>
    Contact Number: <input type="text" name="contact_number"><br><br>

    <button name="save">Save</button>
</form>