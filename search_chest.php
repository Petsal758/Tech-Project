<?php
$conn = mysqli_connect("localhost", "root", "", "powerpulse");

if (!$conn) {
    die("Database connection error.");
}

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chest Workouts</title>
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

        .workout-card p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Chest Workouts</h1>
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
            <form method="GET" action="search_chest.php">
                <input type="text" name="query" placeholder="Search for chest workouts..." required>
                <button type="submit">Search</button>
            </form>
        </div>

        <div id="workout-results">
            <?php
            if ($query !== '') {
                $query = htmlspecialchars($query);
                $sql = "SELECT workout_name, workout_description, workout_type FROM workouts 
                        WHERE workout_type LIKE ? AND workout_name LIKE ?";
                $workoutType = '%chest%';
                $searchTerm = '%' . $query . '%';

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $workoutType, $searchTerm);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='workout-card'>";
                        echo "<h3>" . htmlspecialchars($row['workout_name']) . "</h3>";
                        echo "<p><strong>Type:</strong> " . htmlspecialchars($row['workout_type']) . "</p>";
                        echo "<p>" . htmlspecialchars($row['workout_description']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No workouts found matching your search query.</p>";
                }

                $stmt->close();
                $conn->close();
            }
            ?>
        </div>
    </div>
</body>
</html>
