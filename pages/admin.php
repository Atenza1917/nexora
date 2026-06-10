<?php
session_start();
require_once '../php/db.php';

// Require admin role
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$tab = $_GET['tab'] ?? 'dashboard';
$msg = '';

// ---- PRODUCT ACTIONS ----
if($_SERVER['REQUEST_METHOD']==='POST') {
    if(isset($_POST['add_product'])) {
        $stmt = $pdo->prepare("INSERT INTO products (name,description,price,stock,category,image) VALUES(?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['name']),
            trim($_POST['description']),
            (float)$_POST['price'],
            (int)$_POST['stock'],
            trim($_POST['category']),
            trim($_POST['image']),
        ]);
        $msg = 'Product added successfully!';
        $tab = 'products';
    }
    if(isset($_POST['update_order'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->execute([$_POST['status'], (int)$_POST['order_id']]);
        $msg = 'Order updated!';
        $tab = 'orders';
    }
}

if(isset($_GET['delete_product'])) {
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_GET['delete_product']]);
    $msg = 'Product deleted.';
    $tab = 'products';
}

// Fetch data
$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$orders   = $pdo->query("SELECT o.*,u.name AS customer FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC")->fetchAll();
$users    = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$stats    = [
    'products' => count($products),
    'orders'   => count($orders),
    'users'    => count($users),
    'revenue'  => array_sum(array_column($orders,'total')),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin Panel — Nexora</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:var(--cream);">
<nav class="navbar">
  <a href="../index.php" class="logo">NEX<span>ORA</span> <span style="font-size:0.6rem;color:#aaa;font-family:'DM Sans',sans-serif;letter-spacing:0.1em;"> ADMIN</span></a>
  <ul class="nav-links">
    <li><a href="../index.php">← Site</a></li>
    <li><a href="../php/logout.php" class="btn-nav">Logout</a></li>
  </ul>
</nav>

<div class="admin-page">
  <div class="admin-header">
    <span style="font-size:2rem;">⚙️</span>
    <div>
      <h1>Admin Dashboard</h1>
      <p style="color:#aaa;font-size:0.85rem;">Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></p>
    </div>
  </div>

  <?php if($msg): ?>
    <div class="alert alert-success" style="margin-bottom:1.5rem;"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <!-- TABS -->
  <div class="tab-bar">
    <a href="?tab=dashboard" class="tab-link <?php echo $tab==='dashboard'?'active':''; ?>">📊 Dashboard</a>
    <a href="?tab=products"  class="tab-link <?php echo $tab==='products'?'active':''; ?>">📦 Products</a>
    <a href="?tab=orders"    class="tab-link <?php echo $tab==='orders'?'active':''; ?>">🛒 Orders</a>
    <a href="?tab=users"     class="tab-link <?php echo $tab==='users'?'active':''; ?>">👥 Users</a>
    <a href="?tab=add"       class="tab-link <?php echo $tab==='add'?'active':''; ?>">➕ Add Product</a>
  </div>

  <!-- DASHBOARD TAB -->
  <?php if($tab==='dashboard'): ?>
  <div class="admin-grid">
    <div class="stat-card"><p class="stat-num"><?php echo $stats['products']; ?></p><p class="stat-label">Total Products</p></div>
    <div class="stat-card"><p class="stat-num"><?php echo $stats['orders']; ?></p><p class="stat-label">Total Orders</p></div>
    <div class="stat-card"><p class="stat-num"><?php echo $stats['users']; ?></p><p class="stat-label">Registered Users</p></div>
    <div class="stat-card"><p class="stat-num">$<?php echo number_format($stats['revenue'],0); ?></p><p class="stat-label">Total Revenue</p></div>
    <div class="stat-card"><p class="stat-num"><?php echo count(array_filter($orders,fn($o)=>$o['status']==='pending')); ?></p><p class="stat-label">Pending Orders</p></div>
    <div class="stat-card"><p class="stat-num"><?php echo count(array_filter($products,fn($p)=>$p['stock']<5)); ?></p><p class="stat-label">Low Stock Items</p></div>
  </div>

  <!-- Recent orders -->
  <h3 style="font-family:'Playfair Display',serif;margin-bottom:1rem;">Recent Orders</h3>
  <table class="data-table">
    <tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
    <?php foreach(array_slice($orders,0,5) as $o): ?>
    <tr>
      <td>#<?php echo str_pad($o['id'],6,'0',STR_PAD_LEFT); ?></td>
      <td><?php echo htmlspecialchars($o['customer']); ?></td>
      <td>$<?php echo number_format($o['total'],2); ?></td>
      <td><span class="order-status status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
      <td><?php echo date('M d, Y',strtotime($o['created_at'])); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <!-- PRODUCTS TAB -->
  <?php if($tab==='products'): ?>
  <table class="data-table">
    <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
    <?php foreach($products as $p): ?>
    <tr>
      <td><?php echo $p['id']; ?></td>
      <td><?php echo htmlspecialchars($p['name']); ?></td>
      <td><?php echo htmlspecialchars($p['category']); ?></td>
      <td>$<?php echo number_format($p['price'],2); ?></td>
      <td style="color:<?php echo $p['stock']<5?'#e44':'inherit'; ?>"><?php echo $p['stock']; ?></td>
      <td>
        <a href="?tab=products&delete_product=<?php echo $p['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <!-- ORDERS TAB -->
  <?php if($tab==='orders'): ?>
  <table class="data-table">
    <tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Update</th></tr>
    <?php foreach($orders as $o): ?>
    <tr>
      <td>#<?php echo str_pad($o['id'],6,'0',STR_PAD_LEFT); ?></td>
      <td><?php echo htmlspecialchars($o['customer']); ?></td>
      <td>$<?php echo number_format($o['total'],2); ?></td>
      <td><span class="order-status status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
      <td><?php echo date('M d',strtotime($o['created_at'])); ?></td>
      <td>
        <form method="POST" style="display:flex;gap:0.4rem;">
          <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
          <select name="status" style="padding:0.3rem;border-radius:4px;border:1px solid var(--border);font-size:0.8rem;">
            <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo $o['status']===$s?'selected':''; ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" name="update_order" class="action-btn btn-edit">Save</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <!-- USERS TAB -->
  <?php if($tab==='users'): ?>
  <table class="data-table">
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr>
    <?php foreach($users as $u): ?>
    <tr>
      <td><?php echo $u['id']; ?></td>
      <td><?php echo htmlspecialchars($u['name']); ?></td>
      <td><?php echo htmlspecialchars($u['email']); ?></td>
      <td><span style="background:<?php echo $u['role']==='admin'?'var(--gold)':'var(--cream)'; ?>;color:var(--black);padding:0.2rem 0.6rem;border-radius:4px;font-size:0.75rem;"><?php echo ucfirst($u['role']); ?></span></td>
      <td><?php echo date('M d, Y',strtotime($u['created_at'])); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <!-- ADD PRODUCT TAB -->
  <?php if($tab==='add'): ?>
  <div style="max-width:600px;background:white;border:1px solid var(--border);border-radius:12px;padding:2rem;">
    <h3 style="font-family:'Playfair Display',serif;margin-bottom:1.5rem;">Add New Product</h3>
    <form method="POST">
      <div class="form-group">
        <label>Product Name</label>
        <input type="text" name="name" placeholder="e.g. Wireless Earbuds Pro" required style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;">
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="3" placeholder="Brief product description..." style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;resize:vertical;"></textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group">
          <label>Price ($)</label>
          <input type="number" name="price" step="0.01" min="0" placeholder="29.99" required style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;">
        </div>
        <div class="form-group">
          <label>Stock Quantity</label>
          <input type="number" name="stock" min="0" placeholder="100" required style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;">
        </div>
      </div>
      <div class="form-group">
        <label>Category</label>
        <select name="category" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;">
          <option>Electronics</option>
          <option>Clothing</option>
          <option>Books</option>
          <option>Home</option>
        </select>
      </div>
      <div class="form-group">
        <label>Image URL (optional)</label>
        <input type="text" name="image" placeholder="https://example.com/image.jpg" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:0.75rem;font-family:'DM Sans',sans-serif;">
      </div>
      <button type="submit" name="add_product" class="btn-submit" style="margin-top:0.5rem;">Add Product</button>
    </form>
  </div>
  <?php endif; ?>
</div>
<script src="../js/main.js"></script>
</body>
</html>
