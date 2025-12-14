<?php
// Include database connection
require '../db_connect.php';
require 'rider_navbar.php';

session_start();

// Get the user_id from session
$user_id = $_SESSION['user_id'];

// Handle pick-up action
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Update the order status to 'Assigned' after it is picked up
    $updateQuery = "UPDATE orders SET status = 'Assigned' WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $order_id);
    $updateStmt->execute();

    // Insert a record into the rider_assignments table to mark this order as assigned to the rider
    $insertQuery = "INSERT INTO rider_assignments (order_id, rider_id, status) VALUES (?, ?, 'Picked Up')";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("ii", $order_id, $user_id);
    $insertStmt->execute();

    // Redirect to refresh the page after picking up
    header("Location: new_order.php");
    exit();
}

// Fetch new orders that are not yet assigned to a rider
$query = "
    SELECT o.id AS order_id, o.order_date, o.status, o.total_price, l.name AS location_name, u.name AS customer_name, p.payment_status,
           GROUP_CONCAT(li.name ORDER BY oi.id ASC) AS laundry_items, 
           GROUP_CONCAT(oi.quantity ORDER BY oi.id ASC) AS quantities
    FROM orders o
    JOIN laundry_locations l ON o.laundry_location_id = l.id
    JOIN users u ON o.customer_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN laundry_items li ON oi.laundry_item_id = li.id
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.status = 'Pending' AND NOT EXISTS (
        SELECT 1 FROM rider_assignments ra WHERE ra.order_id = o.id
    )
    GROUP BY o.id
";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$newOrders = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Orders</title>
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

        .btn-assign {
            background-color: rgb(206, 75, 39);
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn-assign:hover {
            background-color: rgb(174, 64, 33);
        }
    </style>
</head>
<body>

    <div class="orders-section">
        <h3>New Orders</h3>

        <?php if (empty($newOrders)): ?>
            <p>No new orders are available!</p> <!-- Message when no new orders are available -->
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
                        <th>Action</th> <!-- Action column to pick up the order -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($newOrders as $order): ?>
                        <tr>
                            <td><?php echo $order['order_id']; ?></td>
                            <td><?php echo $order['customer_name']; ?></td>
                            <td><?php echo $order['laundry_items']; ?></td>
                            <td><?php echo $order['quantities']; ?></td>
                            <td><?php echo $order['payment_status']; ?></td>
                            <td><?php echo $order['location_name']; ?></td>
                            <td><?php echo number_format($order['total_price'], 2); ?></td>
                            <td>
                                <a href="new_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn-assign">Pick Up</a>
                            </td> <!-- Pick Up button for the rider -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>
