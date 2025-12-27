<?php
// Run this once to create an admin user, then delete this file for security.
include '../includes/config.php';
if(PHP_SAPI !== 'cli' && ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1')){
    echo 'Forbidden'; exit;
}
$email = 'admin@example.com';
$password = 'admin123';
$exists = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$exists->execute([$email]);
if($exists->fetch()){
    echo "Admin user already exists\n"; exit;
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
$stmt->execute(['Administrator',$email,$hash,'admin']);
echo "Created admin user: $email with password: $password\n";

?>
