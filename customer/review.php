<?php
// Include database connection
require '../db_connect.php';
require 'customer_navbar.php';

session_start();

// Get customer ID from session
$customer_id = $_SESSION['user_id'];

// Fetch all reviews from the reviews table along with order details
$query = "SELECT r.id, r.rating, r.comment, r.created_at, o.id AS order_id, u.name AS customer_name 
          FROM reviews r 
          JOIN orders o ON r.order_id = o.id 
          JOIN users u ON o.customer_id = u.id";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$reviews = $result->fetch_all(MYSQLI_ASSOC);

// Handle form submission for a new review
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Insert the review into the database
    $insert_query = "INSERT INTO reviews (order_id, customer_id, rating, comment, created_at) 
                     VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiis", $order_id, $customer_id, $rating, $comment);
    $stmt->execute();

    // Redirect back to the same page after submitting
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Reviews & Add Review</title>
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

        .container {
            width: 80%;
            margin: 0 auto;
        }

        .reviews-section, .review-form-section {
            padding: 20px;
            background-color: #fff;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .reviews-section h3, .review-form-section h3 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .review-form-section {
            background-color: #eaeaea;
        }

        .review-form input, .review-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            box-sizing: border-box;
            font-size: 16px;
        }

        .review-form button {
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
        }

        .review-form button:hover {
            background-color: #0056b3;
        }

        .message {
            color: green;
            font-size: 18px;
            margin-top: 20px;
            text-align: center;
        }

        .review {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        .review-rating {
            font-weight: bold;
            color: #ff9800;
            font-size: 20px;
        }

        .review-comment {
            font-size: 16px;
            margin-top: 10px;
        }

        .review-date {
            color: #888;
            font-size: 14px;
            margin-top: 10px;
        }

        .customer-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Add Review Form -->
    <div class="review-form-section">
        <h3>Add a Review for Your Orders</h3>

        <?php if (isset($_GET['review_added'])): ?>
            <div class="message">Thanks for your review!</div>
        <?php endif; ?>

        <!-- Form to submit review -->
        <form class="review-form" method="POST" action="">
            <label for="order_id">Select Your Order:</label>
            <select name="order_id" required>
                <?php
                // Fetch the customer's orders to display in the dropdown
                $order_query = "SELECT id, order_date FROM orders WHERE customer_id = ?";
                $stmt = $conn->prepare($order_query);
                $stmt->bind_param("i", $customer_id);
                $stmt->execute();
                $order_result = $stmt->get_result();
                while ($order = $order_result->fetch_assoc()) {
                    echo "<option value='{$order['id']}'>Order #{$order['id']} - {$order['order_date']}</option>";
                }
                ?>
            </select>

            <label for="rating">Rating (1-5):</label>
            <input type="number" name="rating" min="1" max="5" required>

            <label for="comment">Comment:</label>
            <textarea name="comment" rows="4" required></textarea>

            <button type="submit" name="submit_review">Submit Review</button>
        </form>
    </div>

    <!-- Display All Reviews -->
    <div class="reviews-section">
        <h3>All Customer Reviews</h3>

        <?php if (empty($reviews)): ?>
            <p>No reviews available.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <p class="customer-name">Customer: <?php echo $review['customer_name']; ?></p>
                    <p class="review-rating">Rating: <?php echo $review['rating']; ?>/5</p>
                    <p class="review-comment"><?php echo $review['comment']; ?></p>
                    <p class="review-date">Reviewed on: <?php echo $review['created_at']; ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
