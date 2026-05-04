<?php

// cart.php - View cart contents (session-based)

session_start();
require_once 'includes/functions.php';

// Allow both logged-in users AND admins
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: /pastimes/login.php");
    exit();
}

$cart  = $_SESSION['cart'] ?? [];
$total = array_sum(array_column($cart, 'price'));

// Remove item
if (isset($_GET['remove'])) {
    $idx = (int)$_GET['remove'];
    if (isset($cart[$idx])) {
        array_splice($_SESSION['cart'], $idx, 1);
        header("Location: /pastimes/cart.php");
        exit();
    }
}

// Clear cart
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: /pastimes/cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart — Pasttime</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="nav-inner">
        <span class="nav-brand">Past<span>times</span></span>
        <nav class="nav-links">
            <?php if (isset($_SESSION['admin_id'])): ?>
                <a href="/pastimes/admin_dashboard.php">Admin Dashboard</a>
            <?php else: ?>
                <a href="/pastimes/user_dashboard.php">Dashboard</a>
            <?php endif; ?>
            <a href="/pastimes/clothes.php">Browse</a>
            <a href="/pastimes/cart.php" class="active">Cart <?php if (count($cart) > 0): ?>(<?php echo count($cart); ?>)<?php endif; ?></a>
            <a href="/pastimes/logout.php" class="btn-nav-logout">Logout</a>
        </nav>
    </div>
</header>

<div class="page-hero">
    <h1>My Cart</h1>
    <p><?php echo count($cart); ?> item<?php echo count($cart) !== 1 ? 's' : ''; ?> in your cart</p>
</div>

<div class="wrapper">

    <?php if (empty($cart)): ?>
        <div class="msg msg-info" style="margin-top:28px;">
            Your cart is empty. <a href="/pastimes/clothes.php">Browse clothes</a> to add items.
        </div>
    <?php else: ?>

        <div class="section-head" style="margin-top:28px;">
            <h2>Cart Items</h2>
            <a href="cart.php?clear=1"
               onclick="return confirm('Clear your entire cart?');"
               class="btn btn-danger btn-sm">Clear Cart</a>
        </div>

        <div class="card" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Brand</th>
                            <th>Size</th>
                            <th>Condition</th>
                            <th>Price</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart as $i => $item): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" style="width:80px;height:60px;object-fit:cover;border-radius:8px;">
                                    <span><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($item['brand']); ?></td>
                            <td><?php echo htmlspecialchars($item['size']); ?></td>
                            <td><?php echo htmlspecialchars($item['item_condition']); ?></td>
                            <td style="color:var(--gold);font-family:'Playfair Display',serif;font-weight:700;">
                                R<?php echo number_format($item['price'], 2); ?>
                            </td>
                            <td>
                                <a href="cart.php?remove=<?php echo $i; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Remove this item?');">✕</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:rgba(198,159,213,0.08);">
                            <td colspan="5" style="padding:14px 16px;font-weight:600;font-family:'Playfair Display',serif;">
                                Total
                            </td>
                            <td colspan="2" style="padding:14px 16px;color:var(--gold);font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:700;">
                                R<?php echo number_format($total, 2); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex-gap mt-4">
            <a href="/pastimes/clothes.php" class="btn btn-primary">Continue Shopping</a>
            <button class="btn btn-gold"
                    onclick="alert('Order placed! Total: R<?php echo number_format($total,2); ?>\nThank you for shopping at Pasttime.')">
                Place Order
            </button>
        </div>

    <?php endif; ?>

</div>

<footer>
    &copy; <?php echo date('Y'); ?> <span>Pasttime</span> Clothing Store
</footer>

</body>
</html>
