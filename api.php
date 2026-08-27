<?php
// Start the session to identify the user making the request
session_start();
// Tell the browser to expect JSON data in response
header('Content-Type: application/json');

// Security check: Verify the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit(); // Stop executing if not logged in
}

// Store the logged-in user's ID for use in database queries
$user_id = $_SESSION['user_id'];

// Database connection credentials
$host = 'localhost';
$dbname = 'user_auth'; 
$username = 'root'; 
$password = ''; 

// Attempt to connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Determine what type of action (GET, POST, PUT, DELETE) the front-end is requesting
$method = $_SERVER['REQUEST_METHOD'];
// Decode the incoming JSON data sent from JavaScript
$input = json_decode(file_get_contents('php://input'), true);

// Execute the corresponding database action based on the request method
switch ($method) {
    case 'GET':
        // Fetch all tasks associated with this specific user
        $stmt = $pdo->prepare("SELECT id, task_text FROM tasks WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tasks); // Send tasks back to JavaScript
        break;
        
    case 'POST':
        // Insert a new task into the database for this user
        $task_text = $input['task_text'];
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, task_text) VALUES (:user_id, :task_text)");
        $stmt->execute([':user_id' => $user_id, ':task_text' => $task_text]);
        echo json_encode(["success" => "Task added"]);
        break;

    case 'PUT':
        // Update the text of an existing task ensuring it belongs to the user
        $task_id = $input['id'];
        $task_text = $input['task_text'];
        $stmt = $pdo->prepare("UPDATE tasks SET task_text = :task_text WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':task_text' => $task_text, ':id' => $task_id, ':user_id' => $user_id]);
        echo json_encode(["success" => "Task updated"]);
        break;

    case 'DELETE':
        // Remove a specific task belonging to the user
        $task_id = $input['id'];
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $task_id, ':user_id' => $user_id]);
        echo json_encode(["success" => "Task deleted"]);
        break;
}
?>