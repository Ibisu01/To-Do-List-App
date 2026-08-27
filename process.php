<?php
// This MUST be the very first thing in the file to track the logged-in user
session_start();

// Database connection credentials
$host = 'localhost';
$dbname = 'user_auth'; 
$username = 'root'; 
$password = ''; 

// Attempt to connect to the MySQL database using PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set PDO to throw exceptions if an error occurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // If connection fails, stop the script and show the error
    die("ERROR: Could not connect. " . $e->getMessage());
}

// --- REGISTRATION BLOCK ---
// Check if the registration form was submitted
if (isset($_POST['register'])) {
    // Sanitize user input to prevent malicious code injection
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];

    // Ensure no required fields are empty
    if (!empty($name) && !empty($email) && !empty($pass)) {
        // Securely hash the password before storing it
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        // Prepare the SQL query to insert the new user
        $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        
        try {
            $stmt = $pdo->prepare($sql);
            // Execute the query with the provided data
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashed_password
            ]);

            // FIX: Log the user in immediately after registering by saving their new ID to the session
            $_SESSION['user_id'] = $pdo->lastInsertId();

            // Redirect the user to the to-do app interface
            header("Location: to-do.html");
            exit();
            
        } catch(PDOException $e) {
            // Check if the error is a duplicate email (SQL code 23000)
            if ($e->getCode() == 23000) {
                echo "Error: This email is already registered.";
            } else {
                echo "Error: " . $e->getMessage();
            }
        }
    } else {
        echo "Please fill in all required fields.";
    }
}

// --- LOGIN BLOCK ---
// Check if the login form was submitted
if (isset($_POST['login'])) {
    // Sanitize the email input
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];

    if (!empty($email) && !empty($pass)) {
        // Search the database for a user with this email
        $sql = "SELECT id, name, password FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        // Fetch the user data if found
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify that the user exists and the entered password matches the stored hash
        if ($user && password_verify($pass, $user['password'])) {
            
            // FIX: Save the user's ID to the session to keep them logged in
            $_SESSION['user_id'] = $user['id'];
            
            // Redirect to the to-do app
            header("Location: to-do.html");
            exit();
        } else {
            echo "Invalid email or password.";
        }
    } else {
         echo "Please enter email and password.";
    }
}
?>