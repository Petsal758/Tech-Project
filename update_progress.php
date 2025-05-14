<?php
session_start();
$host = "localhost";
$dbname = "powerpulse";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("You must be logged in to view this page.");
}

// Fetch current user data
$user_stmt = $conn->prepare("SELECT height, weight FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_stmt->bind_result($height, $weight);
$user_stmt->fetch();
$user_stmt->close();

// Fetch calories burned
$cal_stmt = $conn->prepare("SELECT calories_burned FROM progress WHERE user_id = ?");
$cal_stmt->bind_param("i", $user_id);
$cal_stmt->execute();
$cal_stmt->bind_result($calories);
$cal_stmt->fetch();
$cal_stmt->close();

// Calculate BMI
$bmi = $height > 0 ? round(($weight / (($height * 0.0254) ** 2)), 2) : 0;

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_height = $_POST['height'];
    $new_weight = $_POST['weight'];
    $minutes_spent = (float)$_POST['time_spent'];
    $intensity = (float)$_POST['intensity'];

    $new_calories = (($minutes_spent * $intensity) * 3.5 * $new_weight) / 200;

    $get_total_stmt = $conn->prepare("SELECT total_calories FROM progress WHERE user_id = ?");
    $get_total_stmt->bind_param("i", $user_id);
    $get_total_stmt->execute();
    $get_total_stmt->bind_result($existing_total);
    $get_total_stmt->fetch();
    $get_total_stmt->close();

    $updated_total = $existing_total + $new_calories;

    $update_user = $conn->prepare("UPDATE users SET height = ?, weight = ? WHERE id = ?");
    $update_user->bind_param("ddi", $new_height, $new_weight, $user_id);
    $update_user->execute();
    $update_user->close();

    $bmi = $new_height > 0 ? round(($new_weight / (($new_height * 0.0254) ** 2)), 2) : 0;

    $update_progress = $conn->prepare("UPDATE progress SET weight = ?, calories_burned = ?, total_calories = ?, bmi = ? WHERE user_id = ?");
    $update_progress->bind_param("ddddi", $new_weight, $new_calories, $updated_total, $bmi, $user_id);
    $update_progress->execute();
    $update_progress->close();

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Progress</title>
    <style>
        body {
            background-color: #1e1e1e;
            color: #f4f4f4;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 500px;
            margin: 5rem auto;
            background-color: #ff7f00;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #fff;
        }
        label {
            font-weight: bold;
            margin-top: 1rem;
            display: block;
            text-align: left;
            color: #fff;
        }
        input, select {
            width: 100%;
            padding: 12px;
            margin-top: 0.5rem;
            border-radius: 8px;
            border: none;
            background-color: #f1f1f1;
            font-size: 1rem;
        }
        button {
            margin-top: 1.5rem;
            padding: 12px;
            background-color: #1e1e1e;
            color: #ff7f00;
            font-size: 1.2rem;
            font-weight: bold;
            width: 100%;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background-color: #ff9f00;
            color: #1e1e1e;
        }
        .info {
            margin-top: 1rem;
            font-size: 1.1rem;
            color: #fff;
        }
        footer {
            text-align: center;
            background-color: #1e1e1e;
            color: #fff;
            padding: 1rem 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <button onclick="history.back()" class="back-button">⬅ Back</button>
        <h2>Update Progress</h2>
        <form method="POST">
            <label for="height">Height (inches)</label>
            <input type="number" name="height" id="height" value="<?= htmlspecialchars($height) ?>" required>

            <label for="weight">Weight (kgs)</label>
            <input type="number" name="weight" id="weight" value="<?= htmlspecialchars($weight) ?>" required>

            <label for="time_spent">Time Spent Exercising (minutes)</label>
            <input type="number" name="time_spent" step="0.1" placeholder="e.g. 30.5" required>

            <label for="intensity">Level of Intensity</label>
            <select name="intensity" required>
                <option value="1.6">Low Intensity</option>
                <option value="3">Medium Intensity</option>
                <option value="6">High Intensity</option>
            </select>

            <button type="submit">Update</button>
        </form>
        <div class="info">
            <p><strong>BMI:</strong> <?= $bmi ?></p>
            <p><strong>Calories Burned:</strong> <?= $calories ?></p>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 PowerPulse Gym. All rights reserved.</p>
    </footer>
</body>
</html>
