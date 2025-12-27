<?php
// Simple redirect to admin dashboard to avoid 404 when requesting /admin/ or /admin/index.php
if(session_status() !== PHP_SESSION_ACTIVE) session_start();
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$loc = ($base === '' || $base === '/') ? '/admin/dashboard.php' : $base . '/dashboard.php';
header('Location: ' . $loc);
exit;
