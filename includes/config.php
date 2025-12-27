<?php
// Database connection
try {
	$dsn = "mysql:host=127.0.0.1;port=3306;dbname=my_website;charset=utf8";
	$pdo = new PDO($dsn, "root", "");
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	// Log full error for debugging and show a friendly message to the user
	error_log('DB Connection Error: ' . $e->getMessage());
	http_response_code(500);
	echo '<h1>Database connection error</h1>';
	echo '<p>Unable to connect to the database. Please ensure the MySQL server is running (XAMPP Control Panel → Start MySQL) and the connection settings in <code>includes/config.php</code> are correct.</p>';
	echo '<p>If this is a development environment, try using <strong>127.0.0.1</strong> and port <strong>3306</strong> for the host settings, and verify the username/password.</p>';
	exit;
}
// Optional: reCAPTCHA keys (placeholders) — set these to enable verification
$recaptcha_site_key = '';
$recaptcha_secret = '';

// Mail settings (used by password reset emails)
$mail_from = 'no-reply@example.com';

// Base URL used in some generated links (adjust to your local environment)
$app_base_url = 'http://localhost/my_website_xampp_ready';

?>