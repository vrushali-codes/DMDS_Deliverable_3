<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include '../database/config.php';

$userID = $_SESSION['user_id']; // Get logged-in user's ID from session

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient = $_POST['recipient'];
    $amount = $_POST['amount'];
    $memo = $_POST['memo'];

    // Get recipient details (UserID and UserWalletID), either from PhoneNo or EmailID
    $recipientQuery = $conn->prepare("SELECT u.UserID, u.UserWalletID 
                                      FROM User u 
                                      LEFT JOIN EmailAddress e ON u.UserID = e.EUserID 
                                      WHERE u.UserPhoneNo = ? OR e.EmailID = ?");
    $recipientQuery->bind_param("ss", $recipient, $recipient);
    $recipientQuery->execute();
    $recipientResult = $recipientQuery->get_result();

    if ($recipientResult->num_rows > 0) {
        $recipientData = $recipientResult->fetch_assoc();
        $recipientID = $recipientData['UserID'];
        $recipientWalletID = $recipientData['UserWalletID'];

        // Get the sender's wallet ID
        $senderQuery = $conn->prepare("SELECT UserWalletID FROM User WHERE UserID = ?");
        $senderQuery->bind_param("i", $userID);
        $senderQuery->execute();
        $senderResult = $senderQuery->get_result();
        $senderData = $senderResult->fetch_assoc();
        $senderWalletID = $senderData['UserWalletID'];

        // Check if sender has enough balance
        $walletQuery = $conn->prepare("SELECT Balance FROM Wallet WHERE WalletID = ?");
        $walletQuery->bind_param("i", $senderWalletID);
        $walletQuery->execute();
        $walletResult = $walletQuery->get_result();

        if ($walletResult->num_rows > 0) {
            $walletData = $walletResult->fetch_assoc();
            $currentBalance = $walletData['Balance'];

            // Check if the sender has sufficient funds
            if ($currentBalance >= $amount) {
                // Start the transaction
                $conn->begin_transaction();

                try {
                    // Debit the sender's wallet
                    $newSenderBalance = $currentBalance + $amount;
                    $updateSenderWallet = $conn->prepare("UPDATE Wallet SET Balance = ? WHERE WalletID = ?");
                    $updateSenderWallet->bind_param("di", $newSenderBalance, $senderWalletID);
                    $updateSenderWallet->execute();

                    // Credit the recipient's wallet
                    $recipientWalletQuery = $conn->prepare("SELECT Balance FROM Wallet WHERE WalletID = ?");
                    $recipientWalletQuery->bind_param("i", $recipientWalletID);
                    $recipientWalletQuery->execute();
                    $recipientWalletResult = $recipientWalletQuery->get_result();
                    $recipientWalletData = $recipientWalletResult->fetch_assoc();
                    $recipientBalance = $recipientWalletData['Balance'];

                    $newRecipientBalance = $recipientBalance - $amount;
                    $updateRecipientWallet = $conn->prepare("UPDATE Wallet SET Balance = ? WHERE WalletID = ?");
                    $updateRecipientWallet->bind_param("di", $newRecipientBalance, $recipientWalletID);
                    $updateRecipientWallet->execute();

                    // Log the transaction for sender (Send)
                    $transactionQuery = $conn->prepare("INSERT INTO Transaction (TransactionType, Amount, Memo, RecipientID, Status, TWalletID)
                                                        VALUES ('Credit', ?, ?, ?, 'Completed', ?)");
                    $transactionQuery->bind_param("dsii", $amount, $memo, $recipientID, $senderWalletID);
                    $transactionQuery->execute();

                    // Log the transaction for recipient (Receive)
                    $recipientTransactionQuery = $conn->prepare("INSERT INTO Transaction (TransactionType, Amount, Memo, RecipientID, Status, TWalletID)
                                                                 VALUES ('Debit', ?, ?, ?, 'Completed', ?)");
                    $recipientTransactionQuery->bind_param("dsii", $amount, $memo, $userID, $recipientWalletID);
                    $recipientTransactionQuery->execute();

                    // Commit the transaction
                    $conn->commit();
                    echo "Transaction completed successfully!";
                } catch (Exception $e) {
                    $conn->rollback();
                    echo "Transaction failed: " . $e->getMessage();
                }
            } else {
                echo "Insufficient balance!";
            }
        } else {
            echo "Error fetching wallet details.";
        }
    } else {
        echo "Recipient not found!";
    }

    $conn->close();
}
?>

<form method="post">
    Recipient (Phone or Email): <input type="text" name="recipient" required><br>
    Amount: <input type="number" name="amount" required><br>
    Memo: <input type="text" name="memo"><br>
    <button type="submit">Request Money</button>
</form>
