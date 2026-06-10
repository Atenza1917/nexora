<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Nexora — Premium Marketplace</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAV -->
<nav class="navbar">
  <a href="index.php" class="logo">NEX<span>ORA</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="pages/catalog.php">Shop</a></li>
    <li><a href="pages/cart.php">Cart <span class="cart-count"><?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'],'qty')) : 0; ?></span></a></li>
    <?php if(isset($_SESSION['user'])): ?>
      <?php if($_SESSION['user']['role'] === 'admin'): ?>
        <li><a href="pages/admin.php">Admin</a></li>
      <?php endif; ?>
      <li><a href="pages/orders.php">Orders</a></li>
      <li><a href="php/logout.php" class="btn-nav">Logout</a></li>
    <?php else: ?>
      <li><a href="pages/login.php" class="btn-nav">Login</a></li>
    <?php endif; ?>
  </ul>
  <button class="hamburger" onclick="toggleMenu()">&#9776;</button>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <p class="hero-tag">New Season · 2026</p>
    <h1 class="hero-title">Shop the <br><em>Future</em> of Style</h1>
    <p class="hero-sub">Curated products, seamless experience, delivered to your door.</p>
    <a href="pages/catalog.php" class="btn-primary">Explore Collection</a>
  </div>
  <div class="hero-visual">
    <div class="hero-card">
      <div class="hero-img-placeholder">
        <span>✦</span>
      </div>
      <div class="hero-card-label">
        <p>Featured Drop</p>
        <strong>Nexora Essentials</strong>
      </div>
    </div>
    <div class="hero-float f1">Electronics</div>
    <div class="hero-float f2">Clothing</div>
    <div class="hero-float f3">Books</div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="categories">
  <h2 class="section-title">Browse <em>Categories</em></h2>
  <div class="cat-grid">
    <a href="pages/catalog.php?category=electronics" class="cat-card">
      <div class="cat-icon">📱</div>
      <h3>Electronics</h3>
      <p>Gadgets & Devices</p>
    </a>
    <a href="pages/catalog.php?category=clothing" class="cat-card">
      <div class="cat-icon">👗</div>
      <h3>Clothing</h3>
      <p>Fashion & Apparel</p>
    </a>
    <a href="pages/catalog.php?category=books" class="cat-card">
      <div class="cat-icon">📚</div>
      <h3>Books</h3>
      <p>Knowledge & Stories</p>
    </a>
    <a href="pages/catalog.php?category=home" class="cat-card">
      <div class="cat-icon">🏠</div>
      <h3>Home</h3>
      <p>Decor & Living</p>
    </a>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="featured">
  <h2 class="section-title">Featured <em>Products</em></h2>
  <div class="products-grid">
    <?php
    require_once 'php/db.php';
    $stmt = $pdo->query("SELECT * FROM products LIMIT 8");
    $products = $stmt->fetchAll();
    if(empty($products)):
    ?>
      <!-- Demo products if DB empty -->
      <?php
      $demos = [
        ['id'=>1,'name'=>'Wireless Headphones','price'=>129.99,'category'=>'Electronics','image'=>''],
        ['id'=>2,'name'=>'Slim Fit Jacket','price'=>89.99,'category'=>'Clothing','image'=>''],
        ['id'=>3,'name'=>'The Art of Code','price'=>24.99,'category'=>'Books','image'=>''],
        ['id'=>4,'name'=>'Smart Desk Lamp','price'=>49.99,'category'=>'Home','image'=>''],
      ];
      foreach($demos as $p): ?>
      <div class="product-card">
        <div class="product-img">
          <span class="prod-icon"><?php echo $p['category']==='Electronics'?'📱':($p['category']==='Clothing'?'👔':($p['category']==='Books'?'📖':'💡')); ?></span>
          <span class="badge"><?php echo $p['category']; ?></span>
        </div>
        <div class="product-info">
          <h4><?php echo htmlspecialchars($p['name']); ?></h4>
          <p class="price">$<?php echo number_format($p['price'],2); ?></p>
          <a href="php/add_to_cart.php?id=<?php echo $p['id']; ?>" class="btn-add">Add to Cart</a>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <?php foreach($products as $p): ?>
      <div class="product-card">
        <div class="product-img">
          <?php if($p['image']): ?>
            <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
          <?php else: ?>
            <span class="prod-icon">🛒</span>
          <?php endif; ?>
          <span class="badge"><?php echo htmlspecialchars($p['category']); ?></span>
        </div>
        <div class="product-info">
          <h4><?php echo htmlspecialchars($p['name']); ?></h4>
          <p class="price">$<?php echo number_format($p['price'],2); ?></p>
          <a href="php/add_to_cart.php?id=<?php echo $p['id']; ?>" class="btn-add">Add to Cart</a>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div style="text-align:center;margin-top:2rem;">
    <a href="pages/catalog.php" class="btn-primary">View All Products</a>
  </div>
</section>

<!-- WHY NEXORA -->
<section class="why">
  <div class="why-inner">
    <h2 class="section-title" style="color:#fff">Why <em>Nexora?</em></h2>
    <div class="why-grid">
      <div class="why-card">
        <div class="why-icon">🔒</div>
        <h3>Secure Payments</h3>
        <p>HTTPS & encrypted transactions every step of the way.</p>
      </div>
      <div class="why-card">
        <div class="why-icon">🚀</div>
        <h3>Fast Delivery</h3>
        <p>Orders processed instantly with real-time tracking.</p>
      </div>
      <div class="why-card">
        <div class="why-icon">↩️</div>
        <h3>Easy Returns</h3>
        <p>30-day hassle-free return policy on all products.</p>
      </div>
      <div class="why-card">
        <div class="why-icon">🌍</div>
        <h3>Global Reach</h3>
        <p>Shipping to over 50 countries worldwide.</p>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <p class="logo" style="font-size:1.4rem">NEX<span>ORA</span></p>
      <p>Premium e-commerce for the modern world.</p>
    </div>
    <div class="footer-links">
      <h4>Quick Links</h4>
      <a href="index.php">Home</a>
      <a href="pages/catalog.php">Shop</a>
      <a href="pages/login.php">Login</a>
      <a href="pages/register.php">Register</a>
    </div>
    <div class="footer-links">
      <h4>Support</h4>
      <a href="#">FAQ</a>
      <a href="#">Shipping Info</a>
      <a href="#">Returns</a>
      <a href="#">Contact Us</a>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; 2026 Nexora. All rights reserved. | Capstone Project</p>
  </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
