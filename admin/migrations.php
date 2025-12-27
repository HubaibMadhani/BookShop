<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ http_response_code(403); echo 'Access denied'; exit; }
include '../includes/config.php';

$checks = [];
$checks['created_at'] = (bool)$pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = 'created_at'")->execute() && $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = 'created_at'")->fetchColumn();
$checks['is_read'] = (bool)$pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = 'is_read'")->execute() && $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = 'is_read'")->fetchColumn();
// Check password_resets table
$checks['password_resets'] = (bool)$pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'password_resets'")->fetchColumn();

$results = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    try{
        $pdo->beginTransaction();
        if(!$checks['created_at']){
            $pdo->exec("ALTER TABLE contact_messages ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER message");
            $results[] = 'Added column created_at';
        }
        if(!$checks['is_read']){
            $pdo->exec("ALTER TABLE contact_messages ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER created_at");
            $results[] = 'Added column is_read';
        }
      if(!$checks['password_resets']){
        $pdo->exec("CREATE TABLE password_resets(
          id INT AUTO_INCREMENT PRIMARY KEY,
          user_id INT NOT NULL,
          token_hash VARCHAR(255) NOT NULL,
          expires_at DATETIME NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $results[] = 'Created table password_resets';
      }
        $pdo->commit();
    }catch(PDOException $e){
        $pdo->rollBack();
        $results[] = 'Error: ' . $e->getMessage();
    }
    // refresh checks
    $checks['created_at'] = (bool)$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = 'created_at'")->fetchColumn();
    $checks['is_read'] = (bool)$pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = 'is_read'")->fetchColumn();
}

include '../includes/header.php';
?>
<section class="container admin">
  <h1>Database migrations</h1>
  <p>This page applies small, safe schema changes used by the admin UI.</p>
  <ul>
    <li>contact_messages.created_at: <?php echo $checks['created_at'] ? '<strong>present</strong>' : 'missing'; ?></li>
    <li>contact_messages.is_read: <?php echo $checks['is_read'] ? '<strong>present</strong>' : 'missing'; ?></li>
  </ul>

  <?php if(!empty($results)): ?>
    <div class="message">
      <?php foreach($results as $r) echo '<div>'.htmlspecialchars($r).'</div>'; ?>
    </div>
  <?php endif; ?>

  <?php if(!$checks['created_at'] || !$checks['is_read']): ?>
    <form method="post" onsubmit="return confirm('Run migrations? This will alter the database schema.')">
      <button class="btn primary" type="submit">Run migrations</button>
    </form>
  <?php else: ?>
    <div class="message">All migrations applied.</div>
  <?php endif; ?>

  <p><a class="btn" href="dashboard.php">Back to dashboard</a></p>
</section>

<?php include '../includes/footer.php'; ?>
