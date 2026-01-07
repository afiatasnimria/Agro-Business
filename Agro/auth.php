<?php
session_start();
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

switch ($method) {
    case 'POST':
        $data = getPostData();
        $action = $data['action'] ?? '';

        switch ($action) {
            case 'login':
                // Login
                $email = $data['email'];
                $sql = "SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    if (password_verify($data['password'], $user['password_hash'])) {
                        // Remove password_hash from response
                        unset($user['password_hash']);
                        $_SESSION['user'] = $user;
                        sendResponse(['success' => true, 'user' => $user]);
                    } else {
                        sendResponse(['success' => false, 'message' => 'Invalid email or password']);
                    }
                } else {
                    sendResponse(['success' => false, 'message' => 'Invalid email or password']);
                }
                break;

            case 'register':
                // Check if email already exists
                $email = $data['email'];
                $sql = "SELECT id FROM users WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $email);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    sendResponse(['success' => false, 'message' => 'Email already registered']);
                    break;
                }

                // Register new user
                $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
                $full_name = $data['full_name'];
                $phone = $data['phone'] ?? '';
                $sql = "INSERT INTO users (role_id, name, email, password_hash, phone) VALUES (2, ?, ?, ?, ?)"; // Default role 2 (Staff)
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssss', $full_name, $email, $hashed_password, $phone);

                if ($stmt->execute()) {
                    sendResponse(['success' => true, 'message' => 'User registered successfully']);
                } else {
                    sendResponse(['success' => false, 'message' => 'Failed to register user']);
                }
                break;

            default:
                sendResponse(['success' => false, 'message' => 'Invalid action']);
        }
        break;

    default:
        sendResponse(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>