<?php
// User registration
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $pdo = getPDO();

        // Check if email already exists
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute(['email' => $email]);
        if ($check->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, :role)');
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'hash' => $hash,
                'role' => 'admin',
            ]);
            $success = 'Registration successful. You can now log in.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Create an account</div>
            <div class="card-subtitle">Set up the first admin user for your tenant management system.</div>
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
                    <label for="name">Full name</label>
                    <input type="text" id="name" name="name" data-required="true"
                           value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" data-required="true"
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" data-required="true">
                    <span class="helper-text">At least 6 characters.</span>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm password</label>
                    <input type="password" id="confirm_password" name="confirm_password" data-required="true">
                </div>
            </div>
            <button type="submit" class="btn">Register</button>
            <p class="helper-text">
                Already have an account?
                <a href="index.php">Return to login</a>.
            </p>
        </div>
    </form>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

