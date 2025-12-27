<?php
include 'includes/config.php';
if(session_status()!==PHP_SESSION_ACTIVE) session_start();
// Handle quick add-to-cart and buy-now from featured cards (before any output)
if($_SERVER['REQUEST_METHOD']==='POST' && (isset($_POST['add_to_cart']) || isset($_POST['buy_now']))){
  $add_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
  if($add_id){
    if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $qty = 1;
    if(isset($_SESSION['cart'][$add_id])) $_SESSION['cart'][$add_id] += $qty; else $_SESSION['cart'][$add_id] = $qty;
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    if(isset($_POST['buy_now'])){
      $loc = ($base === '' || $base === '/') ? '/checkout.php' : $base . '/checkout.php';
    } else {
      $loc = ($base === '' || $base === '/') ? '/cart.php' : $base . '/cart.php';
    }
    header('Location: ' . $loc);
    exit;
  }
}
include 'includes/header.php';
?>
<section class="hero container">
  <h1>Welcome to BookShop</h1>
  <p>Your place for modern programming and design books.</p>
  <p><a class="btn primary" href="books.php">Shop books</a></p>
</section>
<section class="container">
  <h2>Featured</h2>
  <?php $featured = $pdo->query("SELECT * FROM books ORDER BY created_at DESC LIMIT 3")->fetchAll(); ?>
  <div class="books-grid">
    <?php foreach($featured as $b): ?>
      <article class="book-card">
        <img src="<?=htmlspecialchars($b['image'])?>" alt="<?=htmlspecialchars($b['title'])?>">
        <h3><?=htmlspecialchars($b['title'])?></h3>
        <div class="meta"><?=htmlspecialchars($b['author'])?></div>
        <div class="price">$<?=number_format($b['price'],2)?></div>
        <form method="post" style="margin-top:.5rem;display:flex;gap:.5rem;">
          <input type="hidden" name="book_id" value="<?=$b['id']?>">
          <button class="btn" type="submit" name="add_to_cart">Add to cart</button>
          <button class="btn primary" type="submit" name="buy_now">Buy now</button>
        </form>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php include 'includes/footer.php'; ?>