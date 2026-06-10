<?php
session_start();
require_once '../php/db.php';

$category = $_GET['category'] ?? '';
$search   = $_GET['search'] ?? '';

$sql    = "SELECT * FROM products WHERE 1=1";
$params = [];

if($category) {
    $sql .= " AND LOWER(category) = LOWER(?)";
    $params[] = $category;
}
if($search) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = ['All','Electronics','Clothing','Books','Home'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Shop — Nexora</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="logo">NEX<span>ORA</span></a>
  <ul class="nav-links">
    <li><a href="../index.php">Home</a></li>
    <li><a href="catalog.php">Shop</a></li>
    <li><a href="cart.php">Cart <span class="cart-count"><?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'],'qty')) : 0; ?></span></a></li>
    <?php if(isset($_SESSION['user'])): ?>
      <?php if($_SESSION['user']['role']==='admin'): ?><li><a href="admin.php">Admin</a></li><?php endif; ?>
      <li><a href="orders.php">Orders</a></li>
      <li><a href="../php/logout.php" class="btn-nav">Logout</a></li>
    <?php else: ?>
      <li><a href="login.php" class="btn-nav">Login</a></li>
    <?php endif; ?>
  </ul>
  <button class="hamburger" onclick="toggleMenu()">&#9776;</button>
</nav>

<div class="shop-page">
  <div class="shop-header">
    <div>
      <h1 style="font-family:'Playfair Display',serif;font-size:2rem;">Shop <em style="color:var(--gold)">Everything</em></h1>
      <p style="color:var(--muted);"><?php echo count($products); ?> products found</p>
    </div>
    <form method="GET" class="search-bar">
      <?php if($category): ?>
        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
      <?php endif; ?>
      <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit">🔍</button>
    </form>
  </div>

  <div class="filter-tabs" style="margin-bottom:2rem;">
    <a href="catalog.php" class="filter-tab <?php echo !$category ? 'active' : ''; ?>">All</a>
    <?php foreach(array_slice($categories,1) as $cat): ?>
      <a href="catalog.php?category=<?php echo strtolower($cat); ?>" class="filter-tab <?php echo strtolower($category)===strtolower($cat)?'active':''; ?>">
        <?php echo $cat; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if(empty($products)): ?>
    <div style="text-align:center;padding:5rem;color:var(--muted)">
      <p style="font-size:3rem;">🔍</p>
      <h3 style="font-family:'Playfair Display',serif;">No products found</h3>
      <p>Try a different search or category.</p>
      <a href="catalog.php" class="btn-primary" style="margin-top:1.5rem;display:inline-block;">Clear Filters</a>
    </div>
  <?php else: ?>
  <div class="products-grid">
    <?php foreach($products as $p): ?>
    <div class="product-card">
      <div class="product-img">
        <?php if(!empty($p['image'])): ?>
          <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
        <?php else: ?>
          <span class="prod-icon"><?php
            $icons = ['Electronics'=>'📱','Clothing'=>'👗','Books'=>'📚','Home'=>'🏠'];
            echo $icons[$p['category']] ?? '🛒';
          ?></span>
        <?php endif; ?>
        <span class="badge"><?php echo htmlspecialchars($p['category']); ?></span>
      </div>
      <div class="product-info">
        <h4><?php echo htmlspecialchars($p['name']); ?></h4>
        <p style="color:var(--muted);font-size:0.82rem;margin-bottom:0.5rem;line-height:1.4;">
          <?php echo htmlspecialchars(substr($p['description'],0,80)); ?>...
        </p>
        <p class="price">$<?php echo number_format($p['price'],2); ?></p>
        <p style="font-size:0.75rem;color:<?php echo $p['stock']>0?'#4CAF50':'#e44'; ?>;margin-bottom:0.8rem;">
          <?php echo $p['stock']>0 ? '✓ In Stock ('.$p['stock'].')' : '✗ Out of Stock'; ?>
        </p>
        <?php if($p['stock']>0): ?>
          <a href="../php/add_to_cart.php?id=<?php echo $p['id']; ?>&return=catalog" class="btn-add">Add to Cart</a>
        <?php else: ?>
          <button class="btn-add" disabled style="opacity:0.5;cursor:not-allowed;">Out of Stock</button>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<footer class="footer">
  <div class="footer-bottom"><p>&copy; 2026 Nexora. Capstone Project.</p></div>
</footer>
<script src="../js/main.js"></script>
</body>
</html>
