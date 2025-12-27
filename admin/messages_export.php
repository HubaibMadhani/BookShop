<?php
session_start();
if(!isset($_SESSION['user_id'])){ header('Location: ../login.php'); exit; }
if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){ http_response_code(403); echo 'Access denied'; exit; }
include '../includes/config.php';

$ids = [];
if(isset($_GET['ids']) && strlen(trim($_GET['ids']))){
    $parts = array_filter(array_map('trim', explode(',', $_GET['ids'])));
    $ids = array_map('intval', $parts);
}

if(count($ids)){
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id,name,email,message,created_at,COALESCE(is_read,0) as is_read FROM contact_messages WHERE id IN ($placeholders) ORDER BY created_at DESC");
    $stmt->execute($ids);
} else {
    $stmt = $pdo->query("SELECT id,name,email,message,created_at,COALESCE(is_read,0) as is_read FROM contact_messages ORDER BY created_at DESC");
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=messages_export_'.date('Ymd_His').'.csv');
$out = fopen('php://output','w');
if($out){
    fputcsv($out, ['id','name','email','message','created_at','is_read']);
    foreach($rows as $r){
        fputcsv($out, [$r['id'],$r['name'],$r['email'],$r['message'],$r['created_at'],$r['is_read']]);
    }
    fclose($out);
}
exit;
