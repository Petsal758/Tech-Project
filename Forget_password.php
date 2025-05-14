<?php
$db = new mysqli("localhost", "root", "", "powerpulse");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32));

    $stmt = $db->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
    $stmt->bind_param("ss", $token, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
    $resetLink = "http://localhost/Technical-Project/reset_password.php?token=$token";
    header("Location: $resetLink");
    exit(); // Always call exit after header redirect
} else {
    echo "<p>Email not found.</p>";
}
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #1a1a1a;
            color: white;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #ff7f00;
            color: black;
            padding: 1.5rem;
            text-align: center;
            font-size: 1.8rem;
        }
        .container {
            max-width: 400px;
            margin: 4rem auto;
            background-color: #ff7f00;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(255, 165, 0, 0.5);
            color: black;
        }
        h2 {
            text-align: center;
            margin-bottom: 2rem;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 0.5rem;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ff9f00;
            border-radius: 6px;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #1a1a1a;
            color: #ff7f00;
            border: 2px solid #ff7f00;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 1rem;
        }
        button:hover {
            background-color: #ff7f00;
            color: black;
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
              .back-button a {
    color: inherit;
    text-decoration: none;
    user-select: none;
}

.back-button a:focus,
.back-button a:active {
    outline: none;
    background: none;
}
    </style>
</head>
<body>
    <header>Forgot Password</header>
    <div class="container">
        <button class="back-button"><a href="login.php">⬅ Back</a></button>
        <h2>Reset Your Password</h2>
        <form action="" method="POST">
            <label for="email">Enter Your Email</label>
            <input type="email" name="email" id="email" required>
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>
</html>
