<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: landing.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

switch ($method) {
    case 'GET':
        // Fetch dashboard data
        $dashboard = [];

        // Total sales today
        $sql = "SELECT COALESCE(SUM(total), 0) as total_sales FROM invoices WHERE DATE(date) = CURDATE() AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['total_sales_today'] = $result->fetch_assoc()['total_sales'];

        // Total sales this month
        $sql = "SELECT COALESCE(SUM(total), 0) as total_sales FROM invoices WHERE MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE()) AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['total_sales_month'] = $result->fetch_assoc()['total_sales'];

        // Total customers
        $sql = "SELECT COUNT(*) as total_customers FROM customers";
        $result = $conn->query($sql);
        $dashboard['total_customers'] = $result->fetch_assoc()['total_customers'];

        // Total products
        $sql = "SELECT COUNT(*) as total_products FROM products";
        $result = $conn->query($sql);
        $dashboard['total_products'] = $result->fetch_assoc()['total_products'];

        // Low stock products
        $sql = "SELECT COUNT(*) as low_stock FROM products WHERE stock_qty <= 10";
        $result = $conn->query($sql);
        $dashboard['low_stock_products'] = $result->fetch_assoc()['low_stock'];

        // Recent invoices
        $sql = "SELECT i.*, c.name as customer_name FROM invoices i LEFT JOIN customers c ON i.customer_id = c.id WHERE i.user_id = ? ORDER BY i.date DESC LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['recent_invoices'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['recent_invoices'][] = $row;
        }

        // Monthly sales chart data
        $sql = "SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(total) as sales FROM invoices WHERE date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND user_id = ? GROUP BY DATE_FORMAT(date, '%Y-%m') ORDER BY month";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dashboard['monthly_sales'] = [];
        while ($row = $result->fetch_assoc()) {
            $dashboard['monthly_sales'][] = $row;
        }

        // Output HTML dashboard
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dashboard - <?php echo htmlspecialchars($_SESSION['user']['name']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .card { border: 1px solid #ccc; padding: 20px; margin: 10px; border-radius: 5px; }
                .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
            </style>
        </head>
        <body>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> (<?php echo htmlspecialchars($_SESSION['user']['role_name']); ?>)</h1>
            <div class="grid">
                <div class="card">
                    <h3>Total Sales Today</h3>
                    <p>$<?php echo number_format($dashboard['total_sales_today'], 2); ?></p>
                </div>
                <div class="card">
                    <h3>Total Sales This Month</h3>
                    <p>$<?php echo number_format($dashboard['total_sales_month'], 2); ?></p>
                </div>
                <div class="card">
                    <h3>Total Customers</h3>
                    <p><?php echo $dashboard['total_customers']; ?></p>
                </div>
                <div class="card">
                    <h3>Total Products</h3>
                    <p><?php echo $dashboard['total_products']; ?></p>
                </div>
                <div class="card">
                    <h3>Low Stock Products</h3>
                    <p><?php echo $dashboard['low_stock_products']; ?></p>
                </div>
            </div>
            <h2>Recent Invoices</h2>
            <table>
                <tr>
                    <th>Invoice No</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Paid</th>
                </tr>
                <?php foreach ($dashboard['recent_invoices'] as $invoice): ?>
                <tr>
                    <td><?php echo htmlspecialchars($invoice['invoice_no']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['customer_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($invoice['date']); ?></td>
                    <td>$<?php echo number_format($invoice['total'], 2); ?></td>
                    <td>$<?php echo number_format($invoice['paid_amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <a href="logout.php">Logout</a>
        </body>
        </html>
        <?php
        break;

    default:
        sendResponse(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
