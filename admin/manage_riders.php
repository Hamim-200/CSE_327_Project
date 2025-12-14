<?php include 'navbar.php'; ?>
<?php
include '../db_connect.php';

// Handle deleting a rider
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id = '$delete_id' AND role = 'Rider'");
    header("Location: manage_riders.php");
}

// Handle updating a rider's details including status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_rider'])) {
    $rider_id = $_POST['rider_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $status = $_POST['status'];  // Capture the status from the form

    // Prepare the update query
    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ?, approved = ? WHERE id = ? AND role = 'Rider'");
    $stmt->bind_param("sssii", $name, $phone, $address, $status, $rider_id);
    
    // Execute the query and redirect
    $stmt->execute();
    $stmt->close();
    header("Location: manage_riders.php");
    exit;
}

// Handle approving a rider
if (isset($_GET['approve_id'])) {
    $approve_id = $_GET['approve_id'];
    $conn->query("UPDATE users SET approved = 1 WHERE id = '$approve_id' AND role = 'Rider'");
    header("Location: manage_riders.php");
}

// Fetch riders based on search query
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $conn->real_escape_string($_GET['search']);
    $search_query = " AND (name LIKE '%$search_term%' OR phone LIKE '%$search_term%')";
}

// Modified query to avoid syntax issues when no search term is provided
$riders = $conn->query("SELECT * FROM users WHERE role = 'Rider' $search_query");

// Check if there are any riders after search
$rider_found = $riders->num_rows > 0;

// Fetch assigned orders with item names and status
$orders = $conn->query("
    SELECT o.id AS order_id, 
           o.customer_id, 
           u.name AS customer_name, 
           GROUP_CONCAT(li.name) AS item_names, 
           ra.status, 
           r.name AS rider_name
    FROM orders o
    LEFT JOIN users u ON o.customer_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN laundry_items li ON oi.laundry_item_id = li.id
    LEFT JOIN rider_assignments ra ON o.id = ra.order_id
    LEFT JOIN users r ON ra.rider_id = r.id
    GROUP BY o.id
");

?>

<link rel="stylesheet" href="admin.css">

<h2>Manage Riders</h2>

<!-- Search Form -->
<form method="get" class="search-form">
    <input type="text" name="search" placeholder="Search by name or phone" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
    <button type="submit" class="btn-primary">Search</button>
</form>

<div class="container">
    <?php if (isset($_GET['search']) && !$rider_found): ?>
        <p style="color: red;">Rider not found</p>
    <?php endif; ?>

    <table class="table">
        <tr><th>ID</th><th>Name</th><th>Phone</th><th>Address</th><th>Status</th><th>Actions</th></tr>
        <?php while ($row = $riders->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['phone']; ?></td>
            <td><?php echo $row['address']; ?></td>
            <td><?php echo $row['approved'] ? 'Approved' : 'Pending'; ?></td>
            <td>
                <!-- Approve button -->
                <?php if (!$row['approved']) { ?>
                    <a href="?approve_id=<?php echo $row['id']; ?>" class="btn-success">✔ Approve</a>
                <?php } ?>
                <!-- Delete button -->
                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-danger">🗑 Remove</a>
                <!-- Update button -->
                <button class="btn-warning" onclick="openUpdateForm(<?php echo $row['id']; ?>, '<?php echo $row['name']; ?>', '<?php echo $row['phone']; ?>', '<?php echo $row['address']; ?>', <?php echo $row['approved']; ?>)">✎ Update</button>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<!-- Update Form -->
<div id="updateForm" class="update-form">
    <form method="post" action="manage_riders.php">
        <input type="hidden" name="rider_id" id="update-rider-id">
        <div class="form-group">
            <label for="update-name">Name:</label>
            <input type="text" name="name" id="update-name">
        </div>
        <div class="form-group">
            <label for="update-phone">Phone:</label>
            <input type="text" name="phone" id="update-phone">
        </div>
        <div class="form-group">
            <label for="update-address">Address:</label>
            <input type="text" name="address" id="update-address">
        </div>
        <div class="form-group">
            <label for="update-status">Status:</label>
            <select name="status" id="update-status">
                <option value="1">Approved</option>
                <option value="0">Pending</option>
            </select>
        </div>
        <button type="submit" name="update_rider" class="btn-primary">Update Rider</button>
        <button type="button" class="btn-secondary" onclick="closeUpdateForm()">Close</button>
    </form>
</div>

<script>
    // Function to open the update form and populate it with the rider's details
    function openUpdateForm(id, name, phone, address, status) {
        document.getElementById('update-rider-id').value = id;
        document.getElementById('update-name').value = name;
        document.getElementById('update-phone').value = phone;
        document.getElementById('update-address').value = address;
        document.getElementById('update-status').value = status;  // Set the status in the dropdown
        document.getElementById('updateForm').style.display = 'block';
    }

    // Function to close the update form
    function closeUpdateForm() {
        document.getElementById('updateForm').style.display = 'none';
    }
</script>

<h2>Order Status</h2>
<div class="container">
    <table class="table">
        <tr><th>Order ID</th><th>Customer Name</th><th>Ordered Items</th><th>Assigned Rider</th><th>Status</th></tr>
        <?php while ($row = $orders->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['order_id']; ?></td>
            <td><?php echo $row['customer_name']; ?></td>
            <td><?php echo $row['item_names']; ?></td>
            <td><?php echo $row['rider_name']; ?></td>
            <td><?php echo $row['status']; ?></td>
        </tr>
        <?php } ?>
    </table>
</div>

<style>
    /* Basic Styling for the Update Form */
    .update-form {
        display: none;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }

    .form-group input, .form-group select {
        padding: 10px;
        width: 100%;
        margin-bottom: 10px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        text-decoration: none;
    }

    .btn-secondary {
        background-color: gray;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        text-decoration: none;
    }

    .btn-success {
        background-color: green;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        text-decoration: none;
    }

    .btn-danger {
        background-color: red;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        text-decoration: none;
    }

    .btn-warning {
        background-color: orange;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        text-decoration: none;
    }
</style>
