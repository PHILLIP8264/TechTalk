<?php
require 'config.php'; // Include your database connection

// Check if OrderID is set in the GET request
if (isset($_GET['OrderID'])) {
    $orderID = $_GET['OrderID'];

    // Fetch order items for the given order
    $sql = "SELECT oi.Quantity, p.Name, p.Price 
            FROM OrderItems oi 
            JOIN Products p ON oi.ProductID = p.ProductID 
            WHERE oi.OrderID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($item = $result->fetch_assoc()) {
            echo '<div>';
            echo '<p>Product: ' . htmlspecialchars($item['Name']) . '</p>';
            echo '<p>Quantity: ' . htmlspecialchars($item['Quantity']) . '</p>';
            echo '<p>Price: R' . htmlspecialchars($item['Price']) . '</p>';
            echo '</div>';
        }
    } else {
        echo '<p>No items found for this order.</p>';
    }
} else {
    echo '<p>Order ID not specified.</p>';
}
?>
