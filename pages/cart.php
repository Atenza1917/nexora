<?php
session_start();
require_once '../php/db.php';

// Handle remove
if(isset($_GET['remove'])) {
    $rid = (int)$_GET['remove'];
    unset($_SESSION['cart'][$rid]);
    header('Location: cart.php');
    exit;
}

// Handle qty update
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach($_POST['qty'] as $pid => $qty) {
        $qty = (int)$qty;
        if($qty <= 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            $_SESSION['cart'][$pid]['qty'] = $qty;
        }
    }
    header('Location: cart.php');
    exit;
}

$cart  = $_SESSION['cart'] ?? [];
$total = 0;
foreach($cart as $item) $total += $item['price'] * $item['qty'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Cart — Nexora</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="logo">NEX<span>ORA</span></a>
  <ul class="nav-links">
    <li><a href="../index.php">Home</a></li>
    <li><a href="catalog.php">Shop</a></li>
    <li><a href="cart.php">Cart <span class="cart-count"><?php echo array_sum(array_column($cart,'qty')); ?></span></a></li>
    <?php if(isset($_SESSION['user'])): ?>
      <li><a href="orders.php">Orders</a></li>
      <li><a href="../php/logout.php" class="btn-nav">Logout</a></li>
    <?php else: ?>
      <li><a href="login.php" class="btn-nav">Login</a></li>
    <?php endif; ?>
  </ul>
</nav>

<div class="cart-page">
  <h1>Your <em style="color:var(--gold);font-style:italic;">Cart</em></h1>

  <?php if(isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?>"><?php echo $_SESSION['flash']['msg']; unset($_SESSION['flash']); ?></div>
  <?php endif; ?>

  <?php if(empty($cart)): ?>
    <div style="text-align:center;padding:5rem 0;color:var(--muted)">
      <p style="font-size:4rem;">🛒</p>
      <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">Your cart is empty</h3>
      <a href="catalog.php" class="btn-primary">Start Shopping</a>
    </div>
  <?php else: ?>
    <form method="POST">
      <?php foreach($cart as $id => $item): ?>
      <div class="cart-item">
        <div class="cart-item-img">
          <?php
          $icons = ['Electronics'=>'📱','Clothing'=>'👗','Books'=>'📚','Home'=>'🏠'];
          echo $icons[$item['category']] ?? '🛒';
          ?>
        </div>
        <div>
          <h4><?php echo htmlspecialchars($item['name']); ?></h4>
          <p class="price">$<?php echo number_format($item['price'],2); ?> each</p>
          <p style="color:var(--muted);font-size:0.82rem;">Subtotal: $<?php echo number_format($item['price']*$item['qty'],2); ?></p>
        </div>
        <div class="qty-control">
          <button type="button" class="qty-btn" onclick="changeQty(<?php echo $id; ?>,-1)">−</button>
          <input type="number" name="qty[<?php echo $id; ?>]" id="qty-<?php echo $id; ?>" value="<?php echo $item['qty']; ?>" min="0" style="width:50px;text-align:center;border:1px solid var(--border);border-radius:4px;padding:0.3rem;">
          <button type="button" class="qty-btn" onclick="changeQty(<?php echo $id; ?>,1)">+</button>
        </div>
        <div>
          <a href="cart.php?remove=<?php echo $id; ?>" class="remove-btn">✕ Remove</a>
        </div>
      </div>
      <?php endforeach; ?>
      <button type="submit" class="btn-add" style="display:inline-block;width:auto;padding:0.7rem 2rem;margin-top:1rem;">Update Cart</button>
    </form>

    <div class="cart-total">
      <h3>Order Summary</h3>
      <div class="total-row"><span>Subtotal</span><span>$<?php echo number_format($total,2); ?></span></div>
      <div class="total-row"><span>Shipping</span><span>Free</span></div>
      <div class="total-row"><span>Tax (5%)</span><span>$<?php echo number_format($total*0.05,2); ?></span></div>
      <div class="total-row grand"><span>Total</span><span>$<?php echo number_format($total*1.05,2); ?></span></div>
      <?php if(isset($_SESSION['user'])): ?>
        <a href="checkout.php" class="checkout-btn">Proceed to Checkout →</a>
      <?php else: ?>
        <a href="login.php" class="checkout-btn">Login to Checkout →</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<script>
function changeQty(id, delta) {
  const input = document.getElementById('qty-' + id);
  const newVal = Math.max(0, parseInt(input.value) + delta);
  input.value = newVal;
}
</script>
<script src="../js/main.js"></script>
</body>
</html>
