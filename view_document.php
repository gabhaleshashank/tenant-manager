<?php
// Serve tenant document for view (inline) or download (attachment). Opens in new tab.
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$tenantId = isset($_GET['tenant_id']) ? (int)$_GET['tenant_id'] : 0;
$type = $_GET['type'] ?? '';
$disposition = $_GET['disposition'] ?? 'inline';

$allowedTypes = ['agreement_document', 'passport_photo', 'aadhar_card', 'pan_card'];
if ($tenantId <= 0 || !in_array($type, $allowedTypes, true) || !in_array($disposition, ['inline', 'attachment'], true)) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid request.');
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT ' . $type . ' FROM tenants WHERE id = :id');
$stmt->execute(['id' => $tenantId]);
$path = $stmt->fetchColumn();

if (!$path || $path === '') {
    header('HTTP/1.1 404 Not Found');
    exit('Document not found.');
}

$baseDir = realpath(__DIR__ . '/uploads');
$fullPath = realpath($baseDir . '/' . $path);

if ($fullPath === false || strpos($fullPath, $baseDir) !== 0 || !is_file($fullPath)) {
    header('HTTP/1.1 404 Not Found');
    exit('File not found.');
}

$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
$mimes = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
];
$mime = $mimes[$ext] ?? 'application/octet-stream';
$filename = basename($fullPath);

header('Content-Type: ' . $mime);
header('Content-Disposition: ' . ($disposition === 'attachment' ? 'attachment' : 'inline') . '; filename="' . basename($filename) . '"');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
exit;
