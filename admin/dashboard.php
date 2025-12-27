<?php session_start(); if(!isset($_SESSION['user_id'])){header('Location: ../login.php');}
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ http_response_code(403); echo 'Access denied'; exit; }
include '../includes/config.php';
$totalMessages = $pdo->query("SELECT COUNT(*) as c FROM contact_messages")->fetch()['c'];
// Check whether the `is_read` column exists before using it (supports older DBs)
$colCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact_messages' AND COLUMN_NAME = 'is_read'");
$colCheck->execute();
$hasIsRead = (bool)$colCheck->fetchColumn();
$unreadMessages = 0;
if ($hasIsRead) {
  $unreadMessages = $pdo->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_read = 0")->fetch()['c'];
}
$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
$bookCount = $pdo->query("SELECT COUNT(*) as c FROM books")->fetch()['c'];
$userCount = $pdo->query("SELECT COUNT(*) as c FROM users")->fetch()['c'];
$orderCount = $pdo->query("SELECT COUNT(*) as c FROM orders")->fetch()['c'];
// current admin name
$userName = $pdo->prepare('SELECT name FROM users WHERE id = ?');
$userName->execute([$_SESSION['user_id']]);
$userName = $userName->fetchColumn();
?>
<?php include '../includes/header.php'; ?>
<section class="container admin admin-full-messages">
  <div class="admin-layout">
    <aside class="sidebar" aria-label="Admin navigation">
      <div class="sidebar-brand">
        <a href="dashboard.php" class="brand">Admin Panel</a>
      </div>
      <nav class="sidebar-nav">
        <a href="dashboard.php" class="active">Dashboard</a>
        <!-- Messages management removed from dashboard navigation -->
        <a href="users.php">Users</a>
        <a href="books.php">Books</a>
        <a href="migrations.php">Migrations</a>
        <a href="../logout.php">Logout</a>
      </nav>
    </aside>
    <main class="admin-main">
      <div class="main-header">
        <div>
          <h1>Admin Dashboard</h1>
          <p class="muted">Welcome back, <strong><?= htmlspecialchars($userName ?: 'Admin') ?></strong></p>
        </div>
        <div class="header-actions-right">
          <a class="btn alt" href="migrations.php">Run migrations</a>
        </div>
      </div>
  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi-title">Messages</div>
      <div class="kpi-value"><?= $totalMessages ?></div>
      <div class="kpi-sub">Unread: <strong id="unread-count"><?= $unreadMessages ?></strong></div>
    </div>
    <div class="kpi">
      <div class="kpi-title">Users</div>
      <div class="kpi-value"><?= $userCount ?></div>
    </div>
    <div class="kpi">
      <div class="kpi-title">Books</div>
      <div class="kpi-value"><?= $bookCount ?></div>
    </div>
    <div class="kpi">
      <div class="kpi-title">Orders</div>
      <div class="kpi-value"><?= $orderCount ?></div>
    </div>
  </div>
  <div class="dashboard-grid">
    <div class="card">
      <div class="card-title"><span class="card-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4c-1.1 0-2 .9-2 2v12l4-3 4 3 4-3 4 3V6c0-1.1-.9-2-2-2z"></path></svg></span> Messages</div>
      <div class="card-value"><?= $totalMessages ?></div>
      <div class="card-sub">Unread: <strong id="unread-count"><?= $unreadMessages ?></strong></div>
    </div>
    <div class="card">
      <div class="card-title"><span class="card-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zM8 13c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zM16 13c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5C23 14.17 18.33 13 16 13z"></path></svg></span> Users</div>
      <div class="card-value"><?= $userCount ?></div>
    </div>
    <div class="card">
      <div class="card-title"><span class="card-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 2H6c-1.1 0-2 .9-2 2v16l7-3 7 3V4c0-1.1-.9-2-2-2z"></path></svg></span> Books</div>
      <div class="card-value"><?= $bookCount ?></div>
    </div>
    <div class="card">
      <div class="card-title"><span class="card-icon"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2S15.9 22 17 22s2-.9 2-2-.9-2-2-2zM7.16 14l.84-2h7.45c.75 0 1.41-.41 1.75-1.03L21 4H6.21L5.27 2H2v2h2l3.6 7.59-1.35 2.44C6.16 15.53 6 16 6 16v2h14v-2H7.16z"></path></svg></span> Orders</div>
      <div class="card-value"><?= $orderCount ?></div>
    </div>
  </div>
  <h2>Recent messages</h2>
  <div class="table-controls">
    <div class="search-box">
      <input id="msg-search" placeholder="Search messages...">
    </div>
    <div class="filters">
      <label><input type="checkbox" id="filter-unread"> Show unread only</label>
    </div>
    <div class="bulk-actions">
      
      <button class="btn small icon" id="delete-selected" aria-label="Delete selected"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"></path></svg> <span class="label">Delete</span></button>
      <a class="btn small icon" id="export-selected" href="messages_export.php" aria-label="Export CSV"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 20h14v-2H5v2zm7-18L5.33 9h3.34v4h4.66V9h3.34L12 2z"></path></svg> <span class="label">Export CSV</span></a>
    </div>
  </div>

  <table class="admin-table" id="messages-table">
    <thead><tr><th>Name</th><th>Email</th><th>Excerpt</th><th>Date</th><th>Read</th><th></th></tr></thead>
    <tbody>
      <?php foreach($messages as $r): ?>
        <tr data-id="<?= $r['id'] ?>" data-name="<?= htmlspecialchars($r['name'],ENT_QUOTES) ?>" data-email="<?= htmlspecialchars($r['email'],ENT_QUOTES) ?>" data-message="<?= htmlspecialchars($r['message'],ENT_QUOTES) ?>" data-date="<?= $r['created_at'] ?>" data-read="<?= isset($r['is_read']) ? $r['is_read'] : 0 ?>">
          <td><input type="checkbox" class="select-row" value="<?= $r['id'] ?>"> <?= htmlspecialchars($r['name']) ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td style="max-width:320px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($r['message']) ?></td>
          <td><?= $r['created_at'] ?></td>
          <td class="read-status"><span class="status <?= isset($r['is_read']) ? ($r['is_read'] ? 'read' : 'unread') : 'na' ?>"><?= isset($r['is_read']) ? ($r['is_read'] ? 'Read' : 'Unread') : 'N/A' ?></span></td>
          <td><button class="btn small icon" data-action="view-message" aria-label="View message"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5C21.27 7.61 17 4.5 12 4.5zm0 12.5c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"></path></svg></button></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Message modal -->
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
  <p><a class="btn" href="books.php">Manage Books</a></p>
  <p><a class="btn" href="users.php">Manage Users</a></p>
    </main> <!-- .admin-main -->
  </div> <!-- .admin-layout -->
</section>
<?php include '../includes/footer.php'; ?>