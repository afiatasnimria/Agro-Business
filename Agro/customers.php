<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

switch ($method) {
    case 'GET':
        // Get all customers
        $sql = "SELECT * FROM customers ORDER BY name";
        $result = $conn->query($sql);

        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }

        sendResponse(['success' => true, 'data' => $customers]);
        break;

    case 'POST':
        // Add new customer
        $data = getPostData();

        $sql = "INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $data['name'], $data['phone'], $data['email'], $data['address']);

        if ($stmt->execute()) {
            sendResponse(['success' => true, 'message' => 'Customer added successfully']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to add customer']);
        }
        break;

    case 'PUT':
        // Update customer
        $data = getPostData();

        $sql = "UPDATE customers SET name=?, phone=?, email=?, address=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssi', $data['name'], $data['phone'], $data['email'], $data['address'], $data['id']);

        if ($stmt->execute()) {
            sendResponse(['success' => true, 'message' => 'Customer updated successfully']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to update customer']);
        }
        break;

    case 'DELETE':
        // Delete customer
        $data = getPostData();
        $sql = "DELETE FROM customers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $data['id']);

        if ($stmt->execute()) {
            sendResponse(['success' => true, 'message' => 'Customer deleted successfully']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to delete customer']);
        }
        break;

    default:
        sendResponse(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>