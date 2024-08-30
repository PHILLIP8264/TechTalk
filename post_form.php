<?php
session_start();
require 'db_connect.php'; // Ensure this file contains your database connection setup

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $body = $_POST['body'];
    $tags = $_POST['tags'];
    $image = $_FILES['image'];

    // Validate the inputs
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    if (empty($body)) {
        $errors[] = "Body is required.";
    }
    if (empty($tags)) {
        $errors[] = "At least one tag is required.";
    }

    // Handle image upload
    if ($image && $image['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file size (5MB limit)
        if ($image['size'] > 5000000) {
            $errors[] = "Sorry, your file is too large.";
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }

        // If no errors, move the file to the uploads directory
        if (empty($errors)) {
            if (!move_uploaded_file($image['tmp_name'], $target_file)) {
                $errors[] = "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        $target_file = null;
    }

    // If there are no errors, insert the post into the database
    if (empty($errors)) {
        $sql = "INSERT INTO Questions (Title, Body, Tag, Image, UserID, datePosted) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $title, $body, $tags, $target_file, $_SESSION['UserID']);
        
        if ($stmt->execute()) {
            header('Location: MainFeed.php'); // Redirect to the main feed after successful post
            exit();
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a Post</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed">
    <link rel="stylesheet" href="Page.css">
    <style>
        body {
            font-family: 'Roboto Condensed', sans-serif;
        }
        .post-form-container {
            width: 60%;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .post-form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .post-form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .post-form-container input[type="text"], 
        .post-form-container textarea, 
        .post-form-container input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .post-form-container input[type="file"] {
            padding: 3px;
        }
        .post-form-container button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .post-form-container button:hover {
            background-color: #0056b3;
        }
        .error-messages {
            color: red;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="post-form-container">
        <h2>Create a New Post</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="post_form.php" method="POST" enctype="multipart/form-data">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">

            <label for="body">Body</label>
            <textarea id="body" name="body" rows="5"><?php echo isset($body) ? htmlspecialchars($body) : ''; ?></textarea>

            <label for="tags">Tags (separated by commas)</label>
            <input type="text" id="tags" name="tags" value="<?php echo isset($tags) ? htmlspecialchars($tags) : ''; ?>">

            <label for="image">Image (optional)</label>
            <input type="file" id="image" name="image">

            <button type="submit">Post</button>
        </form>
    </div>
</body>
</html>
