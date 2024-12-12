<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include '../database/config.php';

$userID = $_SESSION['user_id']; // Get the logged-in user's ID

// Handling adding a new phone number
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_phone'])) {
    $phone = $_POST['phone'];

    // Insert new phone number into the PhoneNo table and associate the user ID for verification
    $query = "INSERT INTO PhoneNo (PhoneNo, Verifies) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $phone, $userID); // Bind phone number and user ID for verification

    if ($stmt->execute()) {
        // Update the User table to set the UserPhoneNo to the new phone number
        $updateUserQuery = "UPDATE User SET UserPhoneNo = ? WHERE UserID = ?";
        $updateStmt = $conn->prepare($updateUserQuery);
        $updateStmt->bind_param("si", $phone, $userID);

        if ($updateStmt->execute()) {
            echo "Phone number added successfully and user details updated, marked as verified.";
        } else {
            echo "Error updating user details: " . $conn->error;
        }
        $updateStmt->close();
    } else {
        echo "Error adding phone number: " . $conn->error;
    }

    $stmt->close();
}

// Handling removing a phone number
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_phone'])) {
    $phone = $_POST['phone'];

    // Check if the phone number exists for the current user
    $query = "SELECT * FROM User WHERE UserPhoneNo = ? AND UserID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $phone, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "This phone number is not associated with your account.";
    } else {
        // Delete phone number from the PhoneNo table
        $query = "DELETE FROM PhoneNo WHERE PhoneNo = ? AND Verifies = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $phone, $userID);

        if ($stmt->execute()) {
            // Update the User table to set the UserPhoneNo to NULL
            $updateUserQuery = "UPDATE User SET UserPhoneNo = NULL WHERE UserID = ?";
            $updateStmt = $conn->prepare($updateUserQuery);
            $updateStmt->bind_param("i", $userID);

            if ($updateStmt->execute()) {
                echo "Phone number removed successfully and user details updated.";
            } else {
                echo "Error updating user details: " . $conn->error;
            }
            $updateStmt->close();
        } else {
            echo "Error removing phone number: " . $conn->error;
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
    <title>Modify Phone Number</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Modify Phone Number</h1>

    <h2>Add New Phone Number</h2>
    <form method="POST">
        <label for="phone">Phone Number:</label>
        <input type="tel" name="phone" required><br>
        <button type="submit" name="add_phone">Add Phone</button>
    </form>

    <h2>Remove Phone Number</h2>
    <form method="POST">
        <label for="phone">Phone Number:</label>
        <input type="tel" name="phone" required><br>
        <button type="submit" name="remove_phone">Remove Phone</button>
    </form>

</body>
</html>
