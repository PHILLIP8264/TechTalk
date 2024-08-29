<?php
session_start();
require 'config.php'; // Include your database connection

// Check if the session variable 'username' is set
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login page
    exit(); // Terminate the script to ensure redirection
}

// Get the current user's ID from the database
$username = $_SESSION['username'];
$sql = "SELECT userID FROM Users WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userID = $user['userID'];

// Handle the form data
$title = $_POST['postTitle'];
$body = $_POST['postBody'];
$tags = isset($_POST['postTags']) ? implode(', ', $_POST['postTags']) : '';
$datePosted = date('Y-m-d H:i:s'); // Current date and time

// Handle the file upload
$imagePath = '';
if (isset($_FILES['postImage']) && $_FILES['postImage']['error'] == 0) {
    $fileName = basename($_FILES['postImage']['name']);
    $fileTmpName = $_FILES['postImage']['tmp_name'];
    $fileDestination = 'uploads/' . $fileName;
    
    // Ensure uploads directory exists
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }
    
    move_uploaded_file($fileTmpName, $fileDestination);
    $imagePath = $fileDestination;
}

// Insert the post into the database
$sql = "INSERT INTO Questions (Title, Body, datePosted, userID, Tag, image) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssisss", $title, $body, $datePosted, $userID, $tags, $imagePath);
$stmt->execute();

// Redirect to main feed or another page
header("Location: Mainfeed.php");
exit();
?>
