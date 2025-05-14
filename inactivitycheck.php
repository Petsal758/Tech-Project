<?php
$host = "localhost";
$dbname = "powerpulse";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Select users inactive for over 7 days and not yet notified
$sql = "
    SELECT id, username, email, last_login
    FROM users
    WHERE last_login < NOW() - INTERVAL 7 DAY
    AND notified_inactive = FALSE
";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $user_id = $row['id'];
    $username = $row['username'];
    $email = $row['email'];

    // Email subject and message
    $subject = "We miss you at PowerPulse!";
    $message = "
        Hi $username,\n\n
        It looks like you haven't logged in to PowerPulse for over a week.\n
        Staying consistent is key to your fitness journey – log in today to track your progress and stay on top of your goals!\n\n
        Log in here: http://localhost/Technical-Project/login.php\n\n
        Stay strong,\n
        The PowerPulse Team
    ";
    $headers = "From: no-reply@powerpulse.com\r\n";

    // Send email
    if (mail($email, $subject, $message, $headers)) {
        echo "Notification sent to $email<br>";

        // Update the notified flag
        $update = $conn->prepare("UPDATE users SET notified_inactive = TRUE WHERE id = ?");
        $update->bind_param("i", $user_id);
        $update->execute();
        $update->close();
    } else {
        echo "Failed to send email to $email<br>";
    }
}

$conn->close();
?>
