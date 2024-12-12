<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include '../database/config.php';

$userID = $_SESSION['user_id']; // Get the logged-in user's ID

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action']; // 'add_account' or 'remove_account'

    // Add Bank Account
    if ($action == 'add_account') {
        $accountNumber = $_POST['accountNumber'];
        $bankID = $_POST['bankID']; // Assuming the bank ID is provided

        // Insert bank account into BankAccount table
        $query = "INSERT INTO BankAccount (AccountNumber, BankID) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $accountNumber, $bankID);

        if ($stmt->execute()) {
            // Get the AccountID of the newly inserted account
            $accountID = $stmt->insert_id;

            // Insert into Wallet
            $balance = 1000;  // Assign value to a variable
            $updateWalletQuery = "INSERT INTO Wallet (Balance) VALUES (?)";  // Add semicolon
            $updateWalletStmt = $conn->prepare($updateWalletQuery);
            $updateWalletStmt->bind_param("i", $balance);  // Bind the variable to the query
            $updateWalletStmt->execute();
            $walletID = $updateWalletStmt->insert_id;

            // Update the User table to associate the AccountID and WalletID
            $updateQuery = "UPDATE User SET UserAccountID = ?, UserWalletID = ? WHERE UserID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("iii", $accountID, $walletID, $userID);  // Bind parameters correctly


            if ($updateStmt->execute()) {
                echo "Bank account added and associated successfully.";
            } else {
                echo "Error associating account ID with the user: " . $conn->error;
            }

            $updateStmt->close();
        } else {
            echo "Error adding bank account: " . $conn->error;
        }

        $stmt->close();
    }
    // Remove Bank Account
    elseif ($action == 'remove_account') {
        $accountNumber = $_POST['accountNumber'];

        // Get AccountID based on AccountNumber to disassociate from User table
        $query = "SELECT AccountID FROM BankAccount WHERE AccountNumber = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $accountNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $accountID = $row['AccountID'];

                // Remove the bank account from BankAccount table
                $deleteQuery = "DELETE FROM BankAccount WHERE AccountID = ?";
                $deleteStmt = $conn->prepare($deleteQuery);
                $deleteStmt->bind_param("i", $accountID);

                $deleteWalletQuery = "DELETE FROM Wallet WHERE WalletID = (SELECT UserWalletID FROM User WHERE UserID = ?)";
                $deleteWalletStmt = $conn->prepare($deleteWalletQuery);
                $deleteWalletStmt->bind_param("i", $userID);


                if ($deleteStmt->execute() && $deleteWalletStmt->execute()) {
                    // Set the AccountID to NULL in the User table for the corresponding user
                    $updateQuery = "UPDATE User SET UserAccountID = NULL, UserWalletID = NULL WHERE UserID = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("i", $userID);

                    if ($updateStmt->execute()) {
                        echo "Bank account removed successfully and disassociated from the user.";
                    }

                    $updateStmt->close();

                } else {
                    echo "Error removing bank account: " . $conn->error;
                }

                $deleteStmt->close();
            } else {
                echo "Error disassociating account ID from the user: " . $conn->error;
            }

        } else {
            echo "Bank account not found.";
        }

    }

    $conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Modify Account</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Modify Account Details</h1>
    
    <!-- Add Bank Account Form -->
    <h2>Add Bank Account</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add_account">

        <label for="accountNumber">Account Number:</label>
        <input type="text" name="accountNumber" required><br>

        <label for="bankID">Bank ID:</label>
        <input type="number" name="bankID" required><br>

        <button type="submit">Add Account</button>
    </form>

    <!-- Remove Bank Account Form -->
    <h2>Remove Bank Account</h2>
    <form method="POST">
        <input type="hidden" name="action" value="remove_account">

        <label for="accountNumber">Account Number:</label>
        <input type="text" name="accountNumber" required><br>

        <button type="submit">Remove Account</button>
    </form>
</body>
</html>
