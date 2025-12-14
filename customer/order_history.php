<?php
// Include database connection
require '../db_connect.php';
require 'customer_navbar.php';

session_start();

// Get the customer_id from session
$customer_id = $_SESSION['user_id'];

// Fetch all orders (regardless of status) and their associated items
$query = "SELECT o.id, o.order_date, o.status, o.total_price, l.name AS location_name, oi.laundry_item_id, li.name AS item_name, oi.quantity, oi.price
          FROM orders o
          JOIN laundry_locations l ON o.laundry_location_id = l.id
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN laundry_items li ON oi.laundry_item_id = li.id
          WHERE o.customer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($order = $result->fetch_assoc()) {
    $orders[$order['id']]['order_details'] = $order;
    $orders[$order['id']]['items'][] = [
        'item_name' => $order['item_name'],
        'quantity' => $order['quantity'],
        'price' => $order['price']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="customer.css">
    <style>
        /* General Body Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        /* Center the content */
        .orders-section {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h3 {
            text-align: center;
            color: #333;
            font-size: 1.8em;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: rgb(218, 80, 22);
            color: white;
        }

        td {
            background-color: #fafafa;
        }

        td a {
            color: #007bff;
            text-decoration: none;
        }

        td a:hover {
            text-decoration: underline;
        }

        .no-orders {
            text-align: center;
            font-size: 1.2em;
            color: #ff0000;
        }

        /* Nested Item Table Styling */
        .item-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .item-table th, .item-table td {
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }

        .item-table th {
            background-color: #f1f1f1;
        }

    </style>
</head>
<body>

    <div class="orders-section">
        <h3>Your Orders History</h3>

        <?php if (empty($orders)): ?>
            <p class="no-orders">You have no orders yet.</p>
        <?php else: ?>
            <?php foreach ($orders as $order_id => $orderData): ?>
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
                        <tr>
                            <td><?php echo $orderData['order_details']['id']; ?></td>
                            <td><?php echo $orderData['order_details']['order_date']; ?></td>
                            <td><?php echo $orderData['order_details']['status']; ?></td>
                            <td><?php echo $orderData['order_details']['location_name']; ?></td>
                            <td><?php echo number_format($orderData['order_details']['total_price'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Order Items Table -->
                <?php if (!empty($orderData['items'])): ?>
                    <table class="item-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderData['items'] as $item): ?>
                                <tr>
                                    <td><?php echo $item['item_name']; ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo number_format($item['price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <hr>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>
