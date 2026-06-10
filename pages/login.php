<?php
session_start();
require_once '../php/db.php';

if(isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if(empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header('Location: ../index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Login — Nexora</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="logo">NEX<span>ORA</span></a>
</nav>
<div class="auth-page">
  <div class="auth-card">
    <h2>Welcome Back</h2>
    <p>Don't have an account? <a href="register.php">Register here</a></p>

    <?php if($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-submit">Sign In</button>
    </form>

    <p style="margin-top:1.5rem;font-size:0.78rem;color:#555;text-align:center;">
      Demo admin: admin@nexora.com / password<br>
      Demo user: john@example.com / password
    </p>
  </div>
</div>
</body>
</html>
