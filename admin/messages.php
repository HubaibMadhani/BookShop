<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ http_response_code(403); echo 'Access denied'; exit; }
include '../includes/config.php';

$perPage = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Handle POST actions: mark_read, delete (supports single id or an array of ids via 'ids[]')
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action = $_POST['action'] ?? '';
  $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $ids = [];
  if(isset($_POST['ids']) && is_array($_POST['ids'])){
    $ids = array_map('intval', $_POST['ids']);
  }
  if($id>0){ $ids = [$id]; }
  if(count($ids) > 0){
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    if($action==='delete'){
      $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)");
      $stmt->execute($ids);
    } elseif($action==='mark_read'){
      $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id IN ($placeholders)");
      $stmt->execute($ids);
    } elseif($action==='mark_unread'){
      $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 0 WHERE id IN ($placeholders)");
      $stmt->execute($ids);
    }
  }
  // If request is AJAX, return JSON, otherwise redirect
  $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
  }
  header('Location: messages.php?page='.$page);
  exit;
}

$total = $pdo->query('SELECT COUNT(*) as c FROM contact_messages')->fetch()['c'];
$rows = $pdo->prepare('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?');
$rows->bindValue(1, $perPage, PDO::PARAM_INT);
$rows->bindValue(2, $offset, PDO::PARAM_INT);
$rows->execute();
$messages = $rows->fetchAll();

// Determine whether `is_read` column exists to avoid SQL/JSON errors on older DBs
$colCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = 'is_read'");
$colCheck->execute();
$hasIsRead = (bool)$colCheck->fetchColumn();

include '../includes/header.php';
?>
<section class="container admin">
  <h1>Messages</h1>
  <p><a class="btn" href="dashboard.php">Back to dashboard</a></p>

  <table class="admin-table" style="width:100%">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Message</th>
        <th>Date</th>
        <th>Read</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($messages as $m): ?>
        <tr data-id="<?php echo $m['id']; ?>" data-name="<?php echo htmlspecialchars($m['name'],ENT_QUOTES); ?>" data-email="<?php echo htmlspecialchars($m['email'],ENT_QUOTES); ?>" data-message="<?php echo htmlspecialchars($m['message'],ENT_QUOTES); ?>" data-date="<?php echo $m['created_at']; ?>" data-read="<?php echo isset($m['is_read']) ? $m['is_read'] : 0; ?>">
          <td><?php echo $m['id']; ?></td>
          <td><?php echo htmlspecialchars($m['name']); ?></td>
          <td><?php echo htmlspecialchars($m['email']); ?></td>
          <td style="max-width:400px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?php echo htmlspecialchars($m['message']); ?>"><?php echo htmlspecialchars($m['message']); ?></td>
          <td><?php echo $m['created_at']; ?></td>
          <td><?php echo isset($m['is_read']) ? ($m['is_read'] ? 'Yes' : 'No') : 'N/A'; ?></td>
          <td>
            <button class="btn small icon" data-action="view-message" aria-label="View message"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5C21.27 7.61 17 4.5 12 4.5zm0 12.5c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"></path></svg></button>
            <button class="btn small icon" data-action="delete-row" data-id="<?php echo $m['id']; ?>" aria-label="Delete message"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path></svg></button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php $pages = ceil($total / $perPage); if($pages>1): ?>
    <nav style="margin-top:1rem">Page:
      <?php for($i=1;$i<=$pages;$i++): ?>
        <a class="btn" href="messages.php?page=<?php echo $i; ?>" style="margin-right:.25rem"><?php echo $i; ?></a>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>
  <!-- Message modal (shared with dashboard) -->
  <div id="message-modal-backdrop" class="modal-backdrop">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <h2 id="modal-title">Message</h2>
      <div class="meta"><span id="modal-name"></span> • <span id="modal-email"></span> • <span id="modal-date"></span></div>
      <pre id="modal-message"></pre>
      <div class="modal-actions">
        <button class="btn alt icon" id="modal-toggle-read"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 16.2l-3.5-3.5L4 14.2 9 19l12-12-1.5-1.5z"></path></svg> Mark read</button>
        <button class="btn icon" id="modal-delete"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path></svg> Delete</button>
        <button class="btn" id="modal-close">Close</button>
      </div>
    </div>
  </div>

</section>

<?php include '../includes/footer.php'; ?>
