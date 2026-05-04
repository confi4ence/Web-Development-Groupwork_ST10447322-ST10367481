<?php
// ===================================================
// setup.php — ONE-CLICK full database setup
// Run this FIRST before anything else.
// Creates all 4 tables, inserts admin + users + clothes
// ===================================================

// Connect without selecting a database first so we can CREATE it
$conn = new mysqli("localhost", "root", "", "");
if ($conn->connect_error) {
    die("<b>Connection failed:</b> " . $conn->connect_error);
}

// Create the database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS ClothingStore");
$conn->select_db("ClothingStore");

$steps = [];
$errors = [];

function run($conn, $sql, $label, &$steps, &$errors) {
    if ($conn->query($sql)) {
        $steps[] = $label;
    } else {
        $errors[] = "$label — " . $conn->error;
    }
}

// ---- Drop existing tables (order matters for FK) ----
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("DROP TABLE IF EXISTS tblAorder");
$conn->query("DROP TABLE IF EXISTS tblClothes");
$conn->query("DROP TABLE IF EXISTS tblUser");
$conn->query("DROP TABLE IF EXISTS tblAdmin");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
$steps[] = "Old tables dropped.";

// ---- tblAdmin ----
run($conn, "
    CREATE TABLE tblAdmin (
        admin_id   INT AUTO_INCREMENT PRIMARY KEY,
        full_name  VARCHAR(100)  NOT NULL,
        email      VARCHAR(150)  NOT NULL UNIQUE,
        username   VARCHAR(80)   NOT NULL UNIQUE,
        password   VARCHAR(255)  NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
", "tblAdmin created.", $steps, $errors);

// ---- tblUser ----
run($conn, "
    CREATE TABLE tblUser (
        user_id    INT AUTO_INCREMENT PRIMARY KEY,
        full_name  VARCHAR(100)  NOT NULL,
        email      VARCHAR(150)  NOT NULL UNIQUE,
        username   VARCHAR(80)   NOT NULL UNIQUE,
        password   VARCHAR(255)  NOT NULL,
        role       ENUM('buyer','seller') NOT NULL DEFAULT 'buyer',
        status     ENUM('pending','approved') NOT NULL DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
", "tblUser created.", $steps, $errors);

// ---- tblClothes ----
run($conn, "
    CREATE TABLE tblClothes (
        clothes_id  INT AUTO_INCREMENT PRIMARY KEY,
        item_name   VARCHAR(150)  NOT NULL,
        brand       VARCHAR(100),
        category    VARCHAR(80),
        size        VARCHAR(20),
        item_condition VARCHAR(50),
        price       DECIMAL(10,2) NOT NULL,
        description TEXT,
        image_path  VARCHAR(255),
        seller_id   INT,
        status      ENUM('available','sold') NOT NULL DEFAULT 'available',
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES tblUser(user_id) ON DELETE SET NULL
    )
", "tblClothes created.", $steps, $errors);

// ---- tblAorder ----
run($conn, "
    CREATE TABLE tblAorder (
        order_id    INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT NOT NULL,
        clothes_id  INT NOT NULL,
        quantity    INT NOT NULL DEFAULT 1,
        total_price DECIMAL(10,2) NOT NULL,
        order_date  DATETIME DEFAULT CURRENT_TIMESTAMP,
        status      ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
        FOREIGN KEY (user_id)    REFERENCES tblUser(user_id)        ON DELETE CASCADE,
        FOREIGN KEY (clothes_id) REFERENCES tblClothes(clothes_id)  ON DELETE CASCADE
    )
", "tblAorder created.", $steps, $errors);

// ---- Insert Admin (password hashed here in PHP) ----
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    "INSERT INTO tblAdmin (full_name, email, username, password) VALUES (?, ?, ?, ?)"
);
$fn = 'Super Admin';
$em = 'admin@pasttime.co.za';
$un = 'admin';
$stmt->bind_param("ssss", $fn, $em, $un, $admin_password);
if ($stmt->execute()) {
    $steps[] = "Admin inserted. Login: username=<b>admin</b> | email=<b>admin@pasttime.co.za</b> | password=<b>admin123</b>";
} else {
    $errors[] = "Admin insert failed: " . $stmt->error;
}
$stmt->close();

// ---- Insert Users from userData.txt ----
$file = 'userData.txt';
if (!file_exists($file)) {
    $errors[] = "userData.txt not found — users not loaded.";
} else {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $count = 0;
    $ustmt = $conn->prepare(
        "INSERT INTO tblUser (full_name, email, username, password, role, status) VALUES (?,?,?,?,'buyer','approved')"
    );
    foreach ($lines as $line) {
        $parts = array_map('trim', explode(',', $line));
        if (count($parts) < 5) continue;
        [$full_name, $email, $username, $plain_pw, $role] = $parts;
        $hashed = password_hash($plain_pw, PASSWORD_DEFAULT);
        // Use the role from the file
        $conn->query(
            "INSERT INTO tblUser (full_name, email, username, password, role, status)
             VALUES ('".addslashes($full_name)."','".addslashes($email)."','".addslashes($username)."',
             '".$hashed."','".addslashes($role)."','approved')"
        );
        $count++;
    }
    $ustmt->close();
    $steps[] = "$count users imported from userData.txt (all approved).";
}

// ---- Insert Sample Clothing Items ----
$clothes = [
    ["Blue Denim Jacket",    "Levi's",      "Jackets",  "M",   "Good",      349.99, "Classic blue denim jacket, slightly worn.",       "images/Blue Denim Jacket.jpg"],
    ["White Sneakers",       "Nike",        "Shoes",    "8",   "Like New",  599.00, "Barely worn white Nike sneakers.",                "images/White Sneakers.jpg"],
    ["Black Hoodie",         "H&M",         "Tops",     "L",   "Excellent", 199.99, "Cosy black pullover hoodie.",                     "images/Black Hoodie.jpg"],
    ["Floral Summer Dress",  "Zara",        "Dresses",  "S",   "Like New",  450.00, "Light floral dress, perfect for summer.",         "images/Floral Summer Dress.webp"],
    ["Grey Skinny Jeans",    "Woolworths",  "Bottoms",  "32",  "Good",      280.00, "Comfortable grey skinny jeans.",                  "images/Grey Skinny Jeans.webp"],
    ["Checked Blazer",       "Markham",     "Jackets",  "L",   "Excellent", 520.00, "Smart checked blazer for formal occasions.",      "images/Checked Blazer.webp"],
    ["Red Polo Shirt",       "Lacoste",     "Tops",     "M",   "Good",      375.00, "Classic Lacoste polo, red.",                      "images/Red Polo Shirt.webp"],
    ["Khaki Cargo Shorts",   "Mr Price",    "Bottoms",  "34",  "Good",      120.00, "Sturdy cargo shorts with side pockets.",          "images/Khaki Cargo Shorts.webp"],
    ["Ankle Boots",          "Steve Madden","Shoes",    "7",   "Like New",  680.00, "Stylish ankle boots, barely worn.",               "images/Ankle boots.webp"],
    ["Striped T-Shirt",      "Cotton On",   "Tops",     "S",   "Excellent",  89.99, "Casual striped tee, great condition.",            "images/Striped T-Shirt.webp"],
    ["Winter Puffer Jacket", "The Fix",     "Jackets",  "XL",  "Good",      799.00, "Warm puffer jacket for cold days.",               "images/puffer jacket.webp"],
    ["Pleated Midi Skirt",   "Edgars",      "Bottoms",  "M",   "Like New",  310.00, "Elegant pleated skirt, cream colour.",            "images/midi skirt.webp"],
];

// Fetch seller IDs (users with role=seller)
$sellers = [];
$sr = $conn->query("SELECT user_id FROM tblUser WHERE role='seller'");
if ($sr) {
    while ($row = $sr->fetch_assoc()) $sellers[] = $row['user_id'];
}
if (empty($sellers)) $sellers = [null];

$cstmt = $conn->prepare(
    "INSERT INTO tblClothes (item_name, brand, category, size, item_condition, price, description, image_path, seller_id, status)
     VALUES (?,?,?,?,?,?,?,?,?,'available')"
);

$inserted_clothes = 0;
foreach ($clothes as $i => $c) {
    [$name, $brand, $cat, $size, $cond, $price, $desc, $img] = $c;
    $seller_id = $sellers[$i % count($sellers)];
    $cstmt->bind_param("sssssdssi", $name, $brand, $cat, $size, $cond, $price, $desc, $img, $seller_id);
    if ($cstmt->execute()) $inserted_clothes++;
    else $errors[] = "Clothes insert failed for $name: " . $cstmt->error;
}
$cstmt->close();
$steps[] = "$inserted_clothes clothing items inserted into tblClothes.";

// ---- Insert Sample Orders ----
$orders_sql = "
    INSERT INTO tblAorder (user_id, clothes_id, quantity, total_price, status) VALUES
    (1, 1, 1, 349.99, 'delivered'),
    (2, 4, 1, 450.00, 'confirmed'),
    (3, 2, 1, 599.00, 'shipped'),
    (1, 3, 1, 199.99, 'pending'),
    (2, 5, 1, 280.00, 'delivered')
";
if ($conn->query($orders_sql)) {
    $steps[] = "5 sample orders inserted into tblAorder.";
} else {
    // Non-fatal — user IDs may not match
    $steps[] = "Note: Sample orders skipped (user/item IDs may not align — add manually via phpMyAdmin).";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup — Pasttime ClothingStore</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page" style="align-items: flex-start; padding-top: 40px;">
    <div style="width:100%;max-width:640px;margin:0 auto;padding:0 20px;">

        <div class="card" style="border-top-color: var(--gold);">
            <div style="text-align:center;margin-bottom:28px;">
                <div style="display:inline-flex;align-items:center;justify-content:center;
                            width:56px;height:56px;background:var(--secondary);border-radius:14px;margin-bottom:12px;">
                    <svg viewBox="0 0 24 24" style="width:26px;height:26px;fill:var(--gold);">
                        <path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/>
                    </svg>
                </div>
                <h2 style="font-family:'Playfair Display',serif;">Database Setup Complete</h2>
                <p style="color:#888;font-size:0.88rem;">Pasttime Clothing Store</p>
            </div>

            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $e): ?>
                    <div class="msg msg-error"><?php echo $e; ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div style="margin-bottom:24px;">
                <?php foreach ($steps as $step): ?>
                    <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #f0f0f0;font-size:0.9rem;">
                        <span style="color:var(--success);font-size:1.1rem;flex-shrink:0;">✓</span>
                        <span><?php echo $step; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($errors)): ?>
            <div class="msg msg-success">
                Setup successful! You can now use the application.
            </div>
            <?php endif; ?>

            <div style="background:var(--bg);border-radius:8px;padding:16px;margin-bottom:20px;font-size:0.88rem;">
                <strong style="display:block;margin-bottom:8px;font-family:'Playfair Display',serif;">Admin Credentials</strong>
                <div style="display:grid;grid-template-columns:auto 1fr;gap:4px 16px;">
                    <span style="color:#888;">Username:</span> <code>admin</code>
                    <span style="color:#888;">Email:</span>    <code>admin@pasttime.co.za</code>
                    <span style="color:#888;">Password:</span> <code>admin123</code>
                </div>
            </div>

            <div class="flex-gap">
                <a href="admin_login.php" class="btn btn-gold">Admin Login →</a>
                <a href="register.php"   class="btn btn-primary">Register a User</a>
                <a href="login.php"      class="btn btn-dark">User Login</a>
            </div>
        </div>

    </div>
</div>
</body>
</html>