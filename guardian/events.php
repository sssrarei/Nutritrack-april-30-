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

    <link rel="stylesheet" href="../assets/guardian-style.css">

    <style>

        .events-wrapper{
            display:flex;
            flex-direction:column;
            gap:18px;
        }

        .event-card{
            background:#fff;
            border:1px solid #dcdcdc;
            border-radius:18px;
            padding:22px;
            box-shadow:0 2px 6px rgba(0,0,0,0.04);
        }

        .event-top{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:18px;
            flex-wrap:wrap;
            margin-bottom:14px;
        }

        .event-title{
            font-size:22px;
            font-weight:700;
            color:#1F4E79;
            margin-bottom:6px;
        }

        .event-type{
            display:inline-block;
            padding:6px 12px;
            border-radius:999px;
            background:#edf5fb;
            color:#1F4E79;
            font-size:12px;
            font-weight:700;
        }

        .event-status{
            padding:8px 14px;
            border-radius:999px;
            font-size:12px;
            font-weight:700;
        }

        .event-status.upcoming{
            background:#e8f5e9;
            color:#2e7d32;
        }

        .event-status.completed{
            background:#edf5fb;
            color:#1F4E79;
        }

        .event-status.cancelled{
            background:#fdecea;
            color:#c0392b;
        }

        .event-details{
            display:grid;
            grid-template-columns:repeat(2, 1fr);
            gap:14px;
            margin-top:14px;
        }

        .event-detail-box{
            background:#f8fafc;
            border:1px solid #e5e7eb;
            border-radius:12px;
            padding:14px;
        }

        .event-detail-label{
            font-size:11px;
            font-weight:700;
            color:#6b7280;
            margin-bottom:6px;
            text-transform:uppercase;
        }

        .event-detail-value{
            font-size:14px;
            color:#374151;
            line-height:1.5;
        }

        .event-description{
            margin-top:16px;
            border-top:1px solid #e5e7eb;
            padding-top:16px;
        }

        .event-description h4{
            font-size:14px;
            color:#1f2937;
            margin-bottom:8px;
        }

        .event-description p{
            font-size:14px;
            color:#4b5563;
            line-height:1.7;
        }

        .empty-events{
            background:#fff;
            border:1px dashed #cfd6df;
            border-radius:18px;
            padding:24px;
            text-align:center;
            color:#6b7280;
            font-size:14px;
        }

        @media(max-width:768px){

            .event-details{
                grid-template-columns:1fr;
            }

            .event-title{
                font-size:18px;
            }

        }

    </style>

</head>
<body>

<?php include '../includes/guardian_topbar.php'; ?>
<?php include '../includes/guardian_sidebar.php'; ?>

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