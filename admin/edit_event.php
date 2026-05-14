<?php
include '../includes/auth.php';
include '../config/database.php';

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_events.php");
    exit();
}

$event_id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['delete_event'])) {
        $delete_sql = "UPDATE events SET is_deleted = 1 WHERE event_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $event_id);

        if ($delete_stmt->execute()) {
            header("Location: manage_events.php");
            exit();
        } else {
            $error = "Failed to delete event.";
        }
    } else {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $event_type = trim($_POST['event_type']);
        $event_date = $_POST['event_date'];
        $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
        $location = trim($_POST['location']);
        $status = $_POST['status'];

        if (empty($title) || empty($event_type) || empty($event_date)) {
            $error = "Please fill in all required fields.";
        } else {
            $update_sql = "
                UPDATE events
                SET 
                    title = ?,
                    description = ?,
                    event_type = ?,
                    event_date = ?,
                    start_time = ?,
                    end_time = ?,
                    location = ?,
                    status = ?
                WHERE event_id = ?
            ";

            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param(
                "ssssssssi",
                $title,
                $description,
                $event_type,
                $event_date,
                $start_time,
                $end_time,
                $location,
                $status,
                $event_id
            );

            if ($update_stmt->execute()) {
                $success = "Event updated successfully.";
            } else {
                $error = "Failed to update event.";
            }
        }
    }
}

$sql = "SELECT * FROM events WHERE event_id = ? AND is_deleted = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_events.php");
    exit();
}

$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>

    <link rel="stylesheet" href="../assets/admin/admin-style.css">

    <style>
        .event-form-card{
            background:#fff;
            border:1px solid #dcdcdc;
            border-radius:18px;
            padding:24px;
        }

        .event-form-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
        }

        .event-form-group{
            display:flex;
            flex-direction:column;
            gap:8px;
        }

        .event-form-group.full{
            grid-column:1 / -1;
        }

        .event-form-group label{
            font-size:13px;
            font-weight:600;
            color:#374151;
        }

        .event-form-group input,
        .event-form-group select,
        .event-form-group textarea{
            border:1px solid #cfd6df;
            border-radius:10px;
            padding:12px;
            font-size:14px;
            outline:none;
            background:#fff;
        }

        .event-form-group textarea{
            resize:vertical;
            min-height:120px;
        }

        .event-actions{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            margin-top:20px;
        }

        .event-btn{
            border:none;
            background:#1F4E79;
            color:#fff;
            padding:12px 18px;
            border-radius:10px;
            font-size:14px;
            font-weight:700;
            cursor:pointer;
        }

        .event-btn.delete{
            background:#C0392B;
        }

        .event-btn.back{
            background:#6b7280;
            display:inline-block;
        }

        .success-message{
            background:#e8f5e9;
            color:#2e7d32;
            padding:14px;
            border-radius:10px;
            margin-bottom:18px;
            font-size:13px;
            font-weight:600;
        }

        .error-message{
            background:#fdecea;
            color:#c0392b;
            padding:14px;
            border-radius:10px;
            margin-bottom:18px;
            font-size:13px;
            font-weight:600;
        }

        @media(max-width:768px){
            .event-form-grid{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/admin_topbar.php'; ?>
<?php include '../includes/admin_sidebar.php'; ?>

<main class="main-content">

    <div class="records-page-shell">

        <div class="records-page-header">
            <h1>Edit Event</h1>
        </div>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="event-form-card">

            <form method="POST">

                <div class="event-form-grid">

                    <div class="event-form-group">
                        <label>Event Title *</label>
                        <input 
                            type="text" 
                            name="title" 
                            required
                            value="<?php echo htmlspecialchars($event['title']); ?>"
                        >
                    </div>

                    <div class="event-form-group">
                        <label>Event Type *</label>
                        <select name="event_type" required>
                            <?php
                            $types = [
                                "Nutrition Month",
                                "Meeting",
                                "Seminar",
                                "Feeding Program",
                                "Health Check-up",
                                "Community Program",
                                "Announcement"
                            ];

                            foreach ($types as $type):
                            ?>
                                <option 
                                    value="<?php echo $type; ?>"
                                    <?php echo $event['event_type'] === $type ? 'selected' : ''; ?>
                                >
                                    <?php echo $type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="event-form-group">
                        <label>Event Date *</label>
                        <input 
                            type="date" 
                            name="event_date" 
                            required
                            value="<?php echo htmlspecialchars($event['event_date']); ?>"
                        >
                    </div>

                    <div class="event-form-group">
                        <label>Location</label>
                        <input 
                            type="text" 
                            name="location"
                            value="<?php echo htmlspecialchars($event['location']); ?>"
                        >
                    </div>

                    <div class="event-form-group">
                        <label>Start Time</label>
                        <input 
                            type="time" 
                            name="start_time"
                            value="<?php echo htmlspecialchars($event['start_time']); ?>"
                        >
                    </div>

                    <div class="event-form-group">
                        <label>End Time</label>
                        <input 
                            type="time" 
                            name="end_time"
                            value="<?php echo htmlspecialchars($event['end_time']); ?>"
                        >
                    </div>

                    <div class="event-form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Upcoming" <?php echo $event['status'] === 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="Completed" <?php echo $event['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo $event['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="event-form-group full">
                        <label>Description</label>
                        <textarea name="description"><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>

                </div>

                <div class="event-actions">
                    <button type="submit" class="event-btn">
                        Save Changes
                    </button>

                    <button 
                        type="submit" 
                        name="delete_event" 
                        class="event-btn delete"
                        onclick="return confirm('Are you sure you want to delete this event?');"
                    >
                        Delete Event
                    </button>

                    <a href="manage_events.php" class="event-btn back">
                        Back
                    </a>
                </div>

            </form>

        </div>

    </div>

</main>

<script src="../assets/admin/sidebar.js"></script>

</body>
</html>