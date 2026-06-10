<?php
session_start();
require_once '../php/db.php';

if(!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
$cart = $_SESSION['cart'] ?? [];
if(empty($cart)) { header('Location: cart.php'); exit; }

$total = 0;
foreach($cart as $item) $total += $item['price'] * $item['qty'];
$tax = $total * 0.05;
$grand = $total + $tax;

$success = $error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    if(empty($address) || empty($city) || empty($country)) {
        $error = 'Please fill in all shipping fields.';
    } else {
        $shipping = "$address, $city, $country";
        $stmt = $pdo->prepare("INSERT INTO orders (user_id,total,status,shipping_address) VALUES(?,?,'pending',?)");
        $stmt->execute([$_SESSION['user']['id'], $grand, $shipping]);
        $order_id = $pdo->lastInsertId();

        // Insert order items
        $ins = $pdo->prepare("INSERT INTO order_items (order_id,product_id,quantity,price) VALUES(?,?,?,?)");
        $upd = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        foreach($cart as $item) {
            $ins->execute([$order_id, $item['id'], $item['qty'], $item['price']]);
            $upd->execute([$item['qty'], $item['id']]);
        }
        unset($_SESSION['cart']);
        $success = $order_id;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Checkout — Nexora</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="logo">NEX<span>ORA</span></a>
</nav>

<div style="max-width:700px;margin:4rem auto;padding:0 1.5rem;">
  <?php if($success): ?>
    <div style="text-align:center;padding:4rem 2rem;background:white;border-radius:16px;border:1px solid var(--border);">
      <div style="font-size:4rem;">✅</div>
      <h2 style="font-family:'Playfair Display',serif;font-size:2rem;margin:1rem 0;">Order Confirmed!</h2>
      <p style="color:var(--muted);">Your Order ID: <strong>#<?php echo str_pad($success,6,'0',STR_PAD_LEFT); ?></strong></p>
      <p style="color:var(--muted);margin-bottom:2rem;">Thank you for shopping with Nexora. Your order is being processed.</p>
      <a href="orders.php" class="btn-primary">View My Orders</a>
      &nbsp;&nbsp;
      <a href="catalog.php" class="btn-primary" style="background:var(--black);color:var(--white);">Continue Shopping</a>
    </div>
  <?php else: ?>
    <h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;margin-bottom:2rem;">Checkout</h1>
    <?php if($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:2rem;align-items:start;">
      <div>
        <div style="background:white;border:1px solid var(--border);border-radius:12px;padding:2rem;margin-bottom:1.5rem;">
          <h3 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem;">Shipping Address</h3>
          <form method="POST">
            <div class="form-group" style="margin-bottom:1rem;">
              <label style="color:var(--muted);font-size:0.82rem;">Street Address</label>
              <input type="text" name="address" placeholder="123 Main Street" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;" required>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
              <label style="color:var(--muted);font-size:0.82rem;">City</label>
              <input type="text" name="city" placeholder="Nairobi" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;" required>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
              <label style="color:var(--muted);font-size:0.82rem;">Country</label>
              <input type="text" name="country" placeholder="Kenya" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;" required>
            </div>
            <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">Payment (Simulation)</h3>
            <div class="form-group" style="margin-bottom:1rem;">
              <label style="color:var(--muted);font-size:0.82rem;">Card Number</label>
              <input type="text" placeholder="4242 4242 4242 4242" maxlength="19" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
              <div>
                <label style="color:var(--muted);font-size:0.82rem;display:block;margin-bottom:0.5rem;">Expiry</label>
                <input type="text" placeholder="MM/YY" maxlength="5" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;">
              </div>
              <div>
                <label style="color:var(--muted);font-size:0.82rem;display:block;margin-bottom:0.5rem;">CVV</label>
                <input type="text" placeholder="123" maxlength="3" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;">
              </div>
            </div>
            <button type="submit" class="btn-submit">Place Order — $<?php echo number_format($grand,2); ?></button>
          </form>
        </div>
      </div>

      <div style="background:var(--black);border-radius:12px;padding:1.5rem;color:white;">
        <h3 style="font-family:'Playfair Display',serif;color:var(--gold);margin-bottom:1.2rem;">Order Summary</h3>
        <?php foreach($cart as $item): ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:0.7rem;font-size:0.9rem;border-bottom:1px solid #2a2a2a;padding-bottom:0.7rem;">
          <span style="color:#ccc;"><?php echo htmlspecialchars($item['name']); ?> ×<?php echo $item['qty']; ?></span>
          <span>$<?php echo number_format($item['price']*$item['qty'],2); ?></span>
        </div>
        <?php endforeach; ?>
        <div style="display:flex;justify-content:space-between;margin-top:1rem;color:#aaa;font-size:0.88rem;"><span>Subtotal</span><span>$<?php echo number_format($total,2); ?></span></div>
        <div style="display:flex;justify-content:space-between;color:#aaa;font-size:0.88rem;"><span>Tax (5%)</span><span>$<?php echo number_format($tax,2); ?></span></div>
        <div style="display:flex;justify-content:space-between;margin-top:0.8rem;padding-top:0.8rem;border-top:1px solid #2a2a2a;color:var(--gold);font-size:1.1rem;font-weight:700;"><span>Total</span><span>$<?php echo number_format($grand,2); ?></span></div>
      </div>
    </div>
  <?php endif; ?>
</div>
<script src="../js/main.js"></script>
</body>
</html>
