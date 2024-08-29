<?php 
session_start(); // Start the session
require 'config.php'; // Include the config file for database connection

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get the username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // SQL query to find the user by username 
    $sql = "SELECT * FROM Users WHERE Username = ?";  // Updated table and column names

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);

    // Bind the username to the SQL statement
    $stmt->bind_param("s", $username); // "s" means a string parameter
    
    // Execute the SQL statement
    $stmt->execute();

    // Store the result in the 'result' variable
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Verify the password using password_verify
        if (password_verify($password, $user['Password'])) {  // Updated password check

            // Store user information in session
            $_SESSION['username'] = $user['Username'];

            // Redirect to home page
            header("Location: home.php");
            exit(); // Terminate the script to ensure redirection
        } else {
            $error = "Invalid username or password"; // (Technically just: password wrong)
        }
    } else {
        $error = "Invalid username or password"; // (Technically just: username not found)
    }
    
    // Close the statement
    $stmt->close();

    // Close the database connection
    $conn->close(); 
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed" >
    <title>Login</title>
    <link rel="stylesheet" href="Page.css">
</head>
<body>
    <div class="container">
        <div class="Sign">
            <h1>Sign In</h1>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <h2>Username</h2>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <h2>Password</h2>
                    <input type="password" name="password" required>
                </div>
                <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
</body>
</html>
