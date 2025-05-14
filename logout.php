<?php
session_start(); // Start or resume session

// Optional: Establish database connection if needed (not used in this case)
$db_server   = "localhost";
$db_username = "root";
$db_password = "";
$db_name     = "powerpulse";

$conn = mysqli_connect($db_server, $db_username, $db_password, $db_name);
if (!$conn) {
    echo "Connection failed: " . mysqli_connect_error();
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle logout POST request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['logout'])) {
    // Clear all session variables
    $_SESSION = [];

    // Destroy session cookie (important for complete logout)
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    session_unset();
    session_destroy();

    // Redirect to login page
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #121212;
            color: #FFA500;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .container {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(255, 165, 0, 0.6);
        }
        button {
            padding: 12px 24px;
            background-color: #FFA500;
            color: #121212;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 10px;
        }
        button:hover {
            background-color: #ff8c00;
            color: white;
        }
        .back-button {
            background-color: #ff6600;
            color: black;
        }
        .back-button:hover {
            background-color: #ff8533;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Are you sure you want to log out?</h1>
        <form method="POST">
            <button type="submit" name="logout">Yes, Log Me Out</button>
        </form>
        <button onclick="history.back()" class="back-button">⬅ Cancel</button>
    </div>
</body>
</html>
