<?php
// ===================================================
// register.php — New user registration
// ===================================================
session_start();
require_once 'DBConn.php';
require_once 'includes/functions.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = clean_input($_POST['full_name'] ?? '');
    $email     = clean_input($_POST['email']     ?? '');
    $username  = clean_input($_POST['username']  ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = clean_input($_POST['role']      ?? 'buyer');

    // Server-side validation
    if (!$full_name || !$email || !$username || !$password || !$role) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check duplicates
        $chk = $conn->prepare("SELECT user_id FROM tblUser WHERE email=? OR username=?");
        $chk->bind_param("ss", $email, $username);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error = "Email or username already taken. Please choose another.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $conn->prepare(
                "INSERT INTO tblUser (full_name, email, username, password, role, status)
                 VALUES (?, ?, ?, ?, ?, 'pending')"
            );
            $stmt->bind_param("sssss", $full_name, $email, $username, $hashed, $role);
            if ($stmt->execute()) {
                $success = "Registration successful! Your account is pending admin approval.";
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $chk->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Pasttime</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
            </div>
            <h1>Create Account</h1>
            <p>Join Pasttime Clothing Store</p>
        </div>

        <?php if ($error):   ?><div class="msg msg-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="msg msg-success"><?php echo $success; ?></div><?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="register.php" novalidate>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                       placeholder="e.g. Jane Smith" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="jane@example.com" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       placeholder="Choose a username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Min. 6 characters" required>
            </div>

            <div class="form-group">
                <label for="role">I want to</label>
                <select id="role" name="role" required>
                    <option value="buyer"  <?php echo (($_POST['role'] ?? '') === 'buyer')  ? 'selected' : ''; ?>>Buy clothing</option>
                    <option value="seller" <?php echo (($_POST['role'] ?? '') === 'seller') ? 'selected' : ''; ?>>Sell clothing</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>
        <?php else: ?>
            <a href="login.php" class="btn btn-dark btn-block">Go to Login</a>
        <?php endif; ?>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</div>
</body>
</html>