<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "powerpulse");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = $error = "";

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["diet_id"])) {
    $diet_id = $_POST["diet_id"];

    // First, delete from diet_meals to avoid foreign key constraint errors
    $conn->query("DELETE FROM diet_meals WHERE diet_id = $diet_id");

    // Then delete the diet plan
    $stmt = $conn->prepare("DELETE FROM diets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $diet_id, $user_id);
    if ($stmt->execute()) {
        $success = "Diet plan deleted successfully.";
    } else {
        $error = "Failed to delete diet plan.";
    }
    $stmt->close();
}

// Fetch user diets
$diets = $conn->query("SELECT id, diet_name FROM diets WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Diet Plan | PowerPulse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #ff6600;
            --background: #121212;
            --card-bg: #1e1e1e;
            --text-color: #fff;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--background);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(255, 102, 0, 0.2);
        }

        h2 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        select {
            padding: 12px;
            border-radius: 8px;
            background-color: #2c2c2c;
            color: white;
            font-size: 1rem;
            border: none;
        }

        button {
            padding: 12px 18px;
            border: 2px solid var(--primary);
            border-radius: 8px;
            background-color: transparent;
            color: var(--primary);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: var(--primary);
            color: black;
        }

        .message {
            text-align: center;
            font-weight: bold;
        }

        .success {
            color: #00cc66;
        }

        .error {
            color: #ff3333;
        }

        .back-button {
            margin-top: 2rem;
            padding: 12px 18px;
            background-color: var(--primary);
            color: black;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .back-button:hover {
            background-color: #ff8533;
            transform: scale(1.05);
        }

        a {
            text-decoration: none;
            color: inherit;
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
<div class="container">
    <h2>Delete a Diet Plan</h2>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <select name="diet_id" required>
            <option value="">-- Select a Diet to Delete --</option>
            <?php while ($row = $diets->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['diet_name']) ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" onclick="return confirm('Are you sure you want to delete this diet?')">Delete Diet</button>
    </form>

    <button class="back-button"><a href="view_diets.php">⬅ Back</a></button>
</div>
</body>
</html>
