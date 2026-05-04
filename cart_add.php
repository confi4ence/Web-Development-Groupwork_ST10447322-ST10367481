<?php
session_start();
require_once 'DBConn.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM tblClothes WHERE clothes_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($item) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $_SESSION['cart'][] = $item;
        $_SESSION['cart_msg'] = htmlspecialchars($item['item_name']) . " added to cart!";
    }
}
echo json_encode(['status' => 'ok']);
?>