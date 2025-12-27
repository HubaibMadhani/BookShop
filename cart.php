<?php include 'includes/header.php'; include 'includes/config.php';
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
// handle update/remove
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['update'])){
    foreach($_POST['qty'] as $id=>$q){ if($q<=0) unset($_SESSION['cart'][(int)$id]); else $_SESSION['cart'][(int)$id]=(int)$q; }
  }
  if(isset($_POST['clear'])){ $_SESSION['cart']=[]; }
  header('Location: cart.php'); exit;
}
$items=[]; $total=0;
if($_SESSION['cart']){
  $ids = array_keys($_SESSION['cart']);
  $in = implode(',', array_map('intval',$ids));
  $stmt = $pdo->query("SELECT * FROM books WHERE id IN ($in)");
  $rows = $stmt->fetchAll();
  foreach($rows as $r){ $q = $_SESSION['cart'][$r['id']]; $r['quantity']=$q; $r['subtotal']=$q*$r['price']; $total += $r['subtotal']; $items[]=$r; }
}
?>
<section class="container">
  <h1>Your Cart</h1>
  <?php if(empty($items)): ?>
    <p>Your cart is empty. <a href="books.php">Browse books</a></p>
  <?php else: ?>
    <form method="post">
    <table class="cart-table"><thead><tr><th>Book</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
    <tbody>
      <?php foreach($items as $it): ?>
        <tr>
          <td><?=htmlspecialchars($it['title'])?></td>
          <td>$<?=number_format($it['price'],2)?></td>
          <td><input type="number" name="qty[<?=$it['id']?>]" value="<?=$it['quantity']?>" min="0"></td>
          <td>$<?=number_format($it['subtotal'],2)?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
    <div class="cart-actions">
      <strong>Total: $<?=number_format($total,2)?></strong>
      <button type="submit" name="update" class="btn">Update</button>
      <button type="submit" name="clear" class="btn alt">Clear</button>
      <a class="btn primary" href="checkout.php">Checkout</a>
    </div>
    </form>
  <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>
