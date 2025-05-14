<?php
$db = new mysqli("localhost", "root", "", "powerpulse");

// 🔧 Fix: Avoid "undefined variable" warning
$resetSuccess = null;
$resetError = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $newPassword, $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Password reset successful, redirect to login page
        header("Location: login.php"); // Replace 'login.php' with your actual login page URL
        exit(); // Make sure to call exit() after header to stop further script execution
    } else {
        $resetError = "Invalid or expired token.";
    }

    $stmt->close();
    $db->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            background-color: #121212;
            color: #FFA500;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 2rem;
            font-weight: 600;
        }

        form {
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(255, 165, 0, 0.6);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: none;
            border-radius: 8px;
            background-color: #2c2c2c;
            color: #FFA500;
            font-size: 1rem;
        }

        input[type="password"]::placeholder {
            color: #ffbb50;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #FFA500;
            color: #121212;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1rem;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #ff8c00;
        }

        .message {
            color: #ffbb50;
            margin-top: 15px;
            font-size: 1rem;
        }

        .success {
            color: #00cc66;
        }

        .error {
            color: #ff4c4c;
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
            position: absolute;
            top: 20px;
            left: 20px;
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

<button class="back-button"><a href="Forget_password.php">⬅ Back</a></button>

<h2>Enter a New Password</h2>
<form action="" method="POST">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
    <input type="password" name="new_password" placeholder="New Password" required>
    <button type="submit">Reset Password</button>
</form>

<?php if ($resetError): ?>
    <p class="message error"><?= htmlspecialchars($resetError) ?></p>
<?php endif; ?>

</body>
</html>
