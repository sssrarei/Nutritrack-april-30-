<?php
include '../includes/auth.php';
include '../config/database.php';

if($_SESSION['role_id'] != 2){
    header("Location: ../login.php");
    exit();
}

if(!isset($_SESSION['active_cdc_id'])){
    die("Please select an active CDC first from the dashboard.");
}

$cdc_id = $_SESSION['active_cdc_id'];
$search = "";

$sql = "SELECT child_id, first_name, middle_name, last_name 
        FROM children 
        WHERE cdc_id = '$cdc_id'";

if(isset($_GET['search']) && !empty(trim($_GET['search']))){
    $search = trim($_GET['search']);
    $sql .= " AND (
        first_name LIKE '%$search%' OR
        middle_name LIKE '%$search%' OR
        last_name LIKE '%$search%'
    )";
}

$sql .= " ORDER BY first_name ASC, last_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Child Profile / Add Pupil</title>
</head>
<body>

<p><a href="dashboard.php">Back to Dashboard</a></p>

<h2>Search Pupils</h2>

<p><strong>Active CDC:</strong> <?php echo $_SESSION['active_cdc_name']; ?></p>

<form method="GET">
    <input 
        type="text" 
        name="search" 
        placeholder="Search Pupils" 
        value="<?php echo htmlspecialchars($search); ?>"
    >
    <button type="submit">Search</button>
    <a href="add_child.php">
        <button type="button">Add Child</button>
    </a>
</form>

<br>

<h3>Pupils List</h3>

<?php if($result && $result->num_rows > 0){ ?>
    <?php while($row = $result->fetch_assoc()){ ?>
        <p>
            <a href="child_profile.php?child_id=<?php echo $row['child_id']; ?>">
                <?php echo $row['first_name'] . " " . $row['last_name']; ?>
            </a>
        </p>
    <?php } ?>
<?php } else { ?>
    <p>No pupils found.</p>
<?php } ?>

</body>
</html>