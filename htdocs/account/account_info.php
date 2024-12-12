<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include '../database/config.php'; // Include the database configuration

// Retrieve the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch account info for the specific user
$sql = "
    SELECT 
        u.Name, 
        u.SSN, 
        u.UserID, 
        u.UserPhoneNo, 
        e.EmailID, 
        u.UserAccountID, 
        w.WalletID, 
        w.Balance 
    FROM `User` u
    LEFT JOIN `EmailAddress` e ON u.UserID = e.EUserID
    LEFT JOIN `Wallet` w ON u.UserWalletID = w.WalletID
    WHERE u.UserID = ?
";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $user_id); // Bind the user ID to the query
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<h2>User Details</h2>";
            echo "<p><strong>SSN:</strong> " . htmlspecialchars($row['SSN']) . "</p>";
            echo "<p><strong>User ID:</strong> " . htmlspecialchars($row['UserID']) . "</p>";
            echo "<p><strong>Name:</strong> " . htmlspecialchars($row['Name']) . "</p>";
            echo "<p><strong>Phone Number:</strong> " . htmlspecialchars($row['UserPhoneNo']) . "</p>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($row['EmailID']) . "</p>";
            echo "<p><strong>User Account ID:</strong> " . htmlspecialchars($row['UserAccountID']) . "</p>";
            echo "<p><strong>Wallet ID:</strong> " . htmlspecialchars($row['WalletID']) . "</p>";
            echo "<p><strong>Balance:</strong> $" . number_format($row['Balance'], 2) . "</p>";
        }
    } else {
        echo "<p>No account info found.</p>";
    }

    $stmt->close(); // Close the statement
} else {
    echo "<p>Error preparing the statement: " . $conn->error . "</p>";
}

$conn->close(); // Close the database connection
?>
