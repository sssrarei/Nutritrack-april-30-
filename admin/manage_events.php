<?php
include '../includes/auth.php';
include '../config/database.php';

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$sql = "
    SELECT 
        event_id,
        title,
        description,
        event_type,
        event_date,
        start_time,
        end_time,
        location,
        status,
        created_at
    FROM events
    WHERE is_deleted = 0
";

$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR event_type LIKE ? OR location LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($status_filter !== '') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY event_date ASC, start_time ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

function formatDate($date) {
    return date("F d, Y", strtotime($date));
}

function formatTime($time) {
    if (empty($time)) {
        return "Not set";
    }
    return date("h:i A", strtotime($time));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Planner</title>
    <link rel="stylesheet" href="../assets/admin/admin-style.css">

</head>
<body>

<?php include '../includes/admin_topbar.php'; ?>
<?php include '../includes/admin_sidebar.php'; ?>

<main class="main-content" id="mainContent">
    <div class="records-page-shell">

        <div class="records-page-header">
            <h1>Event Planner</h1>
        </div>

        <div class="records-toolbar-card">
            <div class="records-toolbar-left">
                <form method="GET" class="records-search-wrap">
                    <div class="records-search-icon">🔍</div>
                    <input 
                        type="text" 
                        name="search" 
                        class="records-search-input" 
                        placeholder="Search event, type, or location..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                </form>
            </div>

            <div class="records-toolbar-right">
                <form method="GET">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <select name="status" class="records-filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="Upcoming" <?php echo $status_filter == 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </form>

                <a href="create_event.php" class="quick-btn navy">+ Create Event</a>
            </div>
        </div>

        <div class="records-table-card">
            <div class="records-table-wrap">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($event = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                        <br>
                                        <small><?php echo htmlspecialchars(substr($event['description'], 0, 80)); ?>...</small>
                                    </td>

                                    <td><?php echo htmlspecialchars($event['event_type']); ?></td>

                                    <td><?php echo formatDate($event['event_date']); ?></td>

                                    <td>
                                        <?php echo formatTime($event['start_time']); ?>
                                        -
                                        <?php echo formatTime($event['end_time']); ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($event['location']); ?></td>

                                    <td>
                                        <?php
                                            $statusClass = 'records-view-btn';

                                            if ($event['status'] == 'Upcoming') {
                                                $statusColor = 'background:#edf5fb;color:#1f4e79;border-color:#9fb8c9;';
                                            } elseif ($event['status'] == 'Completed') {
                                                $statusColor = 'background:#e8f5e9;color:#2e7d32;border-color:#a5d6a7;';
                                            } else {
                                                $statusColor = 'background:#fdecea;color:#c0392b;border-color:#f5b7b1;';
                                            }
                                        ?>
                                        <span class="<?php echo $statusClass; ?>" style="<?php echo $statusColor; ?>">
                                            <?php echo htmlspecialchars($event['status']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <a href="edit_event.php?id=<?php echo $event['event_id']; ?>" class="records-view-btn">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="records-empty">
                                    No events found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="records-footer">
                <div class="records-footer-left">
                    Event Planner records are managed by CSWD Admin.
                </div>
            </div>
        </div>

    </div>
</main>

<script src="../assets/admin/sidebar.js"></script>

</body>
</html>