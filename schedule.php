<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "powerpulse");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle schedule creation
if (isset($_POST['create_schedule'])) {
    $schedule_name = $_POST['schedule_name'];
    $stmt = $conn->prepare("INSERT INTO schedules (user_id, schedule_name) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $schedule_name);
    $stmt->execute();
    $stmt->close();
}

// Handle workout addition
if (isset($_POST['add_workout'])) {
    $schedule_id = $_POST['schedule_id'];
    $workout_id = $_POST['workout_id'];
    $reps = $_POST['reps'];
    $sets = $_POST['sets'];
    $weight = $_POST['weight'];

    // Optional: validate workout_id exists
    $check = $conn->prepare("SELECT id FROM workouts WHERE id = ?");
    $check->bind_param("i", $workout_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        die("Invalid workout selected.");
    }
    $check->close();

    $stmt = $conn->prepare("INSERT INTO schedule_workouts (schedule_id, workout_id, reps, sets, weight) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiid", $schedule_id, $workout_id, $reps, $sets, $weight);
    $stmt->execute();
    $stmt->close();
}

// Fetch all workouts
$workouts = $conn->query("SELECT * FROM workouts");

// Fetch schedules for user
$schedules = $conn->query("SELECT * FROM schedules WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Workout Schedule</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background-color: #0a0a0a;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            margin: 0;
        }
        .container {
            background-color: #ffa500;
            color: #000;
            max-width: 700px;
            margin: 4rem auto;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.5);
        }
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        form {
            margin-bottom: 2rem;
        }
        label {
            display: block;
            margin-top: 1rem;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 12px;
            margin-top: 0.5rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        button {
            margin-top: 1.5rem;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            background-color: #000;
            color: #ffa500;
            font-weight: bold;
            border: 2px solid #ffa500;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background-color: #ffa500;
            color: #000;
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
        <button class="back-button"><a href="view_schedule.php">⬅ Back</a></button>

        <h2>Create a New Schedule</h2>
        <form method="POST">
            <input type="text" name="schedule_name" placeholder="Schedule Name" required>
            <button type="submit" name="create_schedule">Create Schedule</button>
        </form>

        <h2>Add Workout to Schedule</h2>
        <form method="POST">
            <label for="schedule_id">Choose Schedule</label>
            <select name="schedule_id" id="schedule_id" required>
                <?php while($row = $schedules->fetch_assoc()): ?>
                    <option value="<?= $row['schedule_id'] ?>">
                        <?= htmlspecialchars($row['schedule_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="workout_id">Choose Workout</label>
            <select name="workout_id" id="workout_id" required>
                <?php
                $workouts->data_seek(0);
                while($workout = $workouts->fetch_assoc()):
                ?>
                <option value="<?= $workout['id'] ?>">
                    <?= htmlspecialchars($workout['workout_name']) ?> (<?= htmlspecialchars($workout['workout_type']) ?>)
                </option>
                <?php endwhile; ?>
            </select>

            <input type="number" name="reps" placeholder="Reps" required>
            <input type="number" name="sets" placeholder="Sets" required>
            <input type="number" step="0.1" name="weight" placeholder="Weight (kg)" required>

            <button type="submit" name="add_workout">Add Workout</button>
        </form>
    </div>
</body>
</html>
