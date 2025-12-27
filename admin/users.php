<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ http_response_code(403); echo 'Access denied'; exit; }
include '../includes/config.php';

$perPage = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Actions: promote, demote, delete
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $action = $_POST['action'] ?? '';
  $id = (int)($_POST['id'] ?? 0);

  // Reset password (only main admin allowed) - expects 'new_password'
  if($action === 'reset_password'){
    if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1){ $_SESSION['flash'] = 'Not authorized'; header('Location: users.php?page='.$page); exit; }
    $new = $_POST['new_password'] ?? '';
    if(strlen($new) < 8){ $_SESSION['flash'] = 'Password must be at least 8 characters.'; header('Location: users.php?page='.$page); exit; }
    $hash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->execute([$hash, $id]);
    $_SESSION['flash'] = 'Password reset.';
    header('Location: users.php?page='.$page);
    exit;
  }

  // Create new user
  if($action === 'create'){
    $name = trim($_POST['name'] ?? '');
    $email_raw = trim($_POST['email'] ?? '');
    $email = $email_raw ? filter_var($email_raw, FILTER_VALIDATE_EMAIL) : false;
    $password = $_POST['password'] ?? '';
    $role = ($_POST['role'] === 'admin') ? 'admin' : 'user';

    if(!$name || strlen($name) < 2){
      $_SESSION['flash'] = 'Name must be at least 2 characters.';
    } elseif(!$email){
      $_SESSION['flash'] = 'Please provide a valid email.';
    } elseif(strlen($password) < 8){
      $_SESSION['flash'] = 'Password must be at least 8 characters.';
    } else {
      // Check duplicate
      $s = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
      $s->execute([$email]);
      if($s->fetch()){
        $_SESSION['flash'] = 'A user with that email already exists.';
      } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
        $ins->execute([$name,$email,$hash,$role]);
        $_SESSION['flash'] = 'User created successfully.';
      }
    }
    header('Location: users.php?page='.$page);
    exit;
  }

  if($id > 0){
    // Prevent self-demotion or self-deletion
    if($id == $_SESSION['user_id'] && in_array($action, ['demote','delete'])){
      $_SESSION['flash'] = 'You cannot demote or delete your own account.';
    } else {
      // Count admins to prevent removing last admin
      $adminCount = $pdo->query("SELECT COUNT(*) as c FROM users WHERE role='admin'")->fetch()['c'];

      if($action === 'promote'){
        $stmt = $pdo->prepare("UPDATE users SET role='admin' WHERE id = ?");
        $stmt->execute([$id]);
      } elseif($action === 'demote'){
        if($adminCount <= 1){
          $_SESSION['flash'] = 'Cannot demote the last admin.';
        } else {
          $stmt = $pdo->prepare("UPDATE users SET role='user' WHERE id = ?");
          $stmt->execute([$id]);
        }
      } elseif($action === 'delete'){
        if($adminCount <= 1 && $pdo->query("SELECT role FROM users WHERE id=$id")->fetch()['role'] === 'admin'){
          $_SESSION['flash'] = 'Cannot delete the last admin.';
        } else {
          $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
          $stmt->execute([$id]);
        }
      }
    }
  }
  header('Location: users.php?page='.$page);
  exit;
}

$total = $pdo->query('SELECT COUNT(*) as c FROM users')->fetch()['c'];
$rows = $pdo->prepare('SELECT id,name,email,role FROM users ORDER BY id DESC LIMIT ? OFFSET ?');
$rows->bindValue(1, $perPage, PDO::PARAM_INT);
$rows->bindValue(2, $offset, PDO::PARAM_INT);
$rows->execute();
$users = $rows->fetchAll();

include '../includes/header.php';
?>
<section class="container admin">
  <h1>Users</h1>
  <?php if(isset($_SESSION['flash'])){ echo '<div class="message">'.htmlspecialchars($_SESSION['flash']).'</div>'; unset($_SESSION['flash']); } ?>

  <section style="margin-bottom:1rem">
    <h2 style="margin-top:0">Add user</h2>
    <form method="post" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
      <input name="name" placeholder="Name" required style="flex:1;min-width:180px">
      <input name="email" type="email" placeholder="Email" required style="flex:1;min-width:200px">
      <input name="password" type="password" placeholder="Password" required style="min-width:160px">
      <select name="role"><option value="user">User</option><option value="admin">Admin</option></select>
      <button class="btn" name="action" value="create">Create</button>
    </form>
  </section>
  <table class="admin-table">
    <thead>
      <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach($users as $u): ?>
      <tr>
        <td><?php echo $u['id']; ?></td>
        <td><?php echo htmlspecialchars($u['name']); ?></td>
        <td><?php echo htmlspecialchars($u['email']); ?></td>
        <td><?php echo htmlspecialchars($u['role']); ?></td>
        <td>
          <?php if($u['role'] !== 'admin'): ?>
            <form method="post" style="display:inline"><input type="hidden" name="id" value="<?php echo $u['id']; ?>"><button class="btn" name="action" value="promote">Promote</button></form>
          <?php else: ?>
            <form method="post" style="display:inline"><input type="hidden" name="id" value="<?php echo $u['id']; ?>"><button class="btn alt" name="action" value="demote">Demote</button></form>
          <?php endif; ?>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete user?');"><input type="hidden" name="id" value="<?php echo $u['id']; ?>"><button class="btn" name="action" value="delete">Delete</button></form>
          <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): ?>
            <button class="btn small" data-action="reset-password" data-id="<?php echo $u['id']; ?>">Reset password</button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php $pages = ceil($total / $perPage); if($pages>1): ?>
    <nav style="margin-top:1rem">Page:
      <?php for($i=1;$i<=$pages;$i++): ?>
        <a class="btn" href="users.php?page=<?php echo $i; ?>" style="margin-right:.25rem"><?php echo $i; ?></a>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>

</section>

<?php include '../includes/footer.php'; ?>
