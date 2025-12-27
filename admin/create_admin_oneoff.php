<?php
// One-off admin creation script. Runs only from localhost and removes itself afterwards.
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1'){
    http_response_code(403); echo 'Forbidden'; exit;
}
require_once __DIR__ . '/../includes/config.php';
$email = 'muhammadhubaib365@gmail.com';
$password = '123';

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
$row = $stmt->fetch();
if ($row) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $u = $pdo->prepare('UPDATE users SET password = ?, role = ? WHERE id = ?');
    $u->execute([$hash, 'admin', $row['id']]);
    echo "Updated existing user to admin: $email";
} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $i = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
    $i->execute(['Administrator', $email, $hash, 'admin']);
    echo "Created admin user: $email";
}

// Attempt to remove this file for security
@unlink(__FILE__);

?>
