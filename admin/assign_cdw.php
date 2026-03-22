<?php
include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 1){
    header("Location: ../login.php");
    exit();
}

// get CDWs
$cdw = $conn->query("SELECT * FROM users WHERE role_id = 2");

// get CDC
$cdc = $conn->query("SELECT * FROM cdc");

if(isset($_POST['assign'])){
    $user_id = $_POST['user_id'];
    $cdc_id = $_POST['cdc_id'];

    $sql = "INSERT INTO cdw_assignments (user_id, cdc_id)
            VALUES ('$user_id', '$cdc_id')";

    $conn->query($sql);
    echo "Assigned successfully!";
}
?>

<h2>Assign CDW</h2>

<form method="POST">
    CDW:
    <select name="user_id">
        <?php while($row = $cdw->fetch_assoc()){ ?>
            <option value="<?php echo $row['user_id']; ?>">
                <?php echo $row['first_name']; ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    CDC:
    <select name="cdc_id">
        <?php while($row = $cdc->fetch_assoc()){ ?>
            <option value="<?php echo $row['cdc_id']; ?>">
                <?php echo $row['cdc_name']; ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    <button name="assign">Assign</button>
</form>