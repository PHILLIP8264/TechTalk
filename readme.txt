Overview
This project is a dynamic Tech Q&A Web Application developed using PHP and MySQL. It allows users to post questions, provide answers, and like posts. The application includes a product page, shopping cart, order management, and a user profile management system. This README file provides detailed information on setting up, configuring, and using the web application.

Table of Contents
Installation
Database Setup
Project Structure
Features
Usage
Configuration
Dependencies
Troubleshooting
Contributing
License
Installation
Prerequisites
Web Server: Apache, Nginx, or any server capable of running PHP
PHP: Version 7.4 or higher
MySQL: Version 5.7 or higher
Composer: (Optional) for dependency management
Steps
Clone the repository:

bash
Copy code
git clone https://github.com/your-username/tech-qa-webapp.git
Navigate to the project directory:

bash
Copy code
cd tech-qa-webapp
Set up the environment:

Create a config.php file in the root directory. This file will store your database configuration settings.
Install dependencies (optional): If you are using Composer, run:

bash
Copy code
composer install
Set file permissions: Ensure the web server has the necessary permissions to write to directories like uploads/ (for profile pictures and post images).

Database Setup
Create the database:

sql
Copy code
CREATE DATABASE tech_qa_db;
Import the database schema:

Navigate to the project’s root directory.
Use the MySQL command line or a tool like phpMyAdmin to import the tech_qa_db.sql file:
bash
Copy code
mysql -u your_username -p tech_qa_db < tech_qa_db.sql
Configure the database connection: Edit the config.php file with your database credentials:

php
Copy code
<?php
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "tech_qa_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
Project Structure
bash
Copy code
/tech-qa-webapp
│
├── /assets            # CSS, JavaScript, and image files
│   ├── css/
│   ├── js/
│   └── img/
├── /uploads           # Directory for uploaded images (profile pictures, post images)
├── /views             # HTML and PHP views
│   ├── homepage.php
│   ├── mainfeed.php
│   ├── cart.php
│   ├── profile.php
│   └── ...
├── /includes          # Reusable PHP scripts (e.g., header, footer, config)
│   ├── header.php
│   ├── footer.php
│   ├── config.php
│   └── ...
├── /scripts           # JavaScript files
│   └── app.js
├── /sql               # SQL scripts for database schema and sample data
│   ├── tech_qa_db.sql
│   └── sample_data.sql
├── index.php          # Entry point for the application
└── README.txt         # This README file
Features
User Authentication:

Login, registration, and logout functionality
User profile management (update profile picture, email, username, password)
Post Questions:

Users can post questions with a title, body, and an optional image.
Questions can be tagged for better organization.
Answer Questions:

Users can answer questions, and answers are displayed in a collapsible format.
Like System:

Users can like questions, and the like count is displayed.
Product Catalog:

Products are displayed in a grid layout with details like name, price, description, and image.
Shopping Cart:

Users can add products to the cart and place orders.
Orders are saved in the database and displayed in the user’s cart page.
Usage
Register and Login:

Create a new account or log in with an existing one.
Posting a Question:

Click on the "Post+" button, fill in the details, and submit your question.
Answering a Question:

Navigate to the question in the feed, click "Answer," and submit your response.
Liking a Post:

Click the heart icon (♡) to like a post. The icon will change to a filled heart (♥) when liked.
Shopping Cart:

Add products to your cart from the product page and proceed to checkout.
Viewing Orders:

Go to the "Cart" page to view and manage your orders.
Configuration
config.php: Configure database connection settings here.
uploads/: Ensure this directory is writable for user-uploaded images.
Dependencies
PHP (version 7.4+)
MySQL (version 5.7+)
JavaScript (for dynamic features like collapsible answers, liking posts)
CSS (for styling)
Troubleshooting
Database Connection Issues:

Ensure your config.php has the correct credentials and the database server is running.
File Permission Issues:

Ensure the uploads/ directory has write permissions.
404 Errors:

Check that all paths in your project structure are correct.
Contributing
If you'd like to contribute to this project, please fork the repository, make your changes, and submit a pull request. Ensure your code is well-documented and follows the existing code style.
