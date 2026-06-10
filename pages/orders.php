<?php
session_start();
require_once '../php/db.php';

if(!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user']['id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>My Orders — Nexora</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="logo">NEX<span>ORA</span></a>
  <ul class="nav-links">
    <li><a href="../index.php">Home</a></li>
    <li><a href="catalog.php">Shop</a></li>
    <li><a href="cart.php">Cart</a></li>
    <li><a href="orders.php">Orders</a></li>
    <li><a href="../php/logout.php" class="btn-nav">Logout</a></li>
  </ul>
</nav>

<div class="orders-page">
  <h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;margin-bottom:2rem;">
    My <em style="color:var(--gold);font-style:italic;">Orders</em>
  </h1>

  <?php if(empty($orders)): ?>
    <div style="text-align:center;padding:5rem;color:var(--muted);">
      <p style="font-size:3rem;">📦</p>
      <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">No orders yet</h3>
      <a href="catalog.php" class="btn-primary">Start Shopping</a>
    </div>
  <?php else: ?>
    <?php foreach($orders as $order):
      $items_stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
      $items_stmt->execute([$order['id']]);
      $items = $items_stmt->fetchAll();
      $statusClass = 'status-'.$order['status'];
    ?>
    <div class="order-card">
      <div class="order-header">
        <div>
          <p class="order-id">Order #<?php echo str_pad($order['id'],6,'0',STR_PAD_LEFT); ?></p>
          <p style="color:var(--muted);font-size:0.82rem;"><?php echo date('M d, Y g:i A', strtotime($order['created_at'])); ?></p>
        </div>
        <span class="order-status <?php echo $statusClass; ?>"><?php echo ucfirst($order['status']); ?></span>
      </div>
      <div style="border-top:1px solid var(--border);padding-top:1rem;margin-top:0.5rem;">
        <?php foreach($items as $item): ?>
          <div style="display:flex;justify-content:space-between;font-size:0.88rem;margin-bottom:0.4rem;">
            <span><?php echo htmlspecialchars($item['name']); ?> ×<?php echo $item['quantity']; ?></span>
            <span style="color:var(--gold);">$<?php echo number_format($item['price']*$item['quantity'],2); ?></span>
          </div>
        <?php endforeach; ?>
        <div style="display:flex;justify-content:space-between;font-weight:700;border-top:1px solid var(--border);padding-top:0.7rem;margin-top:0.7rem;">
          <span>Total</span>
          <span>$<?php echo number_format($order['total'],2); ?></span>
        </div>
        <?php if($order['shipping_address']): ?>
          <p style="color:var(--muted);font-size:0.8rem;margin-top:0.5rem;">📍 <?php echo htmlspecialchars($order['shipping_address']); ?></p>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<script src="../js/main.js"></script>
</body>
</html>
