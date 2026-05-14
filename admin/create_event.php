<?php
include '../includes/auth.php';
include '../config/database.php';

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_type = trim($_POST['event_type']);
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $location = trim($_POST['location']);
    $status = $_POST['status'];

    $created_by = $_SESSION['user_id'];

    if (
        empty($title) ||
        empty($event_type) ||
        empty($event_date)
    ) {
        $error = "Please fill in all required fields.";
    } else {

        $sql = "
            INSERT INTO events (
                title,
                description,
                event_type,
                event_date,
                start_time,
                end_time,
                location,
                status,
                created_by
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssssssssi",
            $title,
            $description,
            $event_type,
            $event_date,
            $start_time,
            $end_time,
            $location,
            $status,
            $created_by
        );

        if ($stmt->execute()) {
            $success = "Event created successfully.";
        } else {
            $error = "Failed to create event.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Event</title>

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
            <h1>Create Event</h1>
        </div>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="event-form-card">

            <form method="POST">

                <div class="event-form-grid">

                    <div class="event-form-group">
                        <label>Event Title *</label>
                        <input type="text" name="title" required>
                    </div>

                    <div class="event-form-group">
                        <label>Event Type *</label>

                        <select name="event_type" required>
                            <option value="">Select Type</option>
                            <option value="Nutrition Month">Nutrition Month</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Seminar">Seminar</option>
                            <option value="Feeding Program">Feeding Program</option>
                            <option value="Health Check-up">Health Check-up</option>
                            <option value="Community Program">Community Program</option>
                            <option value="Announcement">Announcement</option>
                        </select>
                    </div>

                    <div class="event-form-group">
                        <label>Event Date *</label>
                        <input type="date" name="event_date" required>
                    </div>

                    <div class="event-form-group">
                        <label>Location</label>
                        <input type="text" name="location">
                    </div>

                    <div class="event-form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time">
                    </div>

                    <div class="event-form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time">
                    </div>

                    <div class="event-form-group">
                        <label>Status</label>

                        <select name="status">
                            <option value="Upcoming">Upcoming</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="event-form-group full">
                        <label>Description</label>
                        <textarea name="description"></textarea>
                    </div>

                </div>

                <br>

                <button type="submit" class="event-btn">
                    Create Event
                </button>

            </form>

        </div>

    </div>

</main>

<script src="../assets/admin/sidebar.js"></script>

</body>
</html>