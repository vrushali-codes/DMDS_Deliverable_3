<?php
session_start();
include 'database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier']; // SSN, Email, or Phone
    $type = $_POST['type'];             // "ssn", "email", or "phone"

    // Initialize query variable
    $query = null;

    if ($type == "ssn") {
        $query = $conn->prepare("SELECT * FROM User WHERE SSN = ? LIMIT 1");
    } elseif ($type == "email") {
        $query = $conn->prepare("SELECT * FROM User u JOIN EmailAddress e ON u.UserID = e.EUserID WHERE e.EmailID = ? LIMIT 1");
    } elseif ($type == "phone") {
        $query = $conn->prepare("SELECT * FROM User u JOIN PhoneNo p ON u.UserID = p.Verifies WHERE p.PhoneNo = ? LIMIT 1");
    } else {
        $error = "Invalid identifier type.";
    }

    // Debugging: Check if query preparation failed
    if ($query === false) {
        $error = "Query preparation failed: " . $conn->error;
    } else {
        // Bind parameter and execute query if preparation was successful
        $query->bind_param("s", $identifier);

        if ($query->execute()) {
            $result = $query->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Set session variables
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['name'] = $user['Name'];

                // Redirect to wallet network page
                header("Location: wallet_network.php");
                exit();
            } else {
                $error = "No user found with this identifier.";
            }
        } else {
            $error = "Query failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Welcome to Wallet Payment Network</h1>
    </header>
    <form action="login.php" method="post">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
        <label for="identifier">SSN / Email / Phone:</label>
        <input type="text" name="identifier" id="identifier" required>
        <label for="type">Login Type:</label>
        <select name="type" id="type" required>
            <option value="ssn">SSN</option>
            <option value="email">Email</option>
            <option value="phone">Phone</option>
        </select>
        <button type="submit">Login</button>
    </form>
</body>
</html>
