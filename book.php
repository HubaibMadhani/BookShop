<?php include 'includes/header.php'; include 'includes/config.php';
$id = isset($_GET['id'])? (int)$_GET['id'] : 0;
$book = $pdo->prepare('SELECT * FROM books WHERE id = ?'); $book->execute([$id]); $b = $book->fetch();
if(!$b){ echo '<section class="container"><h2>Book not found</h2></section>'; include 'includes/footer.php'; exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!isset($_SESSION['cart'])) $_SESSION['cart']=[];
  $qty = max(1,(int)($_POST['quantity']??1));
  if(isset($_SESSION['cart'][$id])) $_SESSION['cart'][$id] += $qty; else $_SESSION['cart'][$id] = $qty;
  header('Location: cart.php'); exit;
}
?>
<section class="container book-detail">
  <div class="book-detail-card">
    <img src="<?=htmlspecialchars($b['image'])?>" alt="<?=htmlspecialchars($b['title'])?>">
    <div>
      <h1><?=htmlspecialchars($b['title'])?></h1>
      <p class="meta">By <?=htmlspecialchars($b['author'])?></p>
      <p class="price">$<?=number_format($b['price'],2)?></p>
      <p><?=nl2br(htmlspecialchars($b['description']))?></p>
      <form method="post" class="add-form">
        <label>Quantity <input type="number" name="quantity" value="1" min="1"></label>
        <button class="btn" type="submit">Add to cart</button>
      </form>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
