<?php
// Include database connection
require '../db_connect.php';
require 'customer_navbar.php';

session_start();

// Get the customer_id from session
$customer_id = $_SESSION['user_id'];

// Fetch customer information
$query = "SELECT name, email, phone, address FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

// Fetch total number of orders placed by the customer
$orderQuery = "SELECT COUNT(*) AS total_orders FROM orders WHERE customer_id = ?";
$orderStmt = $conn->prepare($orderQuery);
$orderStmt->bind_param("i", $customer_id);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$totalOrders = $orderResult->fetch_assoc()['total_orders'];

// Fetch active orders (orders in progress or pending)
$activeOrdersQuery = "SELECT COUNT(*) AS active_orders FROM orders WHERE customer_id = ? AND status != 'Completed'";
$activeOrdersStmt = $conn->prepare($activeOrdersQuery);
$activeOrdersStmt->bind_param("i", $customer_id);
$activeOrdersStmt->execute();
$activeOrdersResult = $activeOrdersStmt->get_result();
$activeOrders = $activeOrdersResult->fetch_assoc()['active_orders'];

// Fetch all laundry items and their prices
$laundryQuery = "SELECT id, name, price FROM laundry_items";
$laundryStmt = $conn->prepare($laundryQuery);
$laundryStmt->execute();
$laundryResult = $laundryStmt->get_result();
$laundryItems = $laundryResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="customer.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        /* Body styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .dashboard-container {
            display: flex;
            justify-content: space-around;
            padding: 20px;
            margin-top: 30px;
        }

        .dashboard-card {
            background: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 30%;
        }

        .dashboard-card i {
            font-size: 40px;
            color: #007bff;
        }

        .items-section {
            padding: 20px;
            text-align: center;
        }

        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: rgb(206, 75, 39);
            color: white;
        }

        td a {
            color: #007bff;
            text-decoration: none;
        }

        td a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Main Content -->
    <div class="dashboard-container">
        <div class="dashboard-card">
            <i class="fas fa-user"></i>
            <h3>Customer</h3>
            <p><?php echo $customer['name']; ?></p>
        </div>
        <div class="dashboard-card">
            <i class="fas fa-shopping-bag"></i>
            <h3>Total Orders</h3>
            <p><?php echo $totalOrders; ?></p>
        </div>
        <div class="dashboard-card">
            <i class="fas fa-clock"></i>
            <h3>Active Orders</h3>
            <p><?php echo $activeOrders; ?></p>
        </div>
    </div>

    <!-- Laundry Items Section -->
    <div class="items-section">
        <h3>Available Laundry Items</h3>

        <?php if (empty($laundryItems)): ?>
            <p>No laundry items available at the moment.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laundryItems as $item): ?>
                        <tr>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>
