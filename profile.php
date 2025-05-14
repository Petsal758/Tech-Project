<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$db_server   = "localhost";
$db_username = "root";
$db_password = "";
$db_name     = "powerpulse";

$conn = new mysqli($db_server, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user info
$user_stmt = $conn->prepare("SELECT username, gender, height, weight, age FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_stmt->store_result();

if ($user_stmt->num_rows > 0) {
    $user_stmt->bind_result($username, $gender, $height, $weight, $age);
    $user_stmt->fetch();
} else {
    echo "User not found.";
    exit();
}
$user_stmt->close();

// Get progress info and track total calories burned
// We use SUM() to add together all calories_burned values for this user.
$progress_stmt = $conn->prepare("SELECT SUM(calories_burned) AS total_calories, AVG(bmi) AS avg_bmi FROM progress WHERE user_id = ?");
$progress_stmt->bind_param("i", $user_id);
$progress_stmt->execute();
$progress_stmt->bind_result($total_calories_burned, $avg_bmi);
$progress_stmt->fetch();
$progress_stmt->close();

// In case there are no progress entries, set total_calories_burned to zero.
if ($total_calories_burned === null) {
    $total_calories_burned = 0;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PowerPulse | User Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #000000, #1a1a1a);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .card {
            background-color: #ffffff;
            color: #1a1a1a;
            border-radius: 16px;
            padding: 2rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 8px 30px rgba(255, 165, 0, 0.3);
            animation: fadeIn 0.7s ease-in-out;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        .card h1 {
            margin-top: 0;
            font-size: 2rem;
            color: #ff6600;
            text-align: center;
        }
        .profile-detail {
            margin: 1rem 0;
            padding: 0.5rem 1rem;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .profile-detail strong {
            display: inline-block;
            width: 160px;
            color: #333;
        }
        .logout {
            display: block;
            margin-top: 2rem;
            text-align: center;
            background-color: #ff6600;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .logout:hover {
            background-color: #cc5200;
        }
        @media (max-width: 600px) {
            .card {
                padding: 1.5rem;
                margin: 1rem;
            }
            .profile-detail strong {
                width: 120px;
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
            margin-bottom: 1rem;
        }
        .back-button:hover {
            background-color: #ff8533;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

        <div class="profile-detail">
            <strong>Gender:</strong> <?php echo htmlspecialchars($gender); ?>
        </div>
        <div class="profile-detail">
            <strong>Height:</strong> <?php echo htmlspecialchars($height); ?> cm
        </div>
        <div class="profile-detail">
            <strong>Weight:</strong> <?php echo htmlspecialchars($weight); ?> kg
        </div>
        <div class="profile-detail">
            <strong>Age:</strong> <?php echo htmlspecialchars($age); ?> years
        </div>
        <div class="profile-detail">
            <strong>Total Calories Burned:</strong> <?php echo htmlspecialchars($total_calories_burned); ?> kcal
        </div>
        <div class="profile-detail">
            <strong>BMI:</strong> <?php echo htmlspecialchars(round($avg_bmi, 2)); ?>
        </div>
        
        <button onclick="history.back()" class="back-button">⬅ Back</button>
        <a href="logout.php" class="logout">Log Out</a>
    </div>
</body>
</html>