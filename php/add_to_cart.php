<?php
session_start();
require_once 'db.php';

$id     = (int)($_GET['id'] ?? 0);
$qty    = (int)($_GET['qty'] ?? 1);
$return = $_GET['return'] ?? 'home';

if($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND stock > 0");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if($product) {
        if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        if(isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$id] = [
                'id'    => $product['id'],
                'name'  => $product['name'],
                'price' => $product['price'],
                'qty'   => $qty,
                'image' => $product['image'] ?? '',
                'category' => $product['category'],
            ];
        }
        $_SESSION['flash'] = ['type'=>'success','msg'=>'<strong>'.htmlspecialchars($product['name']).'</strong> added to cart!'];
    }
}

// Redirect back
switch($return) {
    case 'catalog': header('Location: ../pages/catalog.php'); break;
    case 'cart':    header('Location: ../pages/cart.php'); break;
    default:        header('Location: ../index.php'); break;
}
exit;
?>
