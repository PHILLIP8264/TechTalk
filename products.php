<?php
session_start();
require 'config.php'; // Include your database connection

// Check if the session variable 'username' is set
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page
    exit(); // Terminate the script to ensure redirection
}

// Get the current user's information from the database
$username = $_SESSION['username'];
$sql = "SELECT ProfilePicture, UserID FROM Users WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $profileImage = !empty($user['ProfilePicture']) ? $user['ProfilePicture'] : 'Img/user.png';
    $userID = $user['UserID']; // Fetch the UserID for later use
} else {
    $profileImage = 'Img/user.png'; // Default image if user is not found
}

// Get the category from the query parameter or session storage (handled by JavaScript)
$category = isset($_GET['Category']) ? $_GET['Category'] : '';

if (!empty($category)) {
    // Fetch products based on the selected category
    $sql = "SELECT * FROM Products WHERE Category = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Handle Add to Cart functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productID = $_POST['product_id'];
    $quantity = 1; // Default quantity to 1 for now

    // Check if there's already an order for this session
    if (!isset($_SESSION['order_id'])) {
        // Create a new order
        $orderDate = date("Y-m-d H:i:s");
        $totalAmount = 0.0; // Default total amount
        $sql = "INSERT INTO Orders (OrderDate, TotalAmount, UserID) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdi", $orderDate, $totalAmount, $userID);
        $stmt->execute();
        $orderID = $stmt->insert_id;

        // Store the order ID in the session
        $_SESSION['order_id'] = $orderID;
    } else {
        // Use the existing order ID
        $orderID = $_SESSION['order_id'];
    }

    // Add the product to the OrderItems table
    $sql = "INSERT INTO OrderItems (Quantity, ProductID, OrderID) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("iii", $quantity, $productID, $orderID);
    $stmt->execute();

    echo "Product added to cart!";
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
        .product-container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-gap: 20px;
        }
        .product-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            background-color: #333;
        }
        .product-item img {
            width: 100%;
            height: auto;
            margin-bottom: 10px;
        }
        .dropdown-menu {
            display: none;
        }
        .dropdown-menu.show {
            display: block;
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
        <div class="product-grid">
            <?php
            if (!empty($category) && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="product-item">';
                    echo '<img src="' . htmlspecialchars($row['image']) . '" alt="Product Image">';
                    echo '<h2>' . htmlspecialchars($row['Name']) . '</h2>';
                    echo '<p>' . htmlspecialchars($row['Description']) . '</p>';
                    echo '<p>Price: R' . htmlspecialchars($row['Price']) . '</p>';
                    echo '<p>Stock: ' . htmlspecialchars($row['Stock']) . '</p>';
                    echo '<form method="POST">';
                    echo '<input type="hidden" name="product_id" value="' . $row['ProductID'] . '">';
                    echo '<button type="submit">Add to Cart</button>';
                    echo '</form>';
                    echo '</div>';
                }
            } else {
                echo '<p>No products available in this category.</p>';
            }
            ?>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const dropdown = document.querySelector('.dropdown-menu');
            dropdown.classList.toggle('show');
        }

        // Retrieve category from session storage and redirect to product page with the category
        window.onload = function() {
            const selectedCategory = sessionStorage.getItem('selectedProductTile');
            const urlParams = new URLSearchParams(window.location.search);
            const currentCategory = urlParams.get('Category');

            // Only redirect if there's no category in the URL and we have a category in sessionStorage
            if (selectedCategory && !currentCategory) {
                // Redirect to the product page with the selected category as a query parameter
                window.location.href = window.location.pathname + '?Category=' + selectedCategory;

                // Clear session storage to prevent continuous redirection
                sessionStorage.removeItem('selectedProductTile');
            }
        };
    </script>
</body>
</html>
