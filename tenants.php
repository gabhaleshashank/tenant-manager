<?php
// List tenants - protected
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$pdo = getPDO();
$stmt = $pdo->query('SELECT id, full_name, phone, email, property_name, monthly_rent, deposit, status FROM tenants ORDER BY full_name ASC');
$tenants = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Tenants</div>
            <div class="card-subtitle">Overview of all tenants in the system.</div>
        </div>
        <div class="card-actions">
            <a href="tenant_form.php" class="btn">Add tenant</a>
        </div>
    </div>
    <div class="table-wrapper table-wrapper-scroll-tenants">
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Property</th>
                <th>Rent</th>
                <th>Deposit</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$tenants): ?>
                <tr>
                    <td colspan="6">No tenants found. Add your first tenant.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tenants as $tenant): ?>
                    <tr>
                        <td>
                            <a href="tenant_profile.php?id=<?php echo $tenant['id']; ?>">
                                <?php echo htmlspecialchars($tenant['full_name']); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($tenant['phone']); ?>
                            <?php if ($tenant['email']): ?>
                                <br><span class="helper-text"><?php echo htmlspecialchars($tenant['email']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($tenant['property_name']); ?></td>
                        <td><?php echo htmlspecialchars(number_format((float)$tenant['monthly_rent'], 0, '.', '')); ?></td>
                        <td><?php echo htmlspecialchars(number_format((float)$tenant['deposit'], 0, '.', '')); ?></td>
                        <td>
                            <?php if ($tenant['status'] === 'active'): ?>
                                <span class="pill pill-active">Active</span>
                            <?php else: ?>
                                <span class="pill pill-moved">Moved out</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-outline" href="tenant_form.php?id=<?php echo $tenant['id']; ?>">Edit</a>
                                <form method="post" action="tenant_delete.php" onsubmit="return confirm('Delete this tenant?');">
                                    <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

