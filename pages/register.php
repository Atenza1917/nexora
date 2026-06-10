<?php
session_start();
require_once '../php/db.php';

if(isset($_SESSION['user'])) { header('Location: ../index.php'); exit; }

$error = $success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if(empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif(strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if($check->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES(?,?,?,'customer')");
            $stmt->execute([$name, $email, $hash]);
            $success = 'Account created! <a href="login.php">Click here to login.</a>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Register — Nexora</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="logo">NEX<span>ORA</span></a>
</nav>
<div class="auth-page">
  <div class="auth-card">
    <h2>Create Account</h2>
    <p>Already have an account? <a href="login.php">Login</a></p>

    <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" placeholder="Jane Doe" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
      </div>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Min. 6 characters" required>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm" placeholder="Repeat your password" required>
      </div>
      <button type="submit" class="btn-submit">Create Account</button>
    </form>
  </div>
</div>
</body>
</html>
