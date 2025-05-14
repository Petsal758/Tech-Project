<?php
// Include database connection
$db_server = "localhost";
    $db_username = "root";  
    $db_password = "";
    $db_name = "powerpulse";
    $conn = "";

    // Establish database connection
    $conn = mysqli_connect($db_server, $db_username, $db_password, $db_name);
    
    if(!$conn){
        echo "Connection failed: " . $e->getMessage();
    }
    
    if ($conn) {
        echo "Connected successfully";
    }

// Initialize variables
$workouts = []; 

$sql = "SELECT workout_name, workout_description, type FROM workouts";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch all workouts
    while ($row = $result->fetch_assoc()) {
        $workouts[] = $row;
    }
} else {
    echo "No workouts found.";
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workouts</title>
</head>
<body>
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
    <h1>Workouts</h1>
    <ul>
        <?php foreach ($workouts as $workout): ?>
            <li>
                <h3><?php echo htmlspecialchars($workout['workout_name']); ?></h3>
                <p>Type: <?php echo htmlspecialchars($workout['workout_type']); ?></p>
                <p><?php echo htmlspecialchars($workout['workout_description']); ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>