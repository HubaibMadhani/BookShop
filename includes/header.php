<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
	<?php
	// Prefer a root-relative URL so includes from subfolders load the same file,
	// and append a cache-busting query using the file modification time.
	$cssPath = '/my_website_xampp_ready/assets/css/style.css';
	$cssFile = __DIR__ . '/../assets/css/style.css';
	$cssVer = file_exists($cssFile) ? filemtime($cssFile) : time();
	?>
	<link rel="stylesheet" href="<?php echo $cssPath ?>?v=<?php echo $cssVer ?>">
	<title>BookShop</title>
</head>
<body>
<?php
// Base web path for links (adjust if the project folder name changes)
$base = '/my_website_xampp_ready';
?>
<?php if(session_status()!==PHP_SESSION_ACTIVE) session_start(); ?>
<header class="site-header">
	<div class="container">
		<a class="brand" href="<?php echo $base ?>/index.php">BookShop</a>
		<nav class="navbar">
			<a href="<?php echo $base ?>/index.php">Home</a>
			<a href="<?php echo $base ?>/books.php">Books</a>
			<a href="<?php echo $base ?>/about.php">About</a>
			<a href="<?php echo $base ?>/contact.php">Contact</a>
			<?php if(isset($_SESSION['role']) && $_SESSION['role']==='admin'): ?><a href="<?php echo $base ?>/admin/dashboard.php">Admin</a><?php endif; ?>
		</nav>
		<div class="header-actions">
			<form class="search" action="books.php" method="get">
				<input name="q" placeholder="Search books...">
			</form>
			<a class="cart-link" href="<?php echo $base ?>/cart.php">Cart (<span id="cart-count"><?php echo isset($_SESSION['cart'])?array_sum($_SESSION['cart']):0; ?></span>)</a>
			<?php if(isset($_SESSION['user_id'])): ?>
				<a class="btn" href="<?php echo $base ?>/logout.php">Logout</a>
			<?php else: ?>
				<a class="btn" href="<?php echo $base ?>/login.php">Login</a>
				<a class="btn" href="<?php echo $base ?>/register.php">Register</a>
			<?php endif; ?>
		</div>
	</div>
</header>
<main class="site-main">