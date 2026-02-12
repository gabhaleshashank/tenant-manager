<?php
// Dashboard - protected
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$pdo = getPDO();

// One-time flash success message (e.g. after creating a tenant)
$flashSuccess = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

// Basic stats
$totalTenants = (int)$pdo->query('SELECT COUNT(*) FROM tenants')->fetchColumn();
$activeTenants = (int)$pdo->query("SELECT COUNT(*) FROM tenants WHERE status = 'active'")->fetchColumn();
$movedOut = (int)$pdo->query("SELECT COUNT(*) FROM tenants WHERE status = 'moved_out'")->fetchColumn();

// Last 5 tenants
$recentStmt = $pdo->query('SELECT id, full_name, property_name, monthly_rent, status, created_at FROM tenants ORDER BY created_at DESC LIMIT 5');
$recentTenants = $recentStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <div>
            <div class="card-title">
                Dashboard
            </div>
            <div class="card-subtitle">
                Welcome back, <?php echo htmlspecialchars(current_user_name() ?? ''); ?>.
                Here is a quick snapshot of your tenant portfolio.
            </div>
        </div>
        <div class="card-actions">
            <a href="tenant_form.php" class="btn">Add tenant</a>
            <a href="tenants.php" class="btn btn-outline">View all</a>
        </div>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($flashSuccess); ?>
        </div>
    <?php endif; ?>

    <div class="stat-group">
        <div class="stat">
            <div class="stat-label">Total tenants</div>
            <div class="stat-value"><?php echo $totalTenants; ?></div>
        </div>
        <div class="stat">
            <div class="stat-label">Active</div>
            <div class="stat-value">
                <?php echo $activeTenants; ?>
                <span class="badge badge-success" style="margin-left: 0.35rem;">In contract</span>
            </div>
        </div>
        <div class="stat">
            <div class="stat-label">Moved out</div>
            <div class="stat-value">
                <?php echo $movedOut; ?>
            </div>
        </div>
    </div>

    <h3 style="font-size: 0.95rem; margin: 0.5rem 0;">Recent tenants</h3>
    <div class="table-wrapper">
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Property</th>
                <th>Monthly rent</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$recentTenants): ?>
                <tr>
                    <td colspan="5">No tenants yet. Start by adding your first tenant.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($recentTenants as $tenant): ?>
                    <tr>
                        <td>
                            <a href="tenant_profile.php?id=<?php echo $tenant['id']; ?>">
                                <?php echo htmlspecialchars($tenant['full_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($tenant['property_name']); ?></td>
                        <td><?php echo number_format((float)$tenant['monthly_rent'], 2); ?></td>
                        <td>
                            <?php if ($tenant['status'] === 'active'): ?>
                                <span class="pill pill-active">Active</span>
                            <?php else: ?>
                                <span class="pill pill-moved">Moved out</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($tenant['created_at']))); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

