<?php
// Include database connection
require '../db_connect.php';
require 'customer_navbar.php';

session_start();

// Get the customer_id from session
$customer_id = $_SESSION['user_id'];

// Fetch all available laundry items
$laundryQuery = "SELECT id, name FROM laundry_items";
$laundryStmt = $conn->prepare($laundryQuery);
$laundryStmt->execute();
$laundryResult = $laundryStmt->get_result();
$laundryItems = $laundryResult->fetch_all(MYSQLI_ASSOC);

// Fetch all laundry locations
$locationQuery = "SELECT id, name FROM laundry_locations";
$locationStmt = $conn->prepare($locationQuery);
$locationStmt->execute();
$locationResult = $locationStmt->get_result();
$laundryLocations = $locationResult->fetch_all(MYSQLI_ASSOC);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $laundry_item_ids = $_POST['laundry_item_id']; // Array of item IDs
    $quantities = $_POST['quantity']; // Array of quantities
    $laundry_location_id = $_POST['laundry_location_id'];
    $order_date = $_POST['order_date'];
    $payment_method = $_POST['payment_method']; // Payment method selected (Cash, Card, Online)

    // Calculate total price by looping through items
    $total_price = 0;
    foreach ($laundry_item_ids as $index => $laundry_item_id) {
        $quantity = $quantities[$index];
        // Fetch price for the laundry item
        $laundryItemQuery = "SELECT price FROM laundry_items WHERE id = ?";
        $laundryItemStmt = $conn->prepare($laundryItemQuery);
        $laundryItemStmt->bind_param("i", $laundry_item_id);
        $laundryItemStmt->execute();
        $laundryItemResult = $laundryItemStmt->get_result();
        $item = $laundryItemResult->fetch_assoc();
        $total_price += $item['price'] * $quantity;
    }

    // Apply extra charges if payment method is Cash
    if ($payment_method == 'Cash') {
        $total_price += 10; // Adding 10 extra for cash payment
    }

    // Insert the order into the 'orders' table
    $orderQuery = "INSERT INTO orders (customer_id, laundry_location_id, total_price, order_date, status)
                   VALUES (?, ?, ?, ?, 'Pending')";
    $orderStmt = $conn->prepare($orderQuery);
    $orderStmt->bind_param("iiis", $customer_id, $laundry_location_id, $total_price, $order_date);
    $orderStmt->execute();

    // Get the last inserted order ID
    $order_id = $conn->insert_id;

    // Insert order items into the 'order_items' table
    foreach ($laundry_item_ids as $index => $laundry_item_id) {
        $quantity = $quantities[$index];
        // Fetch the price for the item
        $laundryItemQuery = "SELECT price FROM laundry_items WHERE id = ?";
        $laundryItemStmt = $conn->prepare($laundryItemQuery);
        $laundryItemStmt->bind_param("i", $laundry_item_id);
        $laundryItemStmt->execute();
        $laundryItemResult = $laundryItemStmt->get_result();
        $item = $laundryItemResult->fetch_assoc();

        // Insert each laundry item into the 'order_items' table
        $orderItemQuery = "INSERT INTO order_items (order_id, laundry_item_id, quantity, price)
                           VALUES (?, ?, ?, ?)";
        $orderItemStmt = $conn->prepare($orderItemQuery);
        $orderItemStmt->bind_param("iiid", $order_id, $laundry_item_id, $quantity, $item['price']);
        $orderItemStmt->execute();
    }

    // Insert payment details into 'payments' table
    $paymentQuery = "INSERT INTO payments (order_id, customer_id, amount, payment_method, payment_status, transaction_date)
                     VALUES (?, ?, ?, ?, 'Pending', NOW())";
    $paymentStmt = $conn->prepare($paymentQuery);
    $paymentStmt->bind_param("iiis", $order_id, $customer_id, $total_price, $payment_method);
    $paymentStmt->execute();

    // Success message
    $success_message = "Your order has been successfully placed!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place New Order</title>
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

        .order-form {
            width: 50%;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .order-form h2 {
            text-align: center;
        }

        .order-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .order-form select, .order-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .order-form button {
            width: 100%;
            padding: 12px;
            background-color: rgb(206, 75, 39);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .order-form button:hover {
            background-color: rgb(174, 64, 33);
        }

        .success-message {
            text-align: center;
            color: green;
            font-size: 18px;
        }

        .item-section {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="order-form">
        <h2>Place New Order</h2>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form action="place_order.php" method="POST">
            <!-- Laundry Location Selection -->
            <label for="laundry_location_id">Select Laundry Location</label>
            <select name="laundry_location_id" required>
                <option value="">Select Location</option>
                <?php foreach ($laundryLocations as $location): ?>
                    <option value="<?php echo $location['id']; ?>"><?php echo $location['name']; ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Laundry Items Section (Multiple Items) -->
            <div id="item-section">
                <div class="item-section">
                    <label for="laundry_item_id[]">Select Laundry Item</label>
                    <select name="laundry_item_id[]" required>
                        <option value="">Select Item</option>
                        <?php foreach ($laundryItems as $item): ?>
                            <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="quantity[]">Quantity</label>
                    <input type="number" name="quantity[]" min="1" required>
                </div>
            </div>

            <!-- Add more items button -->
            <button type="button" id="add-item-btn">Add More Items</button>

            <!-- Order Date Input -->
            <label for="order_date">Order Date</label>
            <input type="date" name="order_date" required>

            <!-- Payment Method Selection -->
            <label for="payment_method">Payment Method</label>
            <select name="payment_method" required>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Online">Online</option>
            </select>

            <button type="submit">Place Order</button>
        </form>
    </div>

    <script>
        // Add more items dynamically
        document.getElementById("add-item-btn").addEventListener("click", function() {
            var newItemSection = document.createElement("div");
            newItemSection.classList.add("item-section");

            newItemSection.innerHTML = `
                <label for="laundry_item_id[]">Select Laundry Item</label>
                <select name="laundry_item_id[]" required>
                    <option value="">Select Item</option>
                    <?php foreach ($laundryItems as $item): ?>
                        <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="quantity[]">Quantity</label>
                <input type="number" name="quantity[]" min="1" required>
            `;

            document.getElementById("item-section").appendChild(newItemSection);
        });
    </script>

</body>
</html>
