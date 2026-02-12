<?php
// Delete tenant - protected
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id > 0) {
        $pdo = getPDO();
        $stmt = $pdo->prepare('DELETE FROM tenants WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

header('Location: tenants.php');
exit;

