<?php 
session_start(); // Start the session

// Check if the session variable 'username' is set
if (!isset($_SESSION['username'])) {
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

// Fetch the latest 6 questions
$questionSql = "SELECT * FROM Questions ORDER BY datePosted DESC LIMIT 6";
$questionResult = $conn->query($questionSql);
if (!$questionResult) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed">
    <link rel="stylesheet" href="Page.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <style>
        .feed-container {
            width: 70%;
            margin: 20px auto;
            background-color: #333;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: white;
        }
        .feed-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .feed-item:last-child {
            border-bottom: none;
        }
        .feed-item img {
            max-width: 100%;
            height: auto;
        }
        .feed-item p {
            margin: 5px 0;
        }
    </style>
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

    <div class="product-container">
        <h1>Products</h1>
        <div class="product-tiles">
            <div class='product-tiles-button'>
                <a role='button' id="component" href='./products.php?category=components'>
                    <img src="Img/cpu 1.png" class="product-tiles-img" alt="component-image">
                    <p>Component</p>
                </a>
            </div>
            <div class='product-tiles-button'>
                <a role='button' id="accessories" href='./products.php?category=accessories'>
                    <img src="Img/keyboard-and-mouse.png" class="product-tiles-img" alt="accessory-image">
                    <p>Accessories</p>
                </a>
            </div>
            <div class='product-tiles-button'>
                <a role='button' id="monitor" href='./products.php?category=monitors'>
                    <img src="Img/monitor.png" class="product-tiles-img" alt="monitor-image">
                    <p>Monitors</p>
                </a>
            </div>
            <div class='product-tiles-button'>
                <a role='button' id="gpu" href='./products.php?category=gpus'>
                    <img src="Img/gpu-mining.png" class="product-tiles-img" alt="gpu-image">
                    <p>Gpu</p>
                </a>
            </div>
            <div class='product-tiles-button'>
                <a role='button' id="cooling" href='./products.php?category=cooling'>
                    <img src="Img/fan.png" class="product-tiles-img" alt="cooling-image">
                    <p>Cooling</p>
                </a>
            </div>
        </div>
    </div>

    <div class="feed-container">
        <h2>Latest Questions</h2>
        <?php
        if ($questionResult->num_rows > 0) {
            while ($row = $questionResult->fetch_assoc()) {
                echo '<div class="feed-item">';
                echo '<h3>' . htmlspecialchars($row['Title']) . '</h3>';
                if ($row['Image']) {
                    echo '<img src="' . htmlspecialchars($row['Image']) . '" alt="Question Image">';
                }
                echo '<p>' . htmlspecialchars($row['Body']) . '</p>';
                echo '<p><small>Posted on: ' . htmlspecialchars($row['DatePosted']) . '</small></p>';
                echo '</div>';
            }
        } else {
            echo '<p>No questions available.</p>';
        }
        ?>
    </div>

    <script>
        // Function to toggle the menu
        function toggleMenu() {
            const dropdown = document.querySelector('.dropdown-menu');
            dropdown.classList.toggle('show');
        }

        // Add event listeners to product tiles to store ID in sessionStorage
        document.querySelectorAll('.product-tiles-button a').forEach(tile => {
            tile.addEventListener('click', function(event) {
                const tileId = this.id; // Get the ID of the clicked <a> tag
                sessionStorage.setItem('selectedProductTile', tileId); // Store the ID in sessionStorage
            });
        });
    </script>
</body>
</html>
