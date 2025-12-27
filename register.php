<?php
include 'includes/config.php';
session_start();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
  $email_raw = trim($_POST['email'] ?? '');
  $email = $email_raw ? filter_var($email_raw, FILTER_VALIDATE_EMAIL) : false;
  $password = $_POST['password'] ?? '';
  $password_confirm = $_POST['password_confirm'] ?? '';

  if (!$name || strlen($name) < 2) $errors[] = 'Please enter your name (2+ characters).';
  if (!$email) $errors[] = 'Please enter a valid email address.';
  if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
  if ($password !== $password_confirm) $errors[] = 'Passwords do not match.';

  if (empty($errors)) {
    try {
      $s = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
      $s->execute([$email]);
      if ($s->fetch()) {
        $errors[] = 'An account with that email already exists.';
      } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
        $stmt->execute([$name, $email, $hash, 'user']);
        $uid = $pdo->lastInsertId();
        if (!$uid) {
          // fallback: fetch user id by email
          $s2 = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
          $s2->execute([$email]);
          $row = $s2->fetch();
          $uid = $row['id'] ?? null;
        }
        if ($uid) {
          $_SESSION['user_id'] = $uid;
          $_SESSION['role'] = 'user';
          header('Location: index.php');
          exit;
        }
        $errors[] = 'Registration succeeded but automatic login failed. Please log in.';
      }
    } catch (PDOException $e) {
      error_log('Registration error: ' . $e->getMessage());
      $errors[] = 'Registration failed due to a server error. Please try again later.';
    }
  }
}
?>

<?php include 'includes/header.php'; ?>
<section class="container">
  <h1>Register</h1>
  <?php if(!empty($errors)) { foreach($errors as $err) echo '<div class="message">'.htmlspecialchars($err).'</div>'; } ?>
  <form method="post" id="register-form" class="auth-form" novalidate>
    <label>Name <input name="name" required value="<?php echo isset($name)?htmlspecialchars($name):''; ?>"></label>
    <label>Email <input name="email" type="email" required value="<?php echo isset($email)?htmlspecialchars($email):''; ?>"></label>
    <label>Password <input name="password" type="password" required></label>
    <label>Confirm password <input name="password_confirm" type="password" required></label>
    <div style="margin-top:.5rem"><small class="muted">Password must be at least 8 characters.</small></div>
    <button class="btn primary" type="submit">Register</button>
  </form>
</section>
<?php include 'includes/footer.php'; ?>