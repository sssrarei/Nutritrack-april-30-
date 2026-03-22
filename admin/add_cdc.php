<?php
include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 1){
    header("Location: ../login.php");
    exit();
}

$message = "";
$error = "";

if(isset($_POST['add_cdc'])){
    $cdc_name = trim($_POST['cdc_name']);
    $barangay = trim($_POST['barangay']);
    $address = trim($_POST['address']);

    if(empty($cdc_name) || empty($barangay)){
        $error = "CDC name and barangay are required.";
    } else {
        $check = $conn->query("SELECT * FROM cdc WHERE cdc_name = '$cdc_name' AND barangay = '$barangay'");

        if($check && $check->num_rows > 0){
            $error = "CDC already exists.";
        } else {
            $sql = "INSERT INTO cdc (cdc_name, barangay, address)
                    VALUES ('$cdc_name', '$barangay', '$address')";

            if($conn->query($sql)){
                $message = "CDC added successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<h1>Add CDC</h1>

<?php if(!empty($message)){ ?>
    <p style="color:green;"><?php echo $message; ?></p>
<?php } ?>

<?php if(!empty($error)){ ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php } ?>

<form method="POST">
    <label>CDC Name:</label>
    <input type="text" name="cdc_name" required>

    <br><br>

    <label>Barangay:</label>
    <input type="text" name="barangay" required>

    <br><br>

    <label>Address:</label>
    <input type="text" name="address">

    <br><br>

    <button type="submit" name="add_cdc">Save CDC</button>
</form>

<br><br>
<a href="dashboard.php">Back to Dashboard</a>