<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include '../database/config.php';

$userID = $_SESSION['user_id']; // Get the logged-in user's ID

// Handling adding a new email
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_email'])) {
    $email = $_POST['email'];

    // Check if email already exists for the user
    $query = "SELECT * FROM EmailAddress WHERE EmailID = ? AND EUserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $email, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "This email is already associated with your account.";
    } else {
        // Insert new email into the EmailAddress table
        $query = "INSERT INTO EmailAddress (EmailID, EUserID) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $email, $userID);

        if ($stmt->execute()) {
            echo "Email added successfully.";
        } else {
            echo "Error adding email: " . $conn->error;
        }
    }

    $stmt->close();
}

// Handling removing an email
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_email'])) {
    $email = $_POST['email'];

    // Check if the email exists for the user
    $query = "SELECT * FROM EmailAddress WHERE EmailID = ? AND EUserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $email, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "This email is not associated with your account.";
    } else {
        // Delete email from the EmailAddress table
        $query = "DELETE FROM EmailAddress WHERE EmailID = ? AND EUserID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $email, $userID);

        if ($stmt->execute()) {
            echo "Email removed successfully.";
        } else {
            echo "Error removing email: " . $conn->error;
        }
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Email Address</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
</head>
<body>
    <h1>Modify Email Address</h1>

    <h2>Add New Email</h2>
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" required><br>
        <button type="submit" name="add_email">Add Email</button>
    </form>

    <h2>Remove Email</h2>
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" required><br>
        <button type="submit" name="remove_email">Remove Email</button>
    </form>

</body>
</html>
