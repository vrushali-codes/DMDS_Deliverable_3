<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Network</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Welcome to Wallet Network, <?php echo $_SESSION['name']; ?>!</h1>
    </header>
    <nav>
        <ul>
            <!-- Main functionalities -->
            <li>Profile
                <ul class="submenu">
                    <li><a href="account/account_info.php">Account Info</a></li>
                    <li><a href="account/modify_email.php">Modify Email Address</a></li>
                    <li><a href="account/modify_phone.php">Modify Phone Number</a></li>
                    <li><a href="account/modify_account.php">Modify Bank Account</a></li>
                </ul>
            </li>
            <li>Transactions
                <ul class="submenu">
                <li><a href="transactions/send_money.php">Send Money</a></li>
                <li><a href="transactions/request_money.php">Request Money</a></li>
                </ul>
            </li>
            <li><a href="transactions/monthly_statements.php">Statements</a></li>
            <li><a href="transactions/search_transactions.php">Search Transactions</a></li>
            <li><a href="logout.php" class="logout-button">Logout</a></li>
        </ul>
    </nav>
</body>
</html>