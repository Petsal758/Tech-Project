<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "powerpulse");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['schedule_id'])) {
    $schedule_id = $_POST['schedule_id'];

    $stmt = $conn->prepare("SELECT * FROM schedules WHERE schedule_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $schedule_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt_del1 = $conn->prepare("DELETE FROM schedule_workouts WHERE schedule_id = ?");
        $stmt_del1->bind_param("i", $schedule_id);
        $stmt_del1->execute();
        $stmt_del1->close();

        $stmt_del2 = $conn->prepare("DELETE FROM schedules WHERE schedule_id = ?");
        $stmt_del2->bind_param("i", $schedule_id);
        $stmt_del2->execute();
        $stmt_del2->close();

        $_SESSION['message'] = "Schedule successfully removed.";
    } else {
        $_SESSION['message'] = "Schedule not found or access denied.";
    }

    header("Location: delete_schedule.php");
    exit;
}

$stmt2 = $conn->prepare("SELECT schedule_id, schedule_name FROM schedules WHERE user_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$schedules = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Remove Schedule</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0a0a0a;
            color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
            margin: 4rem auto;
            background-color: #ffa500;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(255, 165, 0, 0.4);
            color: #000;
        }
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 1.2rem;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background-color: #000;
            color: #ffa500;
            font-weight: bold;
            font-size: 1rem;
            border: 2px solid #ffa500;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background-color: #ffa500;
            color: #000;
        }
        .message {
            text-align: center;
            margin-top: 1rem;
            color: green;
            font-weight: bold;
        }
    </style>
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this schedule?");
        }
    </script>
</head>
<body>
    <div class="container">
        <style>
    .back-button {
        padding: 12px 20px;
        background-color: #ff6600;
        color: #000;
        font-weight: bold;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
        box-shadow: 0 4px 10px rgba(255, 102, 0, 0.3);
    }

    .back-button:hover {
        background-color: #ff8533;
        transform: scale(1.05);
    }
    .back-button a {
    color: inherit;
    text-decoration: none;
    user-select: none;
}

.back-button a:focus,
.back-button a:active {
    outline: none;
    background: none;
}
</style>
<button class="back-button"><a href="view_schedule.php">⬅ Back</a></button>

        <h2>Remove a Schedule</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <p class="message"><?= htmlspecialchars($_SESSION['message']) ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form method="POST" onsubmit="return confirmDelete();">
            <label for="schedule_id">Select a Schedule:</label>
            <select name="schedule_id" id="schedule_id" required>
                <?php while ($row = $schedules->fetch_assoc()): ?>
                    <option value="<?= $row['schedule_id'] ?>">
                        <?= htmlspecialchars($row['schedule_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Delete Schedule</button>
        </form>
    </div>
</body>
</html>
