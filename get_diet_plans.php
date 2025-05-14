<?php
// Connect to the database
$conn = mysqli_connect("localhost", "root", "", "powerpulse");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all diets from the database
$sql = "SELECT * FROM diet";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diet Plans</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: #FFA500;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 220px;
            background-color: #1e1e1e;
            color: #FFA500;
            padding: 15px;
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            border-right: 2px solid #FFA500;
        }

        .sidebar h2 {
            text-align: center;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
        }

        .sidebar a {
            color: #FFA500;
            text-decoration: none;
            font-size: 1rem;
            padding: 8px 0;
            text-align: center;
            transition: background-color 0.3s;
            margin-bottom: 10px;
        }

        .sidebar a:hover {
            background-color: #FFA500;
            color: #121212;
            border-radius: 5px;
        }

        .main-content {
            margin-left: 220px;
            padding: 2rem;
            width: 100%;
            overflow-y: auto;
        }

        header {
            background-color: #FFA500;
            color: black;
            text-align: center;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 10px;
        }

        header img {
            width: 150px;
            margin-bottom: 10px;
        }

        header h1 {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }

        .diet-card {
            background-color: #1e1e1e;
            color: #FFA500;
            margin-bottom: 1rem;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(255, 165, 0, 0.5);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .diet-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 20px rgba(255, 165, 0, 0.7);
        }

        .diet-card h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .diet-card p {
            font-size: 1rem;
            margin: 0.3rem 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 180px;
            }

            .main-content {
                margin-left: 180px;
            }

            header h1 {
                font-size: 1.5rem;
            }

            .diet-card h3 {
                font-size: 1.2rem;
            }

            .diet-card p {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                padding: 15px;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            header h1 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Powerpulse</h2>
        <a href="profile.php">Profile</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="workouts.html">Workouts</a>
        <a href="get_diet_plans.php">Diet Plans</a>
        <a href="update_progress.php">Update Progress</a>
        <a href="logout.php">Log Out</a>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <header>
            <img src="Tech project Logo.png" alt="Tech Project Logo">
            <h1>Diet Plans</h1>
        </header>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="diet-card">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p><strong>Description:</strong> <?= htmlspecialchars($row['diet_description']) ?></p>
                <p><strong>Goals:</strong> <?= htmlspecialchars($row['goal']) ?></p>
            </div>
        <?php endwhile; ?>
        <?php mysqli_close($conn); ?>
    </div>
</body>
</html>
