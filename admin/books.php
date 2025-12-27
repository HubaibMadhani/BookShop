<?php session_start(); if(!isset($_SESSION['user_id'])){ header('Location: ../login.php'); }
include '../includes/config.php';
// Delete (support both GET and POST delete for convenience)
if((isset($_GET['action']) && $_GET['action']=='delete' && isset($_GET['id'])) || (isset($_POST['action']) && $_POST['action']==='delete_book')){
  $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
  if($id){ $pdo->prepare('DELETE FROM books WHERE id=?')->execute([$id]); }
  header('Location: books.php'); exit;
}
// Add
if($_SERVER['REQUEST_METHOD']==='POST'){
  $title=$_POST['title']; $author=$_POST['author']; $desc=$_POST['description']; $price=$_POST['price']; $image=$_POST['image']?:'assets/images/placeholder.png';
  $pdo->prepare('INSERT INTO books (title,author,description,price,image) VALUES (?,?,?,?,?)')->execute([$title,$author,$desc,$price,$image]); header('Location: books.php'); exit;
}
$books = $pdo->query('SELECT * FROM books ORDER BY created_at DESC')->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<section class="container admin">
  <h1>Manage Books</h1>
  <form method="post" class="admin-form">
    <label>Title <input name="title" required></label>
    <label>Author <input name="author"></label>
    <label>Price <input name="price" type="number" step="0.01" required></label>
    <label>Image URL <input name="image"></label>
    <label>Description <textarea name="description"></textarea></label>
    <button class="btn" type="submit">Add Book</button>
  </form>

  <h2>Existing</h2>
  <table class="admin-table books-manage"><thead><tr><th></th><th>Title</th><th>Author</th><th>Price</th><th>Actions</th></tr></thead><tbody>
    <?php foreach($books as $b): ?>
      <tr>
        <td style="width:72px"><img class="thumbnail" src="<?=htmlspecialchars($b['image'])?>" alt="" width="64" height="88"></td>
        <td><?=htmlspecialchars($b['title'])?></td>
        <td><?=htmlspecialchars($b['author'])?></td>
        <td>$<?=number_format($b['price'],2)?></td>
        <td>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete this book?');">
            <input type="hidden" name="id" value="<?=$b['id']?>">
            <button class="btn small" name="action" value="delete_book">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody></table>
</section>
<?php include '../includes/footer.php'; ?>
