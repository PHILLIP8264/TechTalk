<?php 
session_start(); // Start the session

// Check if the session variable 'username' is set
if(!isset($_SESSION['username'])){
    header("Location: login.php"); // Redirect to login page
    exit(); // Terminate the script to ensure redirection
}

require 'config.php'; // Include your database connection

// Get the current user's information from the database
$username = $_SESSION['username'];
$sql = "SELECT ProfilePicture FROM Users WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $profileImage = !empty($user['ProfilePicture']) ? $user['ProfilePicture'] : 'Img/user.png';
} else {
    $profileImage = 'Img/user.png'; // Default image if user is not found
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed">
    <link rel="stylesheet" href="Page.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="navbar">
        <div class="menu-icon">
            <h1 onclick="toggleMenu()">☰</h1>
            <div class="dropdown-menu">
                <a href="./home.php">Home</a>
                <a href="./Mainfeed.php">MainFeed</a>
                <a href="./settings.php">Settings</a>
            </div>
        </div>
        
        <div class="logo">
            <img src="Img/Vector 278.png" alt="WebApp Logo" height="40">
            <h1>TechTalk</h1>
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Search...">
        </div>
        <div class="profile">
            <a href="./profile.php">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image">
            </a>
        </div>
        <div class="nav-cart">
            <a href="./cart.php">
                <img src="Img\shopping-cart.png" alt="cart image">
            </a>
        </div>
        <div class="logout">
            <a href="./logout.php">
                <img src="Img/logout.png" alt="Logout">
            </a>
        </div>
    </div>

    <div class="main-feed-container">
        <h1>Main Feed</h1>
        <div class="main-feed">
            <!-- All questions here -->
        </div>
    </div>

    <div class="main-feed-container">
        <!-- Additional content can go here -->
    </div>

    <script>
        function toggleMenu() {
            const dropdown = document.querySelector('.dropdown-menu');
            dropdown.classList.toggle('show');
        }
    </script>
</body>
</html>
