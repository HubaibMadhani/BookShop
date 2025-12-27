<?php include 'includes/header.php'; include 'includes/config.php';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$stmt = $pdo->prepare("SELECT * FROM books " . ($q?"WHERE title LIKE ? OR author LIKE ?":"") . " ORDER BY created_at DESC");
if($q){ $like = "%$q%"; $stmt->execute([$like,$like]); } else { $stmt->execute(); }
$books = $stmt->fetchAll();
?>
<section class="container">
  <h1>Books</h1>
  <div class="books-grid">
    <?php foreach($books as $b): ?>
      <article class="book-card">
        <img src="<?=htmlspecialchars($b['image'])?>" alt="<?=htmlspecialchars($b['title'])?>">
        <h3><?=htmlspecialchars($b['title'])?></h3>
        <div class="meta"><?=htmlspecialchars($b['author'])?></div>
        <div class="price">$<?=number_format($b['price'],2)?></div>
        <a class="btn" href="book.php?id=<?=$b['id']?>">View</a>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
