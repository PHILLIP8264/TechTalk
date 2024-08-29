<?php 
session_start(); // Starts or resumes a session
require 'config.php'; // Includes the config file

// Check if the request method is POST, which indicates that the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Retrieve the form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Hash the password before storing it in the database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Define the SQL query to insert the user information into the Users table
        $sql = "INSERT INTO Users (Username, Email, Password) VALUES (?, ?, ?)";

        // Prepare the SQL statement for execution
        $stmt = $conn->prepare($sql);

        // Bind the user's input values into the SQL statement
        $stmt->bind_param("sss", $username, $email, $hashed_password); // "sss" means three string parameters
        
        // Execute the prepared statement
        if ($stmt->execute()) {
            echo "Registration Complete";
            // Redirect after 3 seconds
            header("refresh:3;url=login.php");
            exit(); // Ensure script stops here
        } else {
            echo "Error: " . $stmt->error;
        }
        
        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed" >
    <title>Sign Up</title>
    <link rel="stylesheet" href="Page.css">
</head>
<body>
    <div class="container">
        <div class="Sign">
            <h1>Sign Up</h1>
            <form method="POST" action="signup.php">
                <div class="form-group">
                    <h2>Username</h2>
                    <input type="text" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <h2>Email</h2>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <h2>Password</h2>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <h2>Confirm Password</h2>
                    <input type="password" name="confirm-password" placeholder="Confirm your password" required>
                </div>
                <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
                <button type="submit">Sign Up</button>
            </form>
        </div>
    </div>
</body>
</html>
