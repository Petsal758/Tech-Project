<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

$host = "localhost";
$dbname = "powerpulse";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$diet_id = $_GET['diet_id'] ?? null;
$diets = $conn->query("SELECT * FROM diets WHERE user_id = $user_id");

$foods = null;
$total_calories = 0;
if ($diet_id) {
    $stmt = $conn->prepare("SELECT f.*, df.time
        FROM diet_meals df
        JOIN foods f ON df.food_id = f.id
        WHERE df.diet_id = ?
    ");
    $stmt->bind_param("i", $diet_id);
    $stmt->execute();
    $foods = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Diet Plans | PowerPulse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #ff6600;
            --background: #121212;
            --card-bg: #1e1e1e;
            --text-color: #fff;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--background);
            color: var(--text-color);
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(255, 102, 0, 0.2);
        }
        h2, h3 {
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
            border: none;
            background-color: #2c2c2c;
            color: white;
            font-size: 1rem;
        }
        .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        button, .button-link {
            padding: 12px 18px;
            border: 2px solid var(--primary);
            border-radius: 8px;
            background-color: transparent;
            color: var(--primary);
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        button:hover, .button-link:hover {
            background-color: var(--primary);
            color: black;
        }
        .food {
            background-color: #2a2a2a;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: transform 0.2s ease;
        }
        .food:hover {
            transform: scale(1.01);
        }
        .food strong {
            font-size: 1.2rem;
            color: var(--primary);
        }
        .total {
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 20px;
            color: var(--primary);
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
        @media (max-width: 600px) {
            .buttons {
                flex-direction: column;
            }
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
    <button class="back-button"><a href="dashboard.php">⬅ Back</a></button>
    <h2>Select a Diet Plan</h2>
    <form method="GET">
        <select name="diet_id" required>
            <option value="">-- Choose a Diet --</option>
            <?php while ($row = $diets->fetch_assoc()): ?>
                <option value="<?= $row['diet_id'] ?>" <?= $diet_id == $row['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['diet_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <div class="buttons">
            <button type="submit">View Diet</button>
            <a href="add_diet.php" class="button-link">Add Diet</a>
            <a href="delete_diet.php" class="button-link">Delete Diet</a>
        </div>
    </form>

    <?php if ($foods): ?>
        <h3>Diet Details</h3>
        <?php while($f = $foods->fetch_assoc()): 
            $total_calories += $f['calories'];
        ?>
            <div class="food">
                <strong><?= htmlspecialchars($f['food_name']) ?></strong> (<?= htmlspecialchars($f['food_type']) ?>)<br>
                Serving Size: <?= $f['serving_size'] ?><br>
                Protein: <?= $f['protein_grams'] ?>g |
                Carbs: <?= $f['carbs_grams'] ?>g |
                Fat: <?= $f['fat_grams'] ?>g |
                Fiber: <?= $f['fiber_grams'] ?>g<br>
                Vitamin A: <?= $f['vitamin_a_mcg'] ?>µg |
                Vitamin C: <?= $f['vitamin_c_mg'] ?>mg<br>
                Calcium: <?= $f['calcium_mg'] ?>mg |
                Iron: <?= $f['iron_mg'] ?>mg<br>
                Calories: <?= $f['calories'] ?> kcal |
                Time: <?= htmlspecialchars($f['time']) ?>
            </div>
        <?php endwhile; ?>
        <div class="total">Total Calories: <?= $total_calories ?> kcal</div>
    <?php endif; ?>
</div>
</body>
</html>
