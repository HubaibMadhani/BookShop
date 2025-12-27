<?php
include 'includes/config.php';
include 'includes/header.php';

$error = '';
$success = '';
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)($_POST['uid'] ?? 0);
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Find token
        $s = $pdo->prepare('SELECT * FROM password_resets WHERE user_id = ?');
        $s->execute([$uid]);
        $row = $s->fetch();
        if (!$row || strtotime($row['expires_at']) < time() || !password_verify($token, $row['token_hash'])) {
            $error = 'Invalid or expired token.';
        } else {
            // Update password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $u = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $u->execute([$hash, $uid]);
            // Remove token
            $d = $pdo->prepare('DELETE FROM password_resets WHERE user_id = ?');
            $d->execute([$uid]);
            $success = 'Password reset successfully. You may now log in.';
        }
    }
}

?>
<section class="container">
    <div class="contact-card">
        <h1>Set a new password</h1>
        <?php if($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if(!$success): ?>
        <form method="post">
            <input type="hidden" name="uid" value="<?php echo htmlspecialchars($uid); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group"><label for="password">New password</label><input id="password" name="password" type="password" required></div>
            <div class="form-group"><label for="password_confirm">Confirm password</label><input id="password_confirm" name="password_confirm" type="password" required></div>
            <div class="form-actions"><button class="btn primary" type="submit">Set password</button></div>
        </form>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
