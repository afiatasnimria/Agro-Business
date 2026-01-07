<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

switch ($method) {
    case 'GET':
        // Get all products
        $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name";
        $result = $conn->query($sql);

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        sendResponse(['success' => true, 'data' => $products]);
        break;

    case 'POST':
        // Add or update product
        $data = getPostData();

        if (isset($data['id'])) {
            // Update existing product
            $sql = "UPDATE products SET name=?, category_id=?, selling_price=?, stock_qty=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('siddi', $data['name'], $data['category_id'], $data['selling_price'], $data['stock_qty'], $data['id']);
        } else {
            // Add new product
            $sql = "INSERT INTO products (name, category_id, selling_price, stock_qty) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sidd', $data['name'], $data['category_id'], $data['selling_price'], $data['stock_qty']);
        }

        if ($stmt->execute()) {
            sendResponse(['success' => true, 'message' => 'Product saved successfully']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to save product']);
        }
        break;

    case 'DELETE':
        // Delete product
        $data = getPostData();
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $data['id']);

        if ($stmt->execute()) {
            sendResponse(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to delete product']);
        }
        break;

    default:
        sendResponse(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>