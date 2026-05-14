<?php
include '../includes/auth.php';
include '../config/database.php';

if (!isset($_SESSION['role_id'])) {
    header("Location: ../login.php");
    exit();
}

$sql = "
    SELECT *
    FROM events
    WHERE is_deleted = 0
    ORDER BY event_date ASC, start_time ASC
";

$result = mysqli_query($conn, $sql);

function formatDateEvent($date) {
    return date("F d, Y", strtotime($date));
}

function formatTimeEvent($time) {
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
    <title>Community Events</title>

    <link rel="stylesheet" href="../assets/cdw/cdw-style.css">
    <link rel="stylesheet" href="../assets/cdw/event.css">

    <

</head>
<body>

<?php include '../includes/cdw_topbar.php'; ?>
<?php include '../includes/cdw_sidebar.php'; ?>

<main class="main-content">

    <div class="records-page-shell">

        <div class="records-page-header">
            <h1>Community Events</h1>
        </div>

        <div class="events-wrapper">

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <?php while ($event = mysqli_fetch_assoc($result)): ?>

                    <div class="event-card">

                        <div class="event-top">

                            <div>
                                <div class="event-title">
                                    <?php echo htmlspecialchars($event['title']); ?>
                                </div>

                                <span class="event-type">
                                    <?php echo htmlspecialchars($event['event_type']); ?>
                                </span>
                            </div>

                            <div class="event-status <?php echo strtolower($event['status']); ?>">
                                <?php echo htmlspecialchars($event['status']); ?>
                            </div>

                        </div>

                        <div class="event-details">

                            <div class="event-detail-box">
                                <div class="event-detail-label">
                                    Event Date
                                </div>

                                <div class="event-detail-value">
                                    <?php echo formatDateEvent($event['event_date']); ?>
                                </div>
                            </div>

                            <div class="event-detail-box">
                                <div class="event-detail-label">
                                    Event Time
                                </div>

                                <div class="event-detail-value">
                                    <?php echo formatTimeEvent($event['start_time']); ?>
                                    -
                                    <?php echo formatTimeEvent($event['end_time']); ?>
                                </div>
                            </div>

                            <div class="event-detail-box">
                                <div class="event-detail-label">
                                    Location
                                </div>

                                <div class="event-detail-value">
                                    <?php echo !empty($event['location']) ? htmlspecialchars($event['location']) : 'Not specified'; ?>
                                </div>
                            </div>

                            <div class="event-detail-box">
                                <div class="event-detail-label">
                                    Event Status
                                </div>

                                <div class="event-detail-value">
                                    <?php echo htmlspecialchars($event['status']); ?>
                                </div>
                            </div>

                        </div>

                        <?php if (!empty($event['description'])): ?>

                            <div class="event-description">

                                <h4>Description</h4>

                                <p>
                                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                                </p>

                            </div>

                        <?php endif; ?>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="empty-events">
                    No community events available.
                </div>

            <?php endif; ?>

        </div>

    </div>

</main>

</body>
</html>