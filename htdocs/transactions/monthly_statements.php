<?php
session_start();
require_once '../database/config.php';

// Check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit();
}

$userId = $_SESSION['user_id']; // Retrieve UserID from the session

// Handle GET request to fetch monthly stats and display as an HTML table
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Improved query to fetch monthly statistics based on the user wallet ID
    $query = "
        SELECT
            MONTHNAME(InitializedTime) AS Month,
            YEAR(InitializedTime) AS Year,
            TransactionType,
            MAX(CASE WHEN TransactionType = 'Debit' THEN Amount ELSE NULL END) AS MaxDebit,
            AVG(CASE WHEN TransactionType = 'Debit' THEN Amount ELSE NULL END) AS AvgDebit,
            MAX(CASE WHEN TransactionType = 'Credit' THEN Amount ELSE NULL END) AS MaxCredit,
            AVG(CASE WHEN TransactionType = 'Credit' THEN Amount ELSE NULL END) AS AvgCredit
        FROM
            Transaction
        WHERE
            (TWalletID = (SELECT UserWalletID FROM User WHERE UserID = ?) OR
            RecipientID = (SELECT UserWalletID FROM User WHERE UserID = ?))
        GROUP BY
            MONTH(InitializedTime), YEAR(InitializedTime), TransactionType
        ORDER BY
            Year DESC, Month DESC, TransactionType
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // If there is no data, display a message
    if (empty($data)) {
        echo "<p>No transaction statistics available.</p>";
    } else {
        echo "<table border='1'>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Transaction Type</th>
                        <th>Max Amount</th>
                        <th>Avg Amount</th>
                    </tr>
                </thead>
                <tbody>";
        foreach ($data as $stat) {
            $transactionType = $stat['TransactionType'];
            $maxAmount = $transactionType === 'Debit' ? $stat['MaxDebit'] : $stat['MaxCredit'];
            $avgAmount = $transactionType === 'Debit' ? $stat['AvgDebit'] : $stat['AvgCredit'];

            // Handle null values for max and avg amounts
            $maxAmount = $maxAmount ? $maxAmount : 'N/A';
            $avgAmount = $avgAmount ? $avgAmount : 'N/A';

            echo "<tr>
                    <td>{$stat['Month']}</td>
                    <td>{$stat['Year']}</td>
                    <td>{$transactionType}</td>
                    <td>{$maxAmount}</td>
                    <td>{$avgAmount}</td>
                </tr>";
        }
        echo "</tbody></table>";
    }
    exit();  // End the script after rendering the table
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Statistics</title>
</head>
<body>
    <h1>Monthly Statistics</h1>
    <p>Welcome, <?php echo $_SESSION['name']; ?>!</p>
    <h2>Your Monthly Statistics</h2>
    <button id="fetch-stats">Fetch Stats</button>
    <div id="stats-output"></div>

    <script>
        document.getElementById('fetch-stats').addEventListener('click', async () => {
            try {
                const response = await fetch(window.location.href);  // Fetch the stats from the current page URL
                const stats = await response.text();  // Get the HTML content (not JSON)

                const outputDiv = document.getElementById('stats-output');
                outputDiv.innerHTML = stats;  // Insert the HTML content (table)

            } catch (error) {
                console.error("Error fetching statistics:", error);
                document.getElementById('stats-output').innerHTML = "<p>Error fetching statistics. Please try again later.</p>";
            }
        });
    </script>
</body>
</html>
