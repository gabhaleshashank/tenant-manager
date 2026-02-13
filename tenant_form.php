<?php
// Add / edit tenant - protected
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$pdo = getPDO();
$error = '';
$success = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$tenant = [
    'full_name' => '',
    'phone' => '',
    'email' => '',
    'address' => '',
    'property_name' => '',
    'monthly_rent' => '',
    'deposit' => '',
    'move_in_date' => date('Y-m-d'),
    'status' => 'active',
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM tenants WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        $error = 'Tenant not found.';
        $isEdit = false;
    } else {
        $tenant = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant['full_name'] = trim($_POST['full_name'] ?? '');
    $tenant['phone'] = trim($_POST['phone'] ?? '');
    $tenant['email'] = trim($_POST['email'] ?? '');
    $tenant['address'] = trim($_POST['address'] ?? '');
    $tenant['property_name'] = trim($_POST['property_name'] ?? '');
    $tenant['monthly_rent'] = trim($_POST['monthly_rent'] ?? '');
    $tenant['deposit'] = trim($_POST['deposit'] ?? '');
    $tenant['move_in_date'] = trim($_POST['move_in_date'] ?? '');
    $tenant['status'] = $_POST['status'] ?? 'active';

    if (
        $tenant['full_name'] === '' ||
        $tenant['phone'] === '' ||
        $tenant['address'] === '' ||
        $tenant['property_name'] === '' ||
        $tenant['monthly_rent'] === '' ||
        $tenant['deposit'] === '' ||
        $tenant['move_in_date'] === ''
    ) {
        $error = 'Please fill in all required fields.';
    } else {
        if ($isEdit) {
            $stmt = $pdo->prepare(
                'UPDATE tenants
                 SET full_name = :full_name,
                     phone = :phone,
                     email = :email,
                     address = :address,
                     property_name = :property_name,
                     monthly_rent = :monthly_rent,
                     deposit = :deposit,
                     move_in_date = :move_in_date,
                     status = :status
                 WHERE id = :id'
            );
            $stmt->execute([
                'full_name' => $tenant['full_name'],
                'phone' => $tenant['phone'],
                'email' => $tenant['email'] ?: null,
                'address' => $tenant['address'],
                'property_name' => $tenant['property_name'],
                'monthly_rent' => $tenant['monthly_rent'],
                'deposit' => $tenant['deposit'],
                'move_in_date' => $tenant['move_in_date'],
                'status' => $tenant['status'],
                'id' => $id,
            ]);
            $success = 'Tenant updated successfully.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO tenants
                 (full_name, phone, email, address, property_name, monthly_rent, deposit, move_in_date, status)
                 VALUES
                 (:full_name, :phone, :email, :address, :property_name, :monthly_rent, :deposit, :move_in_date, :status)'
            );
            $stmt->execute([
                'full_name' => $tenant['full_name'],
                'phone' => $tenant['phone'],
                'email' => $tenant['email'] ?: null,
                'address' => $tenant['address'],
                'property_name' => $tenant['property_name'],
                'monthly_rent' => $tenant['monthly_rent'],
                'deposit' => $tenant['deposit'],
                'move_in_date' => $tenant['move_in_date'],
                'status' => $tenant['status'],
            ]);
            // Redirect to dashboard with a one-time success message
            $_SESSION['flash_success'] = 'Tenant added successfully!';
            header('Location: dashboard.php');
            exit;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <div>
            <div class="card-title">
                <?php echo $isEdit ? 'Edit tenant' : 'Add tenant'; ?>
            </div>
            <div class="card-subtitle">
                Capture core tenant details, property, and rent information.
            </div>
        </div>
        <div class="card-actions">
            <a href="tenants.php" class="btn btn-outline">Back to list</a>
        </div>
    </div>
    <?php if ($error): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="post" data-validate="true">
        <div class="form">
            <div class="form-row">
                <div class="field">
                    <label for="full_name">Full name</label>
                    <input type="text" id="full_name" name="full_name" data-required="true"
                           value="<?php echo htmlspecialchars($tenant['full_name']); ?>">
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" data-required="true"
                           value="<?php echo htmlspecialchars($tenant['phone']); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="email">Email (optional)</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars((string)$tenant['email']); ?>">
                </div>
                <div class="field">
                    <label for="property_name">Property / Unit</label>
                    <input type="text" id="property_name" name="property_name" data-required="true"
                           value="<?php echo htmlspecialchars($tenant['property_name']); ?>">
                </div>
            </div>
            <div class="field">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="2" data-required="true"><?php
                    echo htmlspecialchars($tenant['address']);
                ?></textarea>
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="monthly_rent">Monthly rent</label>
                    <input type="number" step="0.01" id="monthly_rent" name="monthly_rent" data-required="true"
                           value="<?php echo htmlspecialchars((string)$tenant['monthly_rent']); ?>">
                </div>
                <div class="field">
                    <label for="deposit">Deposit</label>
                    <input type="number" step="0.01" id="deposit" name="deposit" data-required="true"
                           value="<?php echo htmlspecialchars((string)$tenant['deposit']); ?>">
                </div>
            </div>
            <div class="field">
                <label for="move_in_date" id="move_date_label">Move-in date</label>
                <input type="date" id="move_in_date" name="move_in_date" data-required="true"
                       value="<?php echo htmlspecialchars($tenant['move_in_date']); ?>">
                <span class="helper-text" id="move_date_help">
                    Set the date the tenant first occupies the unit.
                </span>
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?php echo $tenant['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="moved_out" <?php echo $tenant['status'] === 'moved_out' ? 'selected' : ''; ?>>
                        Moved out
                    </option>
                </select>
            </div>
            <button type="submit" class="btn">
                <?php echo $isEdit ? 'Save changes' : 'Create tenant'; ?>
            </button>
        </div>
    </form>
</section>
<script>
    (function () {
        const statusSelect = document.getElementById('status');
        const dateLabel = document.getElementById('move_date_label');
        const dateHelp = document.getElementById('move_date_help');

        function updateDateLabel() {
            if (!statusSelect || !dateLabel || !dateHelp) return;
            if (statusSelect.value === 'moved_out') {
                dateLabel.textContent = 'Move-out date';
                dateHelp.textContent = 'Set the date the tenant moved out of the unit.';
            } else {
                dateLabel.textContent = 'Move-in date';
                dateHelp.textContent = 'Set the date the tenant first occupies the unit.';
            }
        }

        if (statusSelect) {
            statusSelect.addEventListener('change', updateDateLabel);
            // Initialise on load to reflect current status (especially when editing)
            updateDateLabel();
        }
    })();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>

