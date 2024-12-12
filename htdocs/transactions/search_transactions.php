<?php
session_start();
include '../database/config.php';

// Check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get filter parameters
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Build query to fetch transactions
    $queryStr = "
        SELECT 
            t.TransactionID,
            t.TransactionType,
            t.Amount,
            t.InitializedTime,
            t.Status
        FROM 
            Transaction t
        JOIN Wallet w ON t.TWalletID = w.WalletID
        JOIN User u ON u.UserWalletID = w.WalletID
        WHERE 
            u.UserID = ? 
            AND t.InitializedTime BETWEEN ? AND ?
    ";

    // Prepare and bind parameters
    $query = $conn->prepare($queryStr);
    $query->bind_param("iss", $userId, $startDate, $endDate);

    // Execute query
    $query->execute();
    $result = $query->get_result();

    // Initialize totals
    $totalCreditAmount = 0;
    $totalDebitAmount = 0;

    // Process results
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
        if ($row['TransactionType'] === 'Credit') {
            $totalCreditAmount += $row['Amount'];
        } elseif ($row['TransactionType'] === 'Debit') {
            $totalDebitAmount += $row['Amount'];
        }
    }

    // Display results
    echo "<h2>Transaction Summary</h2>";
    echo "<p><strong>Total Credit Amount:</strong> $" . number_format($totalCreditAmount, 2) . "</p>";
    echo "<p><strong>Total Debit Amount:</strong> $" . number_format($totalDebitAmount, 2) . "</p>";

    echo "<h2>Transaction History</h2>";
    if (!empty($transactions)) {
        echo "<table border='1'>
                <tr>
                    <th>Transaction ID</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>";
        foreach ($transactions as $transaction) {
            echo "<tr>
                    <td>{$transaction['TransactionID']}</td>
                    <td>{$transaction['TransactionType']}</td>
                    <td>{$transaction['Amount']}</td>
                    <td>{$transaction['InitializedTime']}</td>
                    <td>{$transaction['Status']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No transactions found for the selected filters.</p>";
    }
}
?>

<form method="post">
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" id="start_date" required><br>

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" id="end_date" required><br>

    <button type="submit">Search</button>
</form>
