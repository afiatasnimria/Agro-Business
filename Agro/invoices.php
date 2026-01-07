<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

switch ($method) {
    case 'GET':
        // Get all invoices with customer info
        $sql = "SELECT i.*, c.name as customer_name FROM invoices i LEFT JOIN customers c ON i.customer_id = c.id ORDER BY i.date DESC";
        $result = $conn->query($sql);

        $invoices = [];
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }

        sendResponse(['success' => true, 'data' => $invoices]);
        break;

    case 'POST':
        // Create invoice
        $data = getPostData();

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert invoice
            $sql = "INSERT INTO invoices (invoice_no, customer_id, user_id, date, subtotal, discount, tax, total, paid_amount, notes)
                    VALUES (?, ?, 1, CURDATE(), ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sddddddds',
                $data['invoice_no'], $data['customer_id'], $data['subtotal'],
                $data['discount'], $data['tax'], $data['total'],
                $data['paid_amount'], $data['notes']);
            $stmt->execute();
            $invoice_id = $conn->insert_id;

            // Insert invoice items and update stock
            foreach ($data['items'] as $item) {
                // Insert item
                $sql = "INSERT INTO invoice_items (invoice_id, product_id, description, qty, unit_price, discount)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('iisddd', $invoice_id, $item['product_id'], $item['description'],
                    $item['qty'], $item['unit_price'], $item['discount']);
                $stmt->execute();

                // Update product stock
                $sql = "UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('di', $item['qty'], $item['product_id']);
                $stmt->execute();

                // Log stock change
                $sql = "INSERT INTO stock_logs (product_id, change_qty, reason, reference, created_by)
                        VALUES (?, ?, 'sale', ?, 1)";
                $stmt = $conn->prepare($sql);
                $change_qty = -$item['qty'];
                $stmt->bind_param('dss', $item['product_id'], $change_qty, $data['invoice_no']);
                $stmt->execute();
            }

            $conn->commit();
            sendResponse(['success' => true, 'message' => 'Invoice created successfully', 'invoice_id' => $invoice_id]);

        } catch (Exception $e) {
            $conn->rollback();
            sendResponse(['success' => false, 'message' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
        break;

    default:
        sendResponse(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>