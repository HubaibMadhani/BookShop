<?php include 'includes/header.php'; include 'includes/config.php';
if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])){ header('Location: cart.php'); exit; }
$ids = array_keys($_SESSION['cart']); $in = implode(',', array_map('intval',$ids));
$books = $pdo->query("SELECT * FROM books WHERE id IN ($in)")->fetchAll();
$items = []; $total = 0; foreach($books as $b){ $q = $_SESSION['cart'][$b['id']]; $items[]=['book'=>$b,'qty'=>$q]; $total += $b['price']*$q; }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name = $_POST['name']; $email = $_POST['email'];
  $pdo->beginTransaction();
  $stmt = $pdo->prepare('INSERT INTO orders (name,email,total) VALUES (?,?,?)');
  $stmt->execute([$name,$email,$total]); $order_id = $pdo->lastInsertId();
  $stmt2 = $pdo->prepare('INSERT INTO order_items (order_id,book_id,quantity,price) VALUES (?,?,?,?)');
  foreach($items as $it){ $stmt2->execute([$order_id,$it['book']['id'],$it['qty'],$it['book']['price']]); }
  $pdo->commit(); $_SESSION['cart']=[]; $order_id = (int)$order_id; header('Location: checkout.php?success='.$order_id); exit;
}
?>
<section class="container">
  <h1>Checkout</h1>
  <?php if(isset($_GET['success'])): ?>
    <p>Thank you! Your order #<?=intval($_GET['success'])?> has been placed.</p>
    <a class="btn" href="books.php">Continue browsing</a>
  <?php else: ?>
    <div class="order-summary">
      <h2>Order Summary</h2>
      <ul>
      <?php foreach($items as $it): ?>
        <li><?=htmlspecialchars($it['book']['title'])?> x <?=$it['qty']?> — $<?=number_format($it['book']['price']*$it['qty'],2)?></li>
      <?php endforeach; ?>
      </ul>
      <strong>Total: $<?=number_format($total,2)?></strong>
    </div>
    <form method="post" class="checkout-form">
      <label>Name <input name="name" required></label>
      <label>Email <input type="email" name="email" required></label>
      <button class="btn primary" type="submit">Place order</button>
    </form>
  <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>
