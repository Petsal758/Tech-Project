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

// Handle Add Diet POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["diet_name"])) {
    $diet_name = trim($_POST["diet_name"]);
    $description = trim($_POST["description"]);
    $goals = trim($_POST["goal"]);

    if (!empty($diet_name)) {
        $stmt = $conn->prepare("INSERT INTO diets (diet_name, description, goals, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $diet_name, $description, $goals, $user_id);
        if ($stmt->execute()) {
            $success = "Diet plan added successfully!";
        } else {
            $error = "Error adding diet: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Please enter a diet name.";
    }
}

// Handle Add Food to Diet POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["diet_id"]) && isset($_POST["food_id"])) {
    $diet_id = $_POST["diet_id"];
    $food_id = $_POST["food_id"];
    $time = trim($_POST["time"]);

    if ($diet_id && $food_id && $time) {
        $stmt = $conn->prepare("INSERT INTO diet_meals (diet_id, food_id, time) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $diet_id, $food_id, $time);
        if ($stmt->execute()) {
            $success = "Food added to diet successfully!";
        } else {
            $error = "Failed to add food: " . $stmt->error;
        }
        $stmt->close();
    } elseif (empty($diet_id) || empty($food_id)) {
        $error = "Please select both a diet and a food.";
    }
}

// Fetch latest diet and food options
$diets = $conn->query("SELECT id, diet_name FROM diets WHERE user_id = $user_id");
$foods = $conn->query("SELECT id, food_name FROM foods");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Diet Plans | PowerPulse</title>
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
            max-width: 800px;
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
            margin-bottom: 2rem;
        }

        input, textarea, select {
            padding: 12px;
            border-radius: 8px;
            border: none;
            background-color: #2c2c2c;
            color: white;
            font-size: 1rem;
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

        .section {
            margin-bottom: 40px;
        }

        .back-button {
            margin-top: 1rem;
            padding: 10px 16px;
            background-color: var(--primary);
            color: #000;
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
    <h2>Manage Diet Plans</h2>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="section">
        <h3>Add New Diet Plan</h3>
        <form method="POST">
            <input type="text" name="diet_name" placeholder="Diet name" required>
            <textarea name="description" placeholder="Optional description..." rows="3"></textarea>
            <select name="goal">
                <option value="">-- Select Goal (Optional) --</option>
                <option value="Weight Loss">Weight Loss</option>
                <option value="Muscle Gain">Muscle Gain</option>
                <option value="Maintenance">Maintenance</option>
            </select>
            <button type="submit">Add Diet Plan</button>
        </form>
    </div>

    <div class="section">
        <h3>Add Food to Diet Plan</h3>
        <form method="POST">
            <select name="diet_id" required>
                <option value="">-- Select Diet Plan --</option>
                <?php if ($diets): while ($row = $diets->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['diet_name']) ?></option>
                <?php endwhile; endif; ?>
            </select>

            <select name="food_id" required>
                <option value="">-- Select Food --</option>
                <?php if ($foods): while ($row = $foods->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['food_name']) ?></option>
                <?php endwhile; endif; ?>
            </select>

            <input type="text" name="time" placeholder="Time (e.g., Breakfast, Lunch)" required>
            <button type="submit">Add Food</button>
        </form>
    </div>

    <button class="back-button"><a href="view_diets.php">⬅ Back</a></button>
</div>
</body>
</html>
