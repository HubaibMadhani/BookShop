<?php
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') { http_response_code(403); echo 'Forbidden'; exit; }
require_once __DIR__ . '/../includes/config.php';
$email = 'muhammadhubaib365@gmail.com';
$stmt = $pdo->prepare('SELECT id,name,email,role FROM users WHERE email = ?');
$stmt->execute([$email]);
$u = $stmt->fetch();
if ($u) {
    echo "FOUND: id={$u['id']} email={$u['email']} role={$u['role']}";
} else {
    echo "NOT FOUND";
}
@unlink(__FILE__);
?>
