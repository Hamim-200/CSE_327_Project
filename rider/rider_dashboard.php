<?php
// Include database connection
require '../db_connect.php';
require 'rider_navbar.php';

session_start();

// Get the user_id from session
$user_id = $_SESSION['user_id'];

// Fetch rider's name and email from the users table
$riderQuery = "SELECT name, email FROM users WHERE id = ?";
$riderStmt = $conn->prepare($riderQuery);
$riderStmt->bind_param("i", $user_id);  // bind user_id as an integer
$riderStmt->execute();
$riderResult = $riderStmt->get_result();
$riderDetails = $riderResult->fetch_assoc();
$riderName = $riderDetails['name'];
$riderEmail = $riderDetails['email'];

// Fetch total assigned orders count (regardless of status)
$totalOrdersQuery = "SELECT COUNT(*) AS total_orders
                     FROM rider_assignments ra
                     WHERE ra.rider_id = ?";
$totalOrdersStmt = $conn->prepare($totalOrdersQuery);
$totalOrdersStmt->bind_param("i", $user_id);
$totalOrdersStmt->execute();
$totalOrdersResult = $totalOrdersStmt->get_result();
$totalOrders = $totalOrdersResult->fetch_assoc()['total_orders'];

// Fetch only delivered orders for the rider
$query = "SELECT o.id, o.order_date, o.status, o.total_price, l.name AS location_name, u.name AS customer_name
          FROM orders o
          JOIN laundry_locations l ON o.laundry_location_id = l.id
          JOIN rider_assignments ra ON ra.order_id = o.id
          JOIN users u ON o.customer_id = u.id
          WHERE ra.rider_id = ? AND o.status = 'Delivered'";  // Changed to 'Delivered'
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);  // bind user_id as an integer
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Dashboard</title>
    <link rel="stylesheet" href="rider.css">
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

        .orders-section {
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
            background-color: rgb(255, 0, 76);
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
            <i class="fas fa-shopping-bag"></i>
            <h3>Total Orders</h3>
            <p><?php echo $totalOrders; ?></p> <!-- Total assigned orders count -->
        </div>
        <div class="dashboard-card">
            <i class="fas fa-truck"></i>
            <h3>Delivered Orders</h3> <!-- Changed label from Completed to Delivered -->
            <p><?php echo count($orders); ?></p> <!-- Delivered orders count -->
        </div>
        <div class="dashboard-card">
            <i class="fas fa-cogs"></i>
            <h3>Orders Status</h3>
            <p><?php echo (empty($orders) ? "No order completed" : "Delivered Orders"); ?></p> <!-- Added check for empty orders -->
        </div>
    </div>

    <div class="orders-section">
        <h3>Your Delivered Orders</h3>

        <?php if (empty($orders)): ?>
            <p>No order completed</p> <!-- Message for new riders with no orders -->
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo $order['order_date']; ?></td>
                            <td><?php echo $order['status']; ?></td> <!-- Status: Delivered -->
                            <td><?php echo $order['location_name']; ?></td>
                            <td><?php echo number_format($order['total_price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>
