<?php
session_start();

// Database connection
$host = "localhost";
$dbname = "powerpulse";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$calories_burned = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['weight'], $_POST['height'], $_POST['time'], $_POST['intensity'])) {
    $weight = floatval($_POST['weight']);
    $height = floatval($_POST['height']);
    $time = floatval($_POST['time']);
    $intensity = $_POST['intensity'];

    // MET values
    switch ($intensity) {
        case 'low': $METs = 3.5; break;
        case 'medium': $METs = 7; break;
        case 'high': $METs = 10; break;
        default: $METs = 3.5;
    }

    $calories_burned = ($METs * 3.5 * $weight / 200) * $time;

    // Calculate BMI
    $bmi = $height > 0 ? round(($weight / (($height / 100) ** 2)), 2) : 0;

    // Insert into progress table
    $stmt = $conn->prepare("INSERT INTO progress (user_id, weight, height, bmi, calories_burned) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idddd", $user_id, $weight, $height, $bmi, $calories_burned);
    $stmt->execute();
    $stmt->close();

    header("Location: yourprogresspage.php?updated=1");
    exit();
}

// Fetch progress data for chart
$stmt = $conn->prepare("SELECT weight, height, bmi, calories_burned, date FROM progress WHERE user_id = ? ORDER BY date DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$progressData = [];
while ($row = $result->fetch_assoc()) {
    $progressData[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Progress</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0; padding: 0;
        }
        header {
            background-color: #1f1f1f;
            color: #fff;
            padding: 1.5rem;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 1rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .form-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-container input,
        .form-container select,
        .form-container button {
            padding: 10px;
            margin: 0.5rem;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .form-container button {
            background-color: #ff9f00;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .form-container button:hover {
            background-color: #ff7f00;
        }
        canvas {
            width: 100% !important;
            max-height: 400px;
        }
        footer {
            text-align: center;
            background-color: #1f1f1f;
            color: white;
            padding: 1rem;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .back-button {
            background-color: #ff9f00;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .info {
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<header>
    <h1>Your Progress Chart</h1>
</header>

<div class="container">
    <button onclick="history.back()" class="back-button">⬅ Back</button>

    <div class="form-container">
        <h2>Update Your Progress</h2>
        <form method="POST" action="yourprogresspage.php">
            <input type="number" name="weight" placeholder="Weight (kg)" step="0.1" required>
            <input type="number" name="height" placeholder="Height (cm)" step="0.1" required>
            <input type="number" name="time" placeholder="Time (min)" step="0.1" required>
            <select name="intensity" required>
                <option value="">Select Intensity</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
            <button type="submit">Update Progress</button>
        </form>

        <?php if (isset($_GET['updated'])): ?>
            <div class="info">
                <strong>Progress updated successfully!</strong>
            </div>
        <?php endif; ?>
    </div>

    <canvas id="progressChart"></canvas>
</div>

<footer>
    <p>&copy; 2025 PowerPulse Gym. All rights reserved.</p>
</footer>

<script>
    const progressData = <?php echo json_encode(array_reverse($progressData)); ?>;

    const labels = progressData.map(item => item.date);
    const weightData = progressData.map(item => item.weight);
    const bmiData = progressData.map(item => item.bmi);
    const caloriesData = progressData.map(item => item.calories_burned);

    const ctx = document.getElementById('progressChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Weight (kg)',
                    data: weightData,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    fill: false
                },
                {
                    label: 'BMI',
                    data: bmiData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    fill: false
                },
                {
                    label: 'Calories Burned',
                    data: caloriesData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>
