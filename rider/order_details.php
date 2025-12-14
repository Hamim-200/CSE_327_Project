<?php
// Include database connection
require '../db_connect.php';
require 'rider_navbar.php';

session_start();

// Get the user_id from session
$user_id = $_SESSION['user_id'];

// Fetch all orders assigned to the rider, including payment status
$query = "
    SELECT o.id AS order_id, o.order_date, o.status AS order_status, o.total_price, l.name AS location_name, u.name AS customer_name, 
           p.payment_status,
           GROUP_CONCAT(li.name ORDER BY oi.id ASC) AS laundry_items, 
           GROUP_CONCAT(oi.quantity ORDER BY oi.id ASC) AS quantities, ra.status AS rider_status
    FROM orders o
    JOIN laundry_locations l ON o.laundry_location_id = l.id
    JOIN users u ON o.customer_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN laundry_items li ON oi.laundry_item_id = li.id
    LEFT JOIN rider_assignments ra ON o.id = ra.order_id
    LEFT JOIN payments p ON o.id = p.order_id  -- Left join to payments table
    WHERE ra.rider_id = ?
    GROUP BY o.id
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$assignedOrders = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Assigned Orders</title>
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

        .status-label {
            padding: 3px 6px;
            color: white;
            border-radius: 4px;
        }

        .status-pending {
            background-color: orange;
        }

        .status-picked-up {
            background-color: blue;
        }

        .status-delivered {
            background-color: green;
        }
    </style>
</head>
<body>

    <div class="orders-section">
        <h3>All Orders Assigned to You</h3>

        <?php if (empty($assignedOrders)): ?>
            <p>No orders assigned to you!</p> <!-- Message when no orders are available -->
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Laundry Items</th>
                        <th>Quantity</th>
                        <th>Payment Status</th>
                        <th>Location</th>
                        <th>Total Price</th>
                        <th>Order Status</th> <!-- Order Status column -->
                        <th>Rider Status</th> <!-- Rider Status column -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignedOrders as $order): ?>
                        <tr>
                            <td><?php echo $order['order_id']; ?></td>
                            <td><?php echo $order['customer_name']; ?></td>
                            <td><?php echo $order['laundry_items']; ?></td>
                            <td><?php echo $order['quantities']; ?></td>
                            <td><?php echo isset($order['payment_status']) ? $order['payment_status'] : 'Pending'; ?></td>
                            <td><?php echo $order['location_name']; ?></td>
                            <td><?php echo number_format($order['total_price'], 2); ?></td>

                            <!-- Display Order Status with color coding -->
                            <td>
                                <?php 
                                    $order_status_class = '';
                                    switch ($order['order_status']) {
                                        case 'Pending':
                                            $order_status_class = 'status-pending';
                                            break;
                                        case 'Assigned':
                                            $order_status_class = 'status-pending';
                                            break;
                                        case 'Picked Up':
                                            $order_status_class = 'status-picked-up';
                                            break;
                                        case 'Delivered':
                                            $order_status_class = 'status-delivered';
                                            break;
                                    }
                                ?>
                                <span class="status-label <?php echo $order_status_class; ?>"><?php echo $order['order_status']; ?></span>
                            </td>

                            <!-- Display Rider Status with color coding -->
                            <td>
                                <?php 
                                    $rider_status_class = '';
                                    switch ($order['rider_status']) {
                                        case 'Assigned':
                                            $rider_status_class = 'status-pending';
                                            break;
                                        case 'Picked Up':
                                            $rider_status_class = 'status-picked-up';
                                            break;
                                        case 'Delivered':
                                            $rider_status_class = 'status-delivered';
                                            break;
                                    }
                                ?>
                                <span class="status-label <?php echo $rider_status_class; ?>"><?php echo $order['rider_status']; ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>
