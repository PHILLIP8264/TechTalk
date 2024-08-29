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
    $userID = $user['UserID'];
} else {
    $profileImage = 'Img/user.png'; // Default image if user is not found
    $userID = 0; // No valid user, use 0 to prevent errors
}

// Fetch the latest 6 posts
$sql = "SELECT q.*, 
               (SELECT COUNT(*) FROM Likes l WHERE l.QuestionID = q.QuestionID) AS LikeCount, 
               (SELECT COUNT(*) FROM Answers a WHERE a.QuestionID = q.QuestionID) AS AnswerCount 
        FROM Questions q ORDER BY datePosted DESC LIMIT 6";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Condensed">
    <link rel="stylesheet" href="Page.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Feed</title>
    <style>
        .main-feed-posts {
            width: 70%;
            margin: 0 auto;
            padding: 20px;
        }
        .post {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            position: relative;
        }
        .post .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            position: absolute;
            top: 15px;
            left: 15px;
        }
        .post-content {
            margin-left: 75px;
        }
        .post-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }
        .post-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }
        .post-actions button {
            background: none;
            border: none;
            cursor: pointer;
        }
        .like-button {
            color: #ddd;
        }
        .like-button.liked {
            color: red;
        }
        .answer-form, .answers-container {
            display: none;
            margin-top: 10px;
            padding-left: 20px;
        }
        .answers-container.collapsed {
            display: block;
        }
        .answers-container .answer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        .answers-container .answer img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
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

    <div class="main-feed-posts">
    <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $questionID = $row['QuestionID'];
                $sqlUser = "SELECT ProfilePicture FROM Users WHERE UserID = ?";
                $stmtUser = $conn->prepare($sqlUser);
                $stmtUser->bind_param("i", $row['UserID']);
                $stmtUser->execute();
                $resultUser = $stmtUser->get_result();
                $userImage = $resultUser->fetch_assoc()['ProfilePicture'] ?? 'Img/user.png';

                echo '<div class="post">';
                echo '<img src="' . htmlspecialchars($userImage) . '" class="profile-img" alt="Profile Image">';
                echo '<div class="post-content">';
                echo '<h2>' . htmlspecialchars($row['Title']) . '</h2>';
                if ($row['Image']) {
                    echo '<img src="' . htmlspecialchars($row['Image']) . '" class="post-image" alt="Post Image">';
                    echo '<p>' . htmlspecialchars($row['Body']) . '</p>';
                } else {
                    echo '<p>' . htmlspecialchars($row['Body']) . '</p>';
                }

                echo '<div class="post-actions">';
                echo '<button class="like-button" onclick="toggleLike(this, ' . $questionID . ', ' . $userID . ')">♡</button>';
                echo '<span class="like-count">' . $row['LikeCount'] . '</span>';
                echo '<button onclick="toggleAnswerForm(this)">Answer</button>';
                
                // Answers section
                echo '<div class="answer-form">';
                echo '<textarea rows="3" placeholder="Write your answer..."></textarea>';
                echo '<button onclick="submitAnswer(this, ' . $questionID . ', ' . $userID . ')">Submit</button>';
                echo '</div>';
                
                // Show answer count and a toggle button for answers
                echo '<div class="answers-container">';
                if ($row['AnswerCount'] > 0) {
                    echo '<button onclick="toggleAnswers(this)">Show ' . $row['AnswerCount'] . ' Answer(s)</button>';
                    
                    // Fetch and display answers
                    $answerSql = "SELECT a.Content, a.DatePosted, u.Username, u.ProfilePicture 
                                  FROM Answers a 
                                  JOIN Users u ON a.UserID = u.UserID 
                                  WHERE a.QuestionID = ? 
                                  ORDER BY a.DatePosted DESC";
                    $answerStmt = $conn->prepare($answerSql);
                    $answerStmt->bind_param("i", $questionID);
                    $answerStmt->execute();
                    $answerResult = $answerStmt->get_result();

                    while ($answerRow = $answerResult->fetch_assoc()) {
                        echo '<div class="answer">';
                        echo '<img src="' . htmlspecialchars($answerRow['ProfilePicture']) . '" alt="User Image">';
                        echo '<span>' . htmlspecialchars($answerRow['Username']) . ' (' . htmlspecialchars($answerRow['DatePosted']) . ')</span>';
                        echo '<p>' . htmlspecialchars($answerRow['Content']) . '</p>';
                        echo '</div>';
                    }
                }
                echo '</div>'; // End of answers container

                echo '</div>'; // End of post-actions
                echo '</div>'; // End of post-content
                echo '</div>'; // End of post
            }
        } else {
            echo '<p>No posts available.</p>';
        }
    ?>
    </div>

    <script>
        function toggleLike(button, questionID, userID) {
            const liked = button.classList.toggle('liked');
            button.textContent = liked ? '♥' : '♡';
            const likeCountSpan = button.nextElementSibling;
            let likeCount = parseInt(likeCountSpan.textContent, 10);
            likeCount += liked ? 1 : -1;
            likeCountSpan.textContent = likeCount;

            // AJAX request to update like in the database
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "like_post.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(`questionID=${questionID}&userID=${userID}&liked=${liked}`);
        }

        function toggleAnswerForm(button) {
            const form = button.nextElementSibling;
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }

        function submitAnswer(button, questionID, userID) {
            const answerContent = button.previousElementSibling.value.trim();
            if (answerContent === '') {
                alert("Answer cannot be empty!");
                return;
            }

            // AJAX request to submit answer
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "submit_answer.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Append the new answer to the answer container
                    const answersContainer = button.closest('.post-actions').querySelector('.answers-container');
                    const newAnswer = document.createElement('div');
                    newAnswer.classList.add('answer');
                    newAnswer.innerHTML = `<img src="${'<?php echo htmlspecialchars($profileImage); ?>'}" alt="User Image"><span><?php echo htmlspecialchars($username); ?> (Just Now)</span><p>${answerContent}</p>`;
                    answersContainer.appendChild(newAnswer);
                    button.previousElementSibling.value = ''; // Clear textarea
                }
            };
            xhr.send(`questionID=${questionID}&userID=${userID}&content=${encodeURIComponent(answerContent)}`);
        }

        function toggleAnswers(button) {
            const answersContainer = button.parentElement;
            const isCollapsed = answersContainer.classList.toggle('collapsed');
            button.textContent = isCollapsed ? 'Hide Answers' : 'Show Answers';
        }
    </script>
</body>
</html>
