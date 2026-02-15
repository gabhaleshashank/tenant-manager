<?php
require_once __DIR__ . '/auth.php';

$is_logged_in = !empty($_SESSION['user_id']);
?>
<header class="app-header">
    <a href="<?php echo $is_logged_in ? 'dashboard.php' : 'index.php'; ?>" class="app-brand">
        <span class="logo-circle">T</span>
        <span class="logo-text">Tenant Manager</span>
    </a>
    <nav class="app-nav">
        <?php if ($is_logged_in): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="tenants.php">Tenants</a>
            <form method="post" action="logout.php" class="inline-form">
                <button type="submit" class="btn btn-outline">Logout</button>
            </form>
        <?php else: ?>
            <a href="index.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

