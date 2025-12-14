<nav class="navbar">
    <div class="logo">
        <h2>Laundry Management System</h2>
    </div>
    <div class="nav-links">
        <a href="rider_dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="order_details.php"><i class="fas fa-tshirt"></i> All Orders</a>
        <a href="update_status.php"><i class="fas fa-motorcycle"></i> Pending</a>
        <a href="new_order.php"><i class="fas fa-shopping-bag"></i> Ready for Pickup</a>
        <a href="profile.php"><i class="fas fa-profile"></i> Profile</a>
        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<style>
   
     .navbar {
        background:rgb(220, 81, 97);
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
    }
    
    .navbar a {
        color: white;
        text-decoration: none;
        padding: 10px 15px;
        font-size: 16px;
        margin: 0 5px;
    }
    
    .navbar a:hover {
        background:rgb(73, 99, 81);
        border-radius: 5px;
    }
    
    .nav-links {
        display: flex;
    }

    .logout {
        background: red;
        border-radius: 5px;
    }
</style>