<?php
include '../includes/auth.php';
include '../config/database.php';
checkRole(1);

$success = "";
$error = "";

$report_type_options = [
    'WMR Submission',
    'Feeding Attendance Report',
    'Nutritional Status Summary',
    'Terminal Report',
    'Custom'
];

function getDefaultReminderMessage($report_type, $deadline) {
    $formatted_deadline = !empty($deadline) ? date("F d, Y", strtotime($deadline)) : '';

    switch ($report_type) {
        case 'WMR Submission':
            return "Please submit the Weight Monitoring Report on or before {$formatted_deadline}. Ensure all data is complete and accurate.";
        case 'Feeding Attendance Report':
            return "Please submit the Feeding Attendance Report on or before {$formatted_deadline}. Make sure attendance and food details are complete.";
        case 'Nutritional Status Summary':
            return "Please submit the Nutritional Status Summary on or before {$formatted_deadline}. Kindly review all nutritional classifications before submission.";
        case 'Terminal Report':
            return "Please submit the Terminal Report on or before {$formatted_deadline}. Ensure the baseline, midline, and endline data are complete.";
        default:
            return "";
    }
}

if (isset($_POST['create_reminder'])) {
    $title = trim($_POST['title']);
    $deadline = trim($_POST['deadline']);
    $report_type = trim($_POST['report_type']);
    $message = trim($_POST['message']);
    $cdc_ids = isset($_POST['cdc_ids']) ? $_POST['cdc_ids'] : [];
    $created_by = $_SESSION['user_id'];

    if ($title == "" || $deadline == "" || $report_type == "" || empty($cdc_ids)) {
        $error = "Please fill in all required fields.";
    } elseif (!in_array($report_type, $report_type_options)) {
        $error = "Invalid reminder type selected.";
    } else {
        if ($message == "" && $report_type !== 'Custom') {
            $message = getDefaultReminderMessage($report_type, $deadline);
        }

        $duplicate_found = false;

        foreach ($cdc_ids as $cdc_id) {
            $cdc_id = (int)$cdc_id;

            $dup_stmt = $conn->prepare("
                SELECT rt.reminder_target_id
                FROM reminders r
                INNER JOIN reminder_targets rt ON r.reminder_id = rt.reminder_id
                WHERE rt.cdc_id = ?
                  AND r.report_type = ?
                  AND r.deadline = ?
                  AND r.status = 'active'
                LIMIT 1
            ");
            $dup_stmt->bind_param("iss", $cdc_id, $report_type, $deadline);
            $dup_stmt->execute();
            $dup_result = $dup_stmt->get_result();

            if ($dup_result && $dup_result->num_rows > 0) {
                $duplicate_found = true;
                break;
            }
        }

        if ($duplicate_found) {
            $error = "A reminder already exists for one of the selected CDCs, report type, and deadline.";
        } else {
            $insert_reminder = $conn->prepare("
                INSERT INTO reminders (title, message, deadline, report_type, created_by, status)
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $insert_reminder->bind_param("ssssi", $title, $message, $deadline, $report_type, $created_by);

            if ($insert_reminder->execute()) {
                $reminder_id = $conn->insert_id;

                $target_stmt = $conn->prepare("
                    INSERT INTO reminder_targets (reminder_id, cdc_id)
                    VALUES (?, ?)
                ");

                $notification_stmt = $conn->prepare("
                    INSERT INTO notifications (user_id, reminder_id, title, message, deadline)
                    VALUES (?, ?, ?, ?, ?)
                ");

                foreach ($cdc_ids as $cdc_id) {
                    $cdc_id = (int)$cdc_id;

                    $target_stmt->bind_param("ii", $reminder_id, $cdc_id);
                    $target_stmt->execute();

                    $cdw_stmt = $conn->prepare("
                        SELECT DISTINCT user_id
                        FROM cdw_assignments
                        WHERE cdc_id = ?
                    ");
                    $cdw_stmt->bind_param("i", $cdc_id);
                    $cdw_stmt->execute();
                    $cdw_result = $cdw_stmt->get_result();

                    if ($cdw_result && $cdw_result->num_rows > 0) {
                        while ($cdw = $cdw_result->fetch_assoc()) {
                            $user_id = (int)$cdw['user_id'];
                            $notification_stmt->bind_param("iisss", $user_id, $reminder_id, $title, $message, $deadline);
                            $notification_stmt->execute();
                        }
                    }
                }

                $success = "Reminder created and sent successfully.";
                $_POST = [];
            } else {
                $error = "Failed to create reminder.";
            }
        }
    }
}

$cdc_list = $conn->query("
    SELECT cdc_id, cdc_name
    FROM cdc
    ORDER BY cdc_name ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Reminder</title>
    <link rel="stylesheet" href="../assets/admin/admin-style.css">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Inter', sans-serif;
            color: #1f2937;
        }

        .main-content {
            padding: 24px;
        }

        .page-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            padding: 24px;
        }

        .page-header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 6px;
        }

        .page-header p {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 22px;
            line-height: 1.6;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 12px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: #111827;
        }

        .form-group select[multiple] {
            min-height: 180px;
        }

        .form-group textarea {
            min-height: 140px;
            resize: vertical;
        }

        .form-note {
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border: none;
            border-radius: 10px;
            padding: 11px 18px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-light {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #2563eb;
        }

        .checkbox-item label {
            cursor: pointer;
            font-size: 14px;
            color: #374151;
            margin: 0;
        }

        .checkbox-item input[type="checkbox"]:hover + label {
            color: #2563eb;
        }

        body.dark-mode {
            background: #111827;
            color: #f9fafb;
        }

        body.dark-mode .page-card {
            background: #1f2937;
            box-shadow: none;
        }

        body.dark-mode .page-header h1 {
            color: #93c5fd;
        }

        body.dark-mode .page-header p,
        body.dark-mode .form-note {
            color: #d1d5db;
        }

        body.dark-mode .form-group label {
            color: #f9fafb;
        }

        body.dark-mode .checkbox-item label {
            color: #d1d5db;
        }

        body.dark-mode .checkbox-item input[type="checkbox"]:hover + label {
            color: #60a5fa;
        }

        body.dark-mode .form-group input,
        body.dark-mode .form-group select,
        body.dark-mode .form-group textarea {
            background: #111827;
            border: 1px solid #374151;
            color: #f9fafb;
        }

        body.dark-mode .btn-light {
            background: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="<?php echo (isset($_SESSION['theme_mode']) && $_SESSION['theme_mode'] === 'dark') ? 'dark-mode' : ''; ?>">

<?php include '../includes/admin_sidebar.php'; ?>
<?php include '../includes/admin_topbar.php'; ?>

<div class="main-content" id="mainContent">
    <div class="page-card">
        <div class="page-header">
            <h1>Create Reminder</h1>
            <p>Create reminders for CDWs about report deadlines for each CDC. These reminders will be sent to the assigned CDWs and can be marked as read in their notifications panel.</p>
        </div>

        <?php if ($success != "") { ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>

        <?php if ($error != "") { ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group full">
                    <label for="title">Title</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        required
                        value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="deadline">Deadline</label>
                    <input
                        type="date"
                        id="deadline"
                        name="deadline"
                        required
                        value="<?php echo isset($_POST['deadline']) ? htmlspecialchars($_POST['deadline']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="report_type">Reminder Type</label>
                    <select name="report_type" id="report_type" required>
                        <option value="">Select Reminder Type</option>
                        <?php foreach ($report_type_options as $option) { ?>
                            <option value="<?php echo htmlspecialchars($option); ?>" <?php echo (isset($_POST['report_type']) && $_POST['report_type'] === $option) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($option); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group full">
                    <label>Assigned CDCs</label>
                    <div class="checkbox-group">
                        <?php if ($cdc_list && $cdc_list->num_rows > 0) { ?>
                            <?php while ($cdc = $cdc_list->fetch_assoc()) { ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="cdc_ids[]" id="cdc_<?php echo (int)$cdc['cdc_id']; ?>" value="<?php echo (int)$cdc['cdc_id']; ?>" 
                                        <?php echo (isset($_POST['cdc_ids']) && in_array($cdc['cdc_id'], $_POST['cdc_ids'])) ? 'checked' : ''; ?>>
                                    <label for="cdc_<?php echo (int)$cdc['cdc_id']; ?>"><?php echo htmlspecialchars($cdc['cdc_name']); ?></label>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <div class="form-note">Select at least one CDC.</div>
                </div>

                <div class="form-group full">
                    <label for="message">Message (Optional)</label>
                    <textarea name="message" id="message"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    <div class="form-note">If left empty, the system will generate a default message based on the selected reminder type.</div>
                </div>
            </div>

            <div class="form-actions">
                <a href="dashboard.php" class="btn btn-light">Cancel</a>
                <button type="submit" name="create_reminder" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>


<script src="../assets/admin/sidebar.js"></script>
</body>
</html>