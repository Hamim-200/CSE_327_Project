<?php 
include '../db_connect.php'; 
include 'rider_navbar.php'; 

session_start();

// Get the user_id from session
$user_id = $_SESSION['user_id'];

// Fetch pending orders assigned to the rider
$query = "SELECT o.id, o.order_date, o.deadline, 
                 l.name AS location_name, u.name AS customer_name, 
                 u.phone AS customer_phone, u.address AS customer_address, 
                 ra.status AS rider_status
          FROM orders o
          JOIN laundry_locations l ON o.laundry_location_id = l.id
          JOIN users u ON o.customer_id = u.id
          JOIN rider_assignments ra ON o.id = ra.order_id
          WHERE ra.rider_id = ? AND o.status != 'Delivered'"; // Only pending orders
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    // Handle the status change and set the appropriate timestamps
    if ($new_status == 'Picked Up') {
        $pickup_time = date('Y-m-d H:i:s');
        $delivery_time = null;
    } elseif ($new_status == 'Delivered') {
        $pickup_time = date('Y-m-d H:i:s'); // Keep pickup time as now
        $delivery_time = date('Y-m-d H:i:s');
    }

    // Update the rider assignment status and timestamps
    $updateQuery = "UPDATE rider_assignments 
                    SET status = ?, pickup_time = ?, delivery_time = ? 
                    WHERE order_id = ? AND rider_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssiii", $new_status, $pickup_time, $delivery_time, $order_id, $user_id);
    $updateStmt->execute();

    // Update the order status to 'Completed' when marked as 'Delivered'
    $updateOrderQuery = "UPDATE orders SET status = 'Completed' WHERE id = ?";
    $updateOrderStmt = $conn->prepare($updateOrderQuery);
    $updateOrderStmt->bind_param("i", $order_id);
    $updateOrderStmt->execute();

    // Redirect to reload the page and show updated orders
    header('Location: update_status.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding-top: 50px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: rgb(223, 33, 43);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        form select, form button {
            padding: 5px;
            font-size: 16px;
        }

        form {
            display: inline;
        }

        td form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
        }

        td form button:hover {
            background-color: rgb(223, 33, 43);
        }

        td select {
            padding: 5px;
            border-radius: 5px;
        }

    </style>
</head>
<body>

    <div class="container">
        <h1>Pending Orders for Delivery</h1>

        <table>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Order Date</th>
                <th>Deadline</th>
                <th>Action</th>
            </tr>
            
            <?php while ($order = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo $order['customer_name']; ?></td>
                    <td><?php echo $order['customer_phone']; ?></td>
                    <td><?php echo $order['customer_address']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo $order['deadline']; ?></td>
                    <td>
                        <!-- Form to change status -->
                        <form method="POST" action="">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status">
                                <option value="Picked Up" <?php echo ($order['rider_status'] == 'Picked Up') ? 'selected' : ''; ?>>Picked Up</option>
                                <option value="Delivered" <?php echo ($order['rider_status'] == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                            <button type="submit">Update Status</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>
</html>
