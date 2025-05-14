<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$conn = new mysqli("localhost", "root", "", "powerpulse");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$query = "SELECT weight, bmi, calories_burned, update_date FROM progress WHERE user_id = ? ORDER BY update_date DESC LIMIT 10";
$stmt = $conn->prepare($query);
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
    <title>Powerpulse Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0a0a0a;
            color: #fff;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100vh;
            background-color: #000;
            border-right: 3px solid orange;
            padding: 30px 20px;
        }
        .sidebar h2 {
            color: orange;
            text-align: center;
            margin-bottom: 2rem;
        }
        .sidebar a {
            display: block;
            color: orange;
            text-decoration: none;
            padding: 12px 0;
            font-size: 1rem;
            transition: 0.3s;
        }
        .sidebar a:hover {
            color: #fff;
            padding-left: 10px;
        }
        .main-content {
            margin-left: 240px;
            padding: 30px;
        }
        .header {
            background-color: orange;
            padding: 20px;
            text-align: center;
            color: #000;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .card, .schedule, .goals {
            background-color: #1a1a1a;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            border-left: 5px solid orange;
            box-shadow: 0 4px 10px rgba(255, 165, 0, 0.2);
        }
        .schedule, .goals {
            background-color: orange;
            color: #000;
        }
        .schedule a {
            color: #000;
            text-decoration: underline;
        }
        .update-button {
            background-color: orange;
            color: #000;
            padding: 12px 20px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: block;
            width: 220px;
            margin: 20px auto 0;
            transition: background-color 0.3s;
            text-align: center;
            text-decoration: none;
        }
        .update-button:hover {
            background-color: #ff8c00;
        }
        footer {
            text-align: center;
            padding: 20px;
            background-color: #000;
            color: #fff;
            margin-top: 50px;
        }
        canvas {
            background-color: #fff;
            border-radius: 8px;
            margin-top: 20px;
        }
        a {
            color: orange;
            text-decoration: none;
        }
        h2 {
            color: orange;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Powerpulse</h2>
        <a href="profile.php">Profile</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="workouts.html">Workouts</a>
        <a href="get_diet_plans.php">Diet Plans</a>
        <a href="update_progress.php">Update Progress</a>
        <a href="logout.php">Log Out</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Track Your Daily Activities</h1>
        </div>

        <div class="card">
            <h2><a href="view_schedule.php">My Schedule</a></h2>
            <p>Keep track of your workout schedules.</p>
        </div>

        <div class="card">
            <h2><a href="view_diets.php">My Diet</a></h2>
            <p>Pay attention to your eating habits while working out.</p>
        </div>

        <div class="card">
            <h2>Progress</h2>
            <canvas id="progressChart" width="400" height="200"></canvas>
            <a href="update_progress.php" class="update-button">Update Progress</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Powerpulse Gym. All rights reserved.</p>
    </footer>

    <script>
        const progressData = JSON.parse('<?= json_encode($progressData) ?>');

        const labels = progressData.map(item => item.update_date).reverse();
        const weightData = progressData.map(item => item.weight).reverse();
        const bmiData = progressData.map(item => item.bmi).reverse();
        const caloriesData = progressData.map(item => item.calories_burned).reverse();

        const ctx = document.getElementById('progressChart').getContext('2d');

        // ✅ Prevent multiple charts from stacking
        if (window.progressChartInstance) {
            window.progressChartInstance.destroy();
        }

        window.progressChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Weight (kg)',
                        data: weightData,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'BMI',
                        data: bmiData,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Calories Burned',
                        data: caloriesData,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'black'
                        }
                    },
                    x: {
                        ticks: {
                            color: 'white'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'black',
                        }
                    },
                    tooltip: {
                        bodyColor: 'white',
                        titleColor: 'black',
                        backgroundColor: '#222'
                    }
                }
            }
        });
    </script>
</body>
</html>
