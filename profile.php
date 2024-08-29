<?php
session_start();
require 'config.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the current user's information from the database
$username = $_SESSION['username'];
$sql = "SELECT * FROM Users WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Determine the profile image to use
$profileImage = !empty($user['ProfilePicture']) ? htmlspecialchars($user['ProfilePicture']) : 'Img/user.png';

// Handle form submission for profile updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];

    // Handle profile image upload
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
        $target_dir = "./uploads/profileimg/";
        // Ensure the directory exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory with correct permissions
        }
        $target_file = $target_dir . basename($_FILES["profileImage"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image
        $check = getimagesize($_FILES["profileImage"]["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size (limit to 2MB)
        if ($_FILES["profileImage"]["size"] > 200000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $target_file)) {
                // Update profile image path in the database
                $sql = "UPDATE Users SET ProfilePicture = ? WHERE Username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $target_file, $username);
                $stmt->execute();
                $profileImage = $target_file; // Update the profile image variable
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Prepare SQL for updating username, email, and password
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $sql = "UPDATE Users SET Username = ?, Email = ?, Password = ? WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $new_username, $new_email, $hashed_password, $username);
    } else {
        $sql = "UPDATE Users SET Username = ?, Email = ? WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $new_username, $new_email, $username);
    }

    if ($stmt->execute()) {
        $_SESSION['username'] = $new_username; // Update session username
        echo "Profile updated successfully.";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed">
    <link rel="stylesheet" href="Page.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <style>
        .profile-container {
            width: 40%;
            margin: 0 auto;
            text-align: center;
            border: green 3px solid;
        }

        .password-container {
            position: relative;
        }
        
        .password-container input[type="password"] {
            width: 100%;
        }
        
        .profile-image-container {
            position: relative;
            display: inline-block;
        }
        
        .profile-image-container img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            cursor: pointer;
        }
        
        .profile-image-container input[type="file"] {
            display: none;
        }

        .profile-input-fields {
            width: 60%;
            margin: 0 auto;
            text-align: center;
            padding-top: 5vh;
        }
        
        .profile-input-fields label{  
            color: black;
            float: left;
        }

        .profile-image-container label {
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


    <div class="profile-container">
        <h1>Profile Settings</h1>
        
        <form method="POST" action="profile.php" enctype="multipart/form-data">
            <!-- Profile Image -->
            <div class="profile-image-container">
                <label for="profileImage">
                    <img src="<?php echo !empty($user['ProfilePicture']) ? htmlspecialchars($user['ProfilePicture']) : 'Img/user.png'; ?>" alt="Profile Image">
                </label>
                <input type="file" id="profileImage" name="profileImage" onchange="this.form.submit()">
                <h1>Change profile Image</h1>
            </div>
            <br>
            <div class="profile-input-fields">
                <!-- Username -->
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
                <br>

                <!-- Email -->
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                <br>

                <!-- Password -->
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter new password">
            
            <br>

            <!-- Save Changes Button -->
            <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>

    <script>
        function toggleMenu() {
            const dropdown = document.querySelector('.dropdown-menu');
            dropdown.classList.toggle('show');
        }
    </script>
</body>
</html>
