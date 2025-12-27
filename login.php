<?php session_start(); include 'includes/config.php';
$err=''; if($_POST){ $s=$pdo->prepare("SELECT * FROM users WHERE email=?"); $s->execute([$_POST['email']]); $u=$s->fetch(); if($u && password_verify($_POST['password'],$u['password'])){ $_SESSION['user_id']=$u['id']; $_SESSION['role']=$u['role']; header('Location: '.(isset($_SESSION['role']) && $_SESSION['role']=='admin'? 'admin/dashboard.php' : 'index.php')); exit; } else { $err='Invalid credentials'; } }
?>
<?php include 'includes/header.php'; ?>
<section class="container">
  <h1>Login</h1>
  <?php if($err) echo '<div class="message">'.htmlspecialchars($err).'</div>'; ?>
  <form method="post" class="auth-form">
    <label>Email <input name="email" type="email" required></label>
    <label>Password <input name="password" type="password" required></label>
    <button class="btn primary" type="submit">Login</button>
  </form>
</section>
<?php include 'includes/footer.php'; ?>