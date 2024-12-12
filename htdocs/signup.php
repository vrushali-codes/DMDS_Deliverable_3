<?php
include 'database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ssn = $_POST['ssn'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // Insert into the User table
    $query = $conn->prepare("INSERT INTO User (SSN, Name) VALUES (?, ?)");
    $query->bind_param("ss", $ssn, $name);
    if ($query->execute()) {
        $user_id = $conn->insert_id;

        // Insert into the PhoneNo table
        $phone_query = $conn->prepare("INSERT INTO PhoneNo (PhoneNo, Verifies) VALUES (?, ?)");
        $phone_query->bind_param("si", $phone, $user_id);
        $phone_query->execute();

        $phone_query = $conn->prepare("UPDATE USER SET UserPhoneNo = ? WHERE UserID = ?;");
        $phone_query->bind_param("si", $phone, $user_id);
        $phone_query->execute();

        // Insert into the EmailAddress table
        $email_query = $conn->prepare("INSERT INTO EmailAddress (EmailID, EUserID) VALUES (?, ?)");
        $email_query->bind_param("si", $email, $user_id);
        $email_query->execute();

        // Redirect to index page after successful signup
        header("Location: index.html");
        exit();
    } else {
        $error = "Failed to create account. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Welcome to Wallet Payment Network</h1>
    </header>
    <form action="signup.php" method="post">
        <h2>Sign Up</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required>
        <label for="ssn">SSN:</label>
        <input type="text" name="ssn" id="ssn" required>
        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" id="phone" required>
        <label for="email">Email Address:</label>
        <input type="email" name="email" id="email" required>
        <button type="submit">Sign Up</button>
    </form>
</body>
</html>