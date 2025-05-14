<?php
$conn = mysqli_connect("localhost", "root", "", "powerpulse");
if (!$conn) die("Connection failed");

$query = $_GET['query'] ?? '';
$query = htmlspecialchars(trim($query));

$sql = "SELECT workout_name, workout_type, workout_description FROM workouts WHERE workout_type = 'Core' AND workout_name LIKE ?";
$searchTerm = '%' . $query . '%';
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Core Workouts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: black;
            color: white;
        }
        header {
            background-color: orange;
            color: black;
            padding: 1rem 0;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .search-bar input {
            width: 70%;
            padding: 10px;
            border: 2px solid orange;
            border-radius: 5px 0 0 5px;
            outline: none;
        }
        .search-bar button {
            padding: 10px;
            background-color: orange;
            color: black;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            font-weight: bold;
        }
        .search-bar button:hover {
            background-color: black;
            color: orange;
            border: 2px solid orange;
            transition: 0.3s;
        }
        .workout-card {
            background-color: orange;
            color: black;
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(255, 165, 0, 0.5);
        }
        .workout-card h3 {
            margin: 0 0 10px 0;
        }
    </style>
</head>
<body>
<header>
    <h1>Core Workouts</h1>
</header>
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
</style>

<button onclick="history.back()" class="back-button">⬅ Back</button>
    <div class="search-bar">
        <form method="GET" action="">
            <input type="text" name="query" placeholder="Search for core workouts..." value="<?= htmlspecialchars($query) ?>">
            <button type="submit">Search</button>
        </form>
    </div>
    <div id="workout-results">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='workout-card'>";
                echo "<h3>" . htmlspecialchars($row['workout_name']) . "</h3>";
                echo "<p><strong>Type:</strong> " . htmlspecialchars($row['workout_type']) . "</p>";
                echo "<p>" . htmlspecialchars($row['workout_description']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No workouts found.</p>";
        }
        ?>
    </div>
</div>
</body>
</html>
