<?php
// Tenant profile: rents, electricity, issues
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$pdo = getPDO();

$tenantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($tenantId <= 0) {
    header('Location: tenants.php');
    exit;
}

// Fetch tenant basic info
$stmt = $pdo->prepare('SELECT * FROM tenants WHERE id = :id');
$stmt->execute(['id' => $tenantId]);
$tenant = $stmt->fetch();

if (!$tenant) {
    header('Location: tenants.php');
    exit;
}

$error = '';
$success = '';

// Handle adds for the three sections
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'rent') {
        $rentMonth = (int)($_POST['rent_month'] ?? 0);
        $rentYear = (int)($_POST['rent_year'] ?? (int)date('Y'));
        $amount = trim($_POST['amount'] ?? '');
        $dateGiven = trim($_POST['date_given'] ?? '');
        $mode = $_POST['mode'] ?? 'cash';

        if ($rentMonth < 1 || $rentMonth > 12 || $rentYear <= 0 || $amount === '' || $dateGiven === '') {
            $error = 'Please fill in all required rent fields.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO tenant_rents (tenant_id, rent_month, rent_year, amount, date_given, mode)
                 VALUES (:tenant_id, :rent_month, :rent_year, :amount, :date_given, :mode)'
            );
            $stmt->execute([
                'tenant_id' => $tenantId,
                'rent_month' => $rentMonth,
                'rent_year' => $rentYear,
                'amount' => $amount,
                'date_given' => $dateGiven,
                'mode' => $mode,
            ]);
            $success = 'Rent record added.';
        }
    } elseif ($formType === 'electricity') {
        $billMonth = (int)($_POST['bill_month'] ?? 0);
        $billYear = (int)($_POST['bill_year'] ?? (int)date('Y'));
        $dateGiven = trim($_POST['date_given'] ?? '');
        $paidBy = $_POST['paid_by'] ?? 'tenant';
        $previousUnits = $_POST['previous_units'] !== '' ? (int)$_POST['previous_units'] : null;
        $previousUnitsDate = $_POST['previous_units_date'] !== '' ? $_POST['previous_units_date'] : null;
        $latestUnits = $_POST['latest_units'] !== '' ? (int)$_POST['latest_units'] : null;
        $latestUnitsDate = $_POST['latest_units_date'] !== '' ? $_POST['latest_units_date'] : null;

        if ($billMonth < 1 || $billMonth > 12 || $billYear <= 0 || $dateGiven === '') {
            $error = 'Please fill in the basic electricity bill fields (month, year, date, paid by).';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO tenant_electricity_bills
                 (tenant_id, bill_month, bill_year, date_given, paid_by,
                  previous_units, previous_units_date, latest_units, latest_units_date)
                 VALUES
                 (:tenant_id, :bill_month, :bill_year, :date_given, :paid_by,
                  :previous_units, :previous_units_date, :latest_units, :latest_units_date)'
            );
            $stmt->execute([
                'tenant_id' => $tenantId,
                'bill_month' => $billMonth,
                'bill_year' => $billYear,
                'date_given' => $dateGiven,
                'paid_by' => $paidBy,
                'previous_units' => $previousUnits,
                'previous_units_date' => $previousUnitsDate,
                'latest_units' => $latestUnits,
                'latest_units_date' => $latestUnitsDate,
            ]);
            $success = 'Electricity bill record added.';
        }
    } elseif ($formType === 'issue') {
        $description = trim($_POST['description'] ?? '');
        $raisedDate = trim($_POST['raised_date'] ?? '');
        $status = $_POST['status'] ?? 'open';
        $solvedDate = $_POST['solved_date'] !== '' ? $_POST['solved_date'] : null;

        if ($description === '' || $raisedDate === '') {
            $error = 'Please enter the issue description and raised date.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO tenant_issues (tenant_id, description, raised_date, status, solved_date)
                 VALUES (:tenant_id, :description, :raised_date, :status, :solved_date)'
            );
            $stmt->execute([
                'tenant_id' => $tenantId,
                'description' => $description,
                'raised_date' => $raisedDate,
                'status' => $status,
                'solved_date' => $solvedDate,
            ]);
            $success = 'Issue recorded.';
        }
    }
}

// Fetch related data
$rentsStmt = $pdo->prepare(
    'SELECT rent_month, rent_year, amount, date_given, mode
     FROM tenant_rents
     WHERE tenant_id = :tenant_id
     ORDER BY rent_year DESC, rent_month DESC, date_given DESC'
);
$rentsStmt->execute(['tenant_id' => $tenantId]);
$rents = $rentsStmt->fetchAll();

$elecStmt = $pdo->prepare(
    'SELECT bill_month, bill_year, date_given, paid_by,
            previous_units, previous_units_date, latest_units, latest_units_date
     FROM tenant_electricity_bills
     WHERE tenant_id = :tenant_id
     ORDER BY bill_year DESC, bill_month DESC, date_given DESC'
);
$elecStmt->execute(['tenant_id' => $tenantId]);
$bills = $elecStmt->fetchAll();

$issuesStmt = $pdo->prepare(
    'SELECT description, raised_date, status, solved_date
     FROM tenant_issues
     WHERE tenant_id = :tenant_id
     ORDER BY raised_date DESC'
);
$issuesStmt->execute(['tenant_id' => $tenantId]);
$issues = $issuesStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <div>
            <div class="card-title"><?php echo htmlspecialchars($tenant['full_name']); ?></div>
            <div class="card-subtitle">
                Property: <?php echo htmlspecialchars($tenant['property_name']); ?> ·
                Rent: <?php echo number_format((float)$tenant['monthly_rent'], 2); ?> ·
                Status:
                <?php if ($tenant['status'] === 'active'): ?>
                    <span class="pill pill-active">Active</span>
                <?php else: ?>
                    <span class="pill pill-moved">Moved out</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-actions">
            <a href="tenants.php" class="btn btn-outline">Back to tenants</a>
            <a href="tenant_form.php?id=<?php echo $tenantId; ?>" class="btn">Edit tenant</a>
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

    <div class="grid-two">
        <div>
            <h3 style="font-size: 0.95rem; margin: 0.2rem 0 0.5rem;">Rent history</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>Month</th>
                        <th>Date given</th>
                        <th>Amount</th>
                        <th>Mode</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$rents): ?>
                        <tr>
                            <td colspan="4">No rent records yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rents as $rent): ?>
                            <tr>
                                <td>
                                    <?php
                                    $monthIndex = (int)$rent['rent_month'];
                                    $monthNames = [
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                    ];
                                    $label = ($monthNames[$monthIndex] ?? $monthIndex) . ' ' . (int)$rent['rent_year'];
                                    echo htmlspecialchars($label);
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($rent['date_given']); ?></td>
                                <td><?php echo number_format((float)$rent['amount'], 2); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($rent['mode'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <form method="post" data-validate="true" style="margin-top: 0.75rem;">
                <input type="hidden" name="form_type" value="rent">
                <div class="form-row">
                    <div class="field">
                        <label for="rent_month">Month</label>
                        <select id="rent_month" name="rent_month" data-required="true">
                            <?php
                            $monthNames = [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                            ];
                            foreach ($monthNames as $value => $name): ?>
                                <option value="<?php echo $value; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="rent_year">Year</label>
                        <input type="number" id="rent_year" name="rent_year" data-required="true"
                               value="<?php echo (int)date('Y'); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="amount">Amount</label>
                        <input type="number" step="0.01" id="amount" name="amount" data-required="true">
                    </div>
                    <div class="field">
                        <label for="date_given">Date given</label>
                        <input type="date" id="date_given" name="date_given" data-required="true"
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="field">
                    <label for="mode">Mode</label>
                    <select id="mode" name="mode">
                        <option value="online">Online</option>
                        <option value="cash" selected>Cash</option>
                    </select>
                </div>
                <button type="submit" class="btn">Add rent record</button>
            </form>
        </div>

        <div>
            <h3 style="font-size: 0.95rem; margin: 0.2rem 0 0.5rem;">Electricity bills</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>Bill month</th>
                        <th>Date given</th>
                        <th>Paid by</th>
                        <th>Prev / Latest units</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$bills): ?>
                        <tr>
                            <td colspan="4">No electricity bills recorded.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td>
                                    <?php
                                    $billMonthIndex = (int)$bill['bill_month'];
                                    $monthNames = [
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                    ];
                                    $label = ($monthNames[$billMonthIndex] ?? $billMonthIndex) . ' ' . (int)$bill['bill_year'];
                                    echo htmlspecialchars($label);
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($bill['date_given']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($bill['paid_by'])); ?></td>
                                <td>
                                    <?php if ($bill['previous_units'] !== null): ?>
                                        Prev: <?php echo (int)$bill['previous_units']; ?>
                                        <?php if ($bill['previous_units_date']): ?>
                                            (<?php echo htmlspecialchars($bill['previous_units_date']); ?>)
                                        <?php endif; ?>
                                        <br>
                                    <?php endif; ?>
                                    <?php if ($bill['latest_units'] !== null): ?>
                                        Latest: <?php echo (int)$bill['latest_units']; ?>
                                        <?php if ($bill['latest_units_date']): ?>
                                            (<?php echo htmlspecialchars($bill['latest_units_date']); ?>)
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <form method="post" data-validate="true" style="margin-top: 0.75rem;">
                <input type="hidden" name="form_type" value="electricity">
                <div class="form-row">
                    <div class="field">
                        <label for="bill_month">Bill month</label>
                        <select id="bill_month" name="bill_month" data-required="true">
                            <?php
                            $monthNames = [
                                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                            ];
                            foreach ($monthNames as $value => $name): ?>
                                <option value="<?php echo $value; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="bill_year">Bill year</label>
                        <input type="number" id="bill_year" name="bill_year" data-required="true"
                               value="<?php echo (int)date('Y'); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="date_given_elec">Date given</label>
                        <input type="date" id="date_given_elec" name="date_given" data-required="true"
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="field">
                        <label for="paid_by">Paid by</label>
                        <select id="paid_by" name="paid_by">
                            <option value="tenant" selected>Tenant</option>
                            <option value="landlord">Landlord</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="previous_units">Previous units</label>
                        <input type="number" id="previous_units" name="previous_units">
                    </div>
                    <div class="field">
                        <label for="previous_units_date">Previous units date</label>
                        <input type="date" id="previous_units_date" name="previous_units_date">
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="latest_units">Latest units</label>
                        <input type="number" id="latest_units" name="latest_units">
                    </div>
                    <div class="field">
                        <label for="latest_units_date">Latest units date</label>
                        <input type="date" id="latest_units_date" name="latest_units_date">
                    </div>
                </div>
                <button type="submit" class="btn">Add electricity bill</button>
            </form>
        </div>
    </div>

    <div style="margin-top: 1.5rem;">
        <h3 style="font-size: 0.95rem; margin: 0.2rem 0 0.5rem;">Issues</h3>
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>Description</th>
                    <th>Raised date</th>
                    <th>Status</th>
                    <th>Solved date</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$issues): ?>
                    <tr>
                        <td colspan="4">No issues recorded.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($issues as $issue): ?>
                        <tr>
                            <td><?php echo nl2br(htmlspecialchars($issue['description'])); ?></td>
                            <td><?php echo htmlspecialchars($issue['raised_date']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($issue['status'])); ?></td>
                            <td><?php echo $issue['solved_date'] ? htmlspecialchars($issue['solved_date']) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <form method="post" data-validate="true" style="margin-top: 0.75rem;">
            <input type="hidden" name="form_type" value="issue">
            <div class="field">
                <label for="description">Issue description</label>
                <textarea id="description" name="description" rows="2" data-required="true"></textarea>
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="raised_date">Raised date</label>
                    <input type="date" id="raised_date" name="raised_date" data-required="true"
                           value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="field">
                    <label for="status_issue">Status</label>
                    <select id="status_issue" name="status">
                        <option value="open" selected>Open</option>
                        <option value="in_progress">In progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label for="solved_date">Solved date (if resolved)</label>
                <input type="date" id="solved_date" name="solved_date">
            </div>
            <button type="submit" class="btn">Add issue</button>
        </form>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

