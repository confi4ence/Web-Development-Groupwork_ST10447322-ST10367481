<?php

// user_dashboard.php - User dashboard
session_start();
require_once 'DBConn.php';
require_once 'includes/functions.php';

require_login();

// Fetch full user details
$stmt = $conn->prepare(
    "SELECT user_id, full_name, email, username, role, status, created_at
     FROM tblUser WHERE user_id = ?"
);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Count clothes
$clothes_result = $conn->query("SELECT COUNT(*) AS cnt FROM tblClothes WHERE status='available'");
$clothes_count  = $clothes_result ? $clothes_result->fetch_assoc()['cnt'] : 0;

$initials = get_initials($user['full_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Pasttime</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="nav-inner">
        <span class="nav-brand">Past<span>times</span></span>
        <nav class="nav-links">
            <a href="user_dashboard.php" class="active">Dashboard</a>
            <a href="clothes.php">Browse Clothes</a>
            <a href="cart.php">Cart</a>
            <a href="logout.php" class="btn-nav-logout">Logout</a>
        </nav>
    </div>
</header>

<div class="page-hero">
    <h1>My Dashboard</h1>
    <p>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?></p>
</div>

<div class="wrapper">

    <!-- User info block -->
    <div class="card" style="margin-top: 28px;">
        <div class="user-info-block">
            <div class="user-avatar"><?php echo $initials; ?></div>
            <div>
                <h2><?php echo htmlspecialchars($user['full_name']); ?> is logged in</h2>
                <p>
                    <span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
                    &nbsp;
                    <span class="badge badge-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span>
                </p>
            </div>
        </div>

        <!-- Stats row -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-num"><?php echo $user['user_id']; ?></div>
                <div class="stat-label">User ID</div>
            </div>
            <div class="stat-card gold">
                <div class="stat-num"><?php echo $clothes_count; ?></div>
                <div class="stat-label">Items Available</div>
            </div>
            <div class="stat-card dark">
                <div class="stat-num"><?php echo date('d M', strtotime($user['created_at'])); ?></div>
                <div class="stat-label">Member Since</div>
            </div>
        </div>
    </div>

    <!-- User details table -->
    <div class="section-head">
        <h2>Account Details</h2>
    </div>

    <div class="card" style="padding: 0;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><strong>User ID</strong></td>     <td><?php echo $user['user_id']; ?></td></tr>
                    <tr><td><strong>Full Name</strong></td>   <td><?php echo htmlspecialchars($user['full_name']); ?></td></tr>
                    <tr><td><strong>Email</strong></td>       <td><?php echo htmlspecialchars($user['email']); ?></td></tr>
                    <tr><td><strong>Username</strong></td>    <td><?php echo htmlspecialchars($user['username']); ?></td></tr>
                    <tr><td><strong>Role</strong></td>        <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td></tr>
                    <tr><td><strong>Status</strong></td>      <td><span class="badge badge-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span></td></tr>
                    <tr><td><strong>Registered</strong></td>  <td><?php echo date('d F Y, H:i', strtotime($user['created_at'])); ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex-gap mt-4">
        <a href="clothes.php"  class="btn btn-primary">Browse Clothes</a>
        <a href="cart.php"     class="btn btn-gold">View Cart</a>
        <a href="logout.php"   class="btn btn-dark">Logout</a>
    </div>

</div>

<footer>
    &copy; <?php echo date('Y'); ?> <span>Pasttime</span> Clothing Store
</footer>

</body>
</html>
