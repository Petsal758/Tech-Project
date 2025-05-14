<?php 
session_start();

// Database credentials
$db_server = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "powerpulse";

// Initialize variables
$email = "";
$password = "";
$error_message = "";

// Establish database connection
$conn = mysqli_connect($db_server, $db_username, $db_password, $db_name);

// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate input
    if (empty($email) || empty($password)) {
        $error_message = "Both email and password are required!";
    } else {
        // Fetch user from database
        $sql = "SELECT id, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // Fetch user data
            $user = $result->fetch_assoc();
            $hashed_password = $user['password'];

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Start session and redirect to dashboard
                session_start();
                $_SESSION['user_id'] = $user['id'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error_message = "Invalid password. Please try again.";
            }
        } else {
            $error_message = "No account found with this email.";
        }

        $stmt->close();
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            max-width: 400px;
            margin: 2rem auto;
            background-color: #1a1a1a;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(255, 165, 0, 0.5);
        }
        .container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: orange;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #ddd;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #555;
            border-radius: 5px;
            background-color: #222;
            color: white;
        }
        input:focus {
            outline: none;
            border-color: orange;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: orange;
            color: black;
            border: none;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 1rem;
        }
        button:hover {
            background-color: #ff9900;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
            color: #ccc;
        }
        .back-link a {
            color: orange;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <img src="Tech project Logo.png" alt="Tech Project Logo" height="60">
        <h1>Login to Your Account</h1>
    </header>
    <div class="container">
        <h2>Log In</h2>
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <div>
                <p style="text-align: center;">
                    <a href="Forget_password.php" style="color: orange; text-decoration: none;">Forgot Password?</a>
                </p>
            </div>
            <button type="submit">Log In</button>
        </form>
        <div class="back-link">
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>
</body>
</html>
