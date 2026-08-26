<?php
// This MUST be the very first thing in the file
session_start();

$host = 'localhost';
$dbname = 'user_auth'; 
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// --- REGISTRATION BLOCK ---
if (isset($_POST['register'])) {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];

    if (!empty($name) && !empty($email) && !empty($pass)) {
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashed_password
            ]);

            // FIX: Log the user in immediately after registering
            $_SESSION['user_id'] = $pdo->lastInsertId();

            // Redirect to the to-do app
            header("Location: to-do.html");
            exit();
            
        } catch(PDOException $e) {
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
if (isset($_POST['login'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];

    if (!empty($email) && !empty($pass)) {
        
        $sql = "SELECT id, name, password FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password'])) {
            
            // FIX: Save the user's ID to the session
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