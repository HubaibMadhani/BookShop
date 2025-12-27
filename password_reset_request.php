<?php
include 'includes/config.php';
include 'includes/header.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Please enter a valid email address.';
    } else {
        // Find user
        $s = $pdo->prepare('SELECT id,name FROM users WHERE email = ? LIMIT 1');
        $s->execute([$email]);
        $user = $s->fetch();
        // Always show the same success message to avoid leaking emails
        $msg = 'If that email exists in our system, a password reset link has been sent.';

        if ($user) {
            // ensure table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token_hash VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

            $token = bin2hex(random_bytes(16));
            $token_hash = password_hash($token, PASSWORD_DEFAULT);
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            // Remove old tokens for user
            $del = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
            $del->execute([$user['id']]);

            $ins = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
            $ins->execute([$user['id'], $token_hash, $expires]);

            // Send email with token link
            $resetLink = rtrim($app_base_url, '/') . '/password_reset.php?uid=' . $user['id'] . '&token=' . $token;
            $subject = 'Password reset request';
            $body = "Hello " . ($user['name'] ?: '') . ",\n\n" .
                "We received a request to reset your password. Click the link below to reset it (valid for 1 hour):\n\n" .
                $resetLink . "\n\n" .
                "If you did not request this, ignore this email.\n\n" .
                "— BookShop\n";
            $headers = 'From: ' . $mail_from . "\r\n" . 'Content-Type: text/plain; charset=utf-8';
            @mail($email, $subject, $body, $headers);
        }
    }
}
?>
<section class="container">
    <div class="contact-card">
        <h1>Reset your password</h1>
        <?php if($msg): ?><div class="alert success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group"><label for="email">Email</label><input id="email" name="email" type="email" required></div>
            <div class="form-actions"><button class="btn primary" type="submit">Send reset link</button></div>
        </form>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
