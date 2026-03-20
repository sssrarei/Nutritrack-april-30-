<?php
include '../config/database.php';

$id = $_GET['id'];

$sql = "DELETE FROM children WHERE child_id=$id";

if($conn->query($sql)){
    header("Location: child_list.php");
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>