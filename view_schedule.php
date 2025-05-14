<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

$host = "localhost";
$dbname = "powerpulse";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$schedule_id = $_GET['schedule_id'] ?? null;
$schedules = $conn->query("SELECT * FROM schedules WHERE user_id = $user_id");

$workouts = null;
if ($schedule_id) {
    $stmt = $conn->prepare("SELECT w.workout_name, w.workout_description, sw.reps, sw.sets, sw.weight
        FROM schedule_workouts sw
        JOIN workouts w ON sw.workout_id = w.id
        WHERE sw.schedule_id = ? 
    ");
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $workouts = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Workout Schedules | PowerPulse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #ff6600;
            --background: #121212;
            --card-bg: #1e1e1e;
            --text-color: #fff;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--background);
            color: var(--text-color);
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(255, 102, 0, 0.2);
        }
        h2, h3 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 1.5rem;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        select {
            padding: 12px;
            border-radius: 8px;
            border: none;
            background-color: #2c2c2c;
            color: white;
            font-size: 1rem;
        }
        .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        button, .button-link {
            padding: 12px 18px;
            border: 2px solid var(--primary);
            border-radius: 8px;
            background-color: transparent;
            color: var(--primary);
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        button:hover, .button-link:hover {
            background-color: var(--primary);
            color: black;
        }
        .workout {
            background-color: #2a2a2a;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: transform 0.2s ease;
        }
        .workout:hover {
            transform: scale(1.01);
        }
        .workout strong {
            font-size: 1.2rem;
            color: var(--primary);
        }
        @media (max-width: 600px) {
            .buttons {
                flex-direction: column;
            }
        }
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
</head>
<body>
<div class="container">
    <button class="back-button"><a href="dashboard.php" style="text-decoration:none; color:inherit;">⬅ Back</a></button>

    <h2>Select a Schedule</h2>
    <form method="GET">
        <select name="schedule_id" required>
            <option value="">-- Choose a Schedule --</option>
            <?php while ($row = $schedules->fetch_assoc()): ?>
                <option value="<?= $row['schedule_id'] ?>" <?= $schedule_id == $row['schedule_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['schedule_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <div class="buttons">
            <button type="submit">View Workouts</button>
            <a href="schedule.php" class="button-link">Add Schedule</a>
            <a href="delete_schedule.php" class="button-link">Delete Schedule</a>
        </div>
    </form>

    <?php if ($workouts): ?>
        <h3>Workout List</h3>
        <?php while($w = $workouts->fetch_assoc()): ?>
            <div class="workout">
                <strong><?= htmlspecialchars($w['workout_name']) ?></strong><br>
                <?= nl2br(htmlspecialchars($w['workout_description'])) ?><br><br>
                Reps: <?= $w['reps'] ?> |
                Sets: <?= $w['sets'] ?> |
                Weight: <?= $w['weight'] ?> kg
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>
</body>
</html>
