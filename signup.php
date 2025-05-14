<?php 
// Database connection
$host = "localhost";
$dbname = "powerpulse";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable better error reporting

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";
$calories_burned = 0;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $age = $_POST["age"];
    $gender = $_POST["gender"];
    $height = $_POST["height"];
    $weight = $_POST["weight"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $signup_date = date("Y-m-d");
    $calories_burned = 0;
    $bmi = $weight / (($height * 0.0254) ** 2); // BMI calculation

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<p style='color: red; text-align: center;'>Error: Email already registered. <a href='login.php'>Login here</a></p>";
    } else {
        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, age, gender, height, weight, email, password, signup_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissdsss", $username, $age, $gender, $height, $weight, $email, $password, $signup_date);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();

            // Insert into progress table
            // Ensure all fields in the progress table are correctly handled
            $progress_stmt = $conn->prepare("INSERT INTO progress (user_id, weight, height, calories_burned, signup_date, bmi) VALUES (?, ?, ?, ?, ?, ?)");
            $progress_stmt->bind_param("idddsd", $user_id, $weight, $height, $calories_burned, $signup_date, $bmi);

            if ($progress_stmt->execute()) {
                $progress_stmt->close();
                $conn->close();
                header("Location: login.php");
                exit();
            } else {
                echo "<p style='color:red; text-align:center;'>Error inserting into progress table: " . $progress_stmt->error . "</p>";
            }
        } else {
            $error = "Error: " . $stmt->error;
        }
    }

    $check_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Powerpulse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: white;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: orange;
            color: black;
            padding: 1rem 0;
            text-align: center;
        }
        .container {
            max-width: 500px;
            margin: 2rem auto;
            background-color: #1a1a1a;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px orange;
        }
        h2 {
            color: orange;
            text-align: center;
            margin-bottom: 1.5rem;
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
        .login-link {
            text-align: center;
            margin-top: 1rem;
            color: #ccc;
        }
        .login-link a {
            color: orange;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <img src="Tech project Logo.png" alt="Powerpulse Logo" height="60">
        <h1>Join Powerpulse</h1>
    </header>
    <div class="container">
        <h2>Create Your Account</h2>
        <?php if ($error): ?>
            <p style="color: red; text-align: center;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <input type="text" id="gender" name="gender" placeholder="e.g., Male or Female" required>
            </div>
            <div class="form-group">
                <label for="height">Height (inches)</label>
                <input type="number" step="0.1" id="height" name="height" required>
            </div>
            <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input type="number" step="0.1" id="weight" name="weight" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Log In</a></p>
        </div>
    </div>
</body>
</html>
