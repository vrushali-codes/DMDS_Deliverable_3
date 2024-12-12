<?php 
session_start(); // Start the session

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient = trim($_POST['recipient']);
    $amount = floatval($_POST['amount']);
    $memo = trim($_POST['memo']);

    // Fetch the sender's wallet ID based on the session user ID
    $senderWalletQuery = $conn->prepare("SELECT UserWalletID FROM User WHERE UserID = ?");
    if (!$senderWalletQuery) {
        die("Error preparing sender wallet query: " . $conn->error);
    }

    $senderWalletQuery->bind_param("i", $_SESSION['user_id']);
    $senderWalletQuery->execute();
    $senderWalletResult = $senderWalletQuery->get_result();

    if ($senderWalletResult->num_rows > 0) {
        $senderWalletData = $senderWalletResult->fetch_assoc();
        $senderWalletID = $senderWalletData['UserWalletID'];

        // Validate the recipient's existence and fetch their wallet ID
        $recipientQuery = $conn->prepare("
            SELECT u.UserID, Wallet.WalletID 
            FROM Wallet
            JOIN User u ON Wallet.WalletID = u.UserWalletID
            LEFT JOIN PhoneNo ON PhoneNo.PhoneNo = ? 
            LEFT JOIN EmailAddress ON EmailAddress.EmailID = ?
            WHERE PhoneNo.Verifies = u.UserID OR EmailAddress.EUserID = u.UserID
        ");
        if (!$recipientQuery) {
            die("Error preparing recipient query: " . $conn->error);
        }

        $recipientQuery->bind_param("ss", $recipient, $recipient);
        $recipientQuery->execute();
        $recipientResult = $recipientQuery->get_result();

        if ($recipientResult->num_rows > 0) {
            $recipientData = $recipientResult->fetch_assoc();
            $recipientWalletID = $recipientData['WalletID'];
            $recipientID = $recipientData['UserID'];

            // Check the sender's wallet balance
            $balanceQuery = $conn->prepare("SELECT Balance FROM Wallet WHERE WalletID = ?");
            if (!$balanceQuery) {
                die("Error preparing balance query: " . $conn->error);
            }

            $balanceQuery->bind_param("i", $senderWalletID);
            $balanceQuery->execute();
            $balanceResult = $balanceQuery->get_result();

            if ($balanceResult->num_rows > 0) {
                $balanceData = $balanceResult->fetch_assoc();

                if ($balanceData['Balance'] >= $amount) {
                    // Deduct amount from sender and add to recipient
                    $conn->begin_transaction();
                    try {
                        // Update sender's wallet balance
                        $deductQuery = $conn->prepare("UPDATE Wallet SET Balance = Balance - ? WHERE WalletID = ?");
                        if (!$deductQuery) {
                            throw new Exception("Error preparing deduct query: " . $conn->error);
                        }
                        $deductQuery->bind_param("di", $amount, $senderWalletID);
                        $deductQuery->execute();

                        // Update recipient's wallet balance
                        $addQuery = $conn->prepare("UPDATE Wallet SET Balance = Balance + ? WHERE WalletID = ?");
                        if (!$addQuery) {
                            throw new Exception("Error preparing add query: " . $conn->error);
                        }
                        $addQuery->bind_param("di", $amount, $recipientWalletID);
                        $addQuery->execute();

                        // Log the transaction for sender (Send)
                        $transactionQuery = $conn->prepare("
                            INSERT INTO Transaction (TransactionType, Amount, Memo, RecipientID, Status, TWalletID)
                            VALUES ('Debit', ?, ?, ?, 'Completed', ?)
                        ");
                        if (!$transactionQuery) {
                            throw new Exception("Error preparing transaction query for sender: " . $conn->error);
                        }
                        $transactionQuery->bind_param("dsii", $amount, $memo, $recipientID, $senderWalletID);
                        $transactionQuery->execute();

                        // Log the transaction for recipient (Receive)
                        $recipientTransactionQuery = $conn->prepare("
                            INSERT INTO Transaction (TransactionType, Amount, Memo, RecipientID, Status, TWalletID)
                            VALUES ('Credit', ?, ?, ?, 'Completed', ?)
                        ");
                        if (!$recipientTransactionQuery) {
                            throw new Exception("Error preparing transaction query for recipient: " . $conn->error);
                        }
                        $recipientTransactionQuery->bind_param("dsii", $amount, $memo, $_SESSION['user_id'], $recipientWalletID);
                        $recipientTransactionQuery->execute();

                        // Commit the transaction
                        $conn->commit();
                        echo "Money sent successfully!";
                    } catch (Exception $e) {
                        $conn->rollback();
                        echo "Transaction failed: " . $e->getMessage();
                    }
                } else {
                    echo "Insufficient balance!";
                }
            } else {
                echo "Error fetching sender's wallet balance!";
            }
        } else {
            echo "Recipient not found!";
        }
    } else {
        echo "Sender's wallet not found!";
    }
}
?>

<form method="post">
    Recipient (Phone or Email): <input type="text" name="recipient" required><br>
    Amount: <input type="number" name="amount" step="0.01" required><br>
    Memo: <input type="text" name="memo"><br>
    <button type="submit">Send Money</button>
</form>
