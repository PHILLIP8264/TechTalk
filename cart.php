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
$sql = "SELECT ProfilePicture, UserID FROM Users WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $profileImage = !empty($user['ProfilePicture']) ? $user['ProfilePicture'] : 'Img/user.png';
    $userID = $user['UserID']; // Get the UserID for fetching orders
} else {
    $profileImage = 'Img/user.png'; // Default image if user is not found
    $userID = 0; // No valid user, use 0 to prevent errors
}

// Fetch user orders along with order items
$orderSql = "SELECT o.OrderID, o.OrderDate, oi.Quantity, p.Name, p.Price, p.image 
             FROM Orders o
             JOIN OrderItems oi ON o.OrderID = oi.OrderID
             JOIN Products p ON oi.ProductID = p.ProductID
             WHERE o.UserID = ?
             ORDER BY o.OrderDate DESC, o.OrderID ASC";
$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param("i", $userID);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

$currentOrderID = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed">
    <link rel="stylesheet" href="Page.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <style>
        .order-container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .order-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #333;
            color: white;
        }
        .order-item h3, .order-item p {
            margin: 5px 0;
            background-color: #333;
            text-decoration: none;
        }
        .order-items {
            margin-top: 10px;
            padding-left: 20px;
            display: none;
            background-color: #333;
        }
        .order-items.show {
            display: block;
        }
        .product-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .product-item img {
            width: 50px;
            margin-right: 10px;
        }
        .order-summary {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            
        }
        .view-order-btn {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
        /* Modal styles */
        .modal {
            display: none; 
            position: fixed;
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .modal-form input[type="text"], .modal-form input[type="number"] {
            width: calc(100% - 22px);
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .modal-form .half-width {
            width: calc(50% - 12px);
            display: inline-block;
        }
        .modal-form .half-width:nth-child(even) {
            margin-left: 20px;
        }
        .modal-form button {
            width: 48%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
        }
        .modal-form .cancel-btn {
            background-color: #f44336;
            color: white;
        }
        .modal-form .pay-btn {
            background-color: #4CAF50;
            color: white;
        }
        .view-order-btn{
            text-decoration: none;
            color: red;
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

<div class="order-container">
    <h2>Your Orders</h2>
    <?php
    if ($orderResult->num_rows > 0) {
        while ($row = $orderResult->fetch_assoc()) {
            if ($currentOrderID !== $row['OrderID']) {
                if ($currentOrderID !== null) {
                    // Close the previous order item div
                    echo '<div class="order-summary">';
                    echo '<p>Items: ' . $itemCount . '</p>';
                    echo '<p>Total: R' . number_format($totalPrice, 2) . '</p>';
                    echo '</div>';
                    echo '<button onclick="showModal()">Order</button>';
                    echo '</div>';
                }

                // New order item div
                echo '<div class="order-item">';
                echo '<h3>Order: ' . htmlspecialchars($row['OrderID']) . '</h3>';
                echo '<p>Order Date: ' . htmlspecialchars($row['OrderDate']) . '</p>';
                echo '<span class="view-order-btn" onclick="toggleOrderItems(' . htmlspecialchars($row['OrderID']) . ')">View Order</span>';
                echo '<div class="order-items" id="order-' . htmlspecialchars($row['OrderID']) . '">'; // Open order items container

                // Reset item count and total price for the new order
                $itemCount = 0;
                $totalPrice = 0;
            }

            // Calculate the total price for the current item
            $itemTotalPrice = $row['Quantity'] * $row['Price'];
            $totalPrice += $itemTotalPrice;

            // Display each item in the order
            echo '<div class="product-item">';
            echo '<img src="' . htmlspecialchars($row['image']) . '" alt="Product Image">';
            echo '<p>Product: ' . htmlspecialchars($row['Name']) . '</p>';
            echo '<p>Quantity: ' . htmlspecialchars($row['Quantity']) . '</p>';
            echo '<p>Price: R' . number_format($itemTotalPrice, 2) . '</p>';
            echo '</div>';

            // Increment item count
            $itemCount++;

            // Update the currentOrderID
            $currentOrderID = $row['OrderID'];
        }

        // Close the last order item div
        if ($currentOrderID !== null) {
            echo '<div class="order-summary">';
            echo '<p>Items: ' . $itemCount . '</p>';
            echo '<p>Total: R' . number_format($totalPrice, 2) . '</p>';
            echo '</div>';
            echo '<button onclick="showModal()">Order</button>';
            echo '</div>';
        }
    } else {
        echo '<p>No orders found.</p>';
    }
    ?>
</div>

<!-- The Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Order Details</h2>
        <div id="orderDetails">
            <!-- Order details will be injected here -->
        </div>
        <h3>Card Details</h3>
        <form class="modal-form">
            <input type="text" placeholder="Card Number" id="cardNumber" required>
            <div>
                <input type="text" class="half-width" placeholder="Expiry Date (MM/YY)" id="expiryDate" required>
                <input type="text" class="half-width" placeholder="CVV" id="cvv" required>
            </div>
            <input type="text" placeholder="Card Holder Name" id="cardHolder" required>
            <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
            <button type="button" class="pay-btn" onclick="pay()">Pay</button>
        </form>
    </div>
</div>

<script>
    // Function to toggle the order items
    function toggleOrderItems(orderID) {
        const orderItems = document.getElementById('order-' + orderID);
        orderItems.classList.toggle('show');
    }

    // Function to show the modal
    function showModal() {
        document.getElementById('orderModal').style.display = 'block';
    }

    // Function to close the modal
    function closeModal() {
        document.getElementById('orderModal').style.display = 'none';
    }

    // Function to handle payment
    function pay() {
        alert('Items ordered');
        closeModal();
    }

    // Function to toggle the menu
    function toggleMenu() {
        const dropdown = document.querySelector('.dropdown-menu');
        dropdown.classList.toggle('show');
    }

    // Close the modal if the user clicks outside of it
    window.onclick = function(event) {
        const modal = document.getElementById('orderModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
</body>
</html>
