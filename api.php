<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

$host = 'localhost';
$dbname = 'user_auth'; 
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $stmt = $pdo->prepare("SELECT id, task_text FROM tasks WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tasks);
        break;
        
    case 'POST':
        $task_text = $input['task_text'];
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, task_text) VALUES (:user_id, :task_text)");
        $stmt->execute([':user_id' => $user_id, ':task_text' => $task_text]);
        echo json_encode(["success" => "Task added"]);
        break;

    case 'PUT':
        $task_id = $input['id'];
        $task_text = $input['task_text'];
        $stmt = $pdo->prepare("UPDATE tasks SET task_text = :task_text WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':task_text' => $task_text, ':id' => $task_id, ':user_id' => $user_id]);
        echo json_encode(["success" => "Task updated"]);
        break;

    case 'DELETE':
        $task_id = $input['id'];
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $task_id, ':user_id' => $user_id]);
        echo json_encode(["success" => "Task deleted"]);
        break;
}
?>