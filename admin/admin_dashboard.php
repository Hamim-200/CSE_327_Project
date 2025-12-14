<?php include 'navbar.php'; ?>
<?php include '../db_connect.php'; ?>

<?php
// Fetch total earnings for completed transactions
$sql_earnings = "SELECT SUM(p.amount) AS total_earnings 
                 FROM payments p
                 WHERE p.payment_status = 'Completed'";
$result_earnings = $conn->query($sql_earnings);
$total_earnings = 0;
if ($result_earnings->num_rows > 0) {
    $row = $result_earnings->fetch_assoc();
    $total_earnings = $row['total_earnings'];
}

// Fetch total orders
$sql_orders = "SELECT COUNT(*) AS total_orders FROM orders";
$result_orders = $conn->query($sql_orders);
$total_orders = 0;
if ($result_orders->num_rows > 0) {
    $row = $result_orders->fetch_assoc();
    $total_orders = $row['total_orders'];
}

// Fetch total customers
$sql_customers = "SELECT COUNT(*) AS total_customers FROM users WHERE role = 'Customer'";
$result_customers = $conn->query($sql_customers);
$total_customers = 0;
if ($result_customers->num_rows > 0) {
    $row = $result_customers->fetch_assoc();
    $total_customers = $row['total_customers'];
}

// Fetch laundry items with prices
$sql_items = "SELECT name, price FROM laundry_items";
$result_items = $conn->query($sql_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-card">
            <i class="fas fa-dollar-sign"></i>
            <h3>Total Earnings</h3>
            <p>৳ <?php echo number_format($total_earnings, 2); ?></p>
        </div>
        <div class="dashboard-card">
            <i class="fas fa-shopping-bag"></i>
            <h3>Total Orders</h3>
            <p><?php echo $total_orders; ?></p>
        </div>
        <div class="dashboard-card">
            <i class="fas fa-users"></i>
            <h3>Total Customers</h3>
            <p><?php echo $total_customers; ?></p>
        </div>
    </div>

    <div class="laundry-items">
        <h3>Laundry Items & Prices</h3>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Price (৳)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_items->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td>৳ <?php echo number_format($row['price'], 2); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html> 

<style>
    .dashboard-container {
        display: flex;
        justify-content: space-around;
        padding: 20px;
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
    
    .laundry-items {
        padding: 20px;
        text-align: center;
    }
    
    table {
        width: 50%;
        margin: 0 auto;
        border-collapse: collapse;
    }
    
    th, td {
        padding: 10px;
        border: 1px solid #ddd;
    }
    
    th {
        background-color: #007bff;
        color: white;
    }
</style>
