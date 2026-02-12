<?php
// Login page
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="card">
    <div class="grid-two">
        <div class="auth-layout">
            <div>
                <div class="auth-hero-title">Welcome back, property manager.</div>
                <p class="auth-hero-text">
                    Sign in to access your unified view of tenants, units, and rent status – all in one clean dashboard.
                </p>
            </div>
            <div class="auth-metadata">
                <span class="badge badge-success">Secure sessions</span>
                <span class="badge">Hashed passwords</span>
                <span class="badge badge-warning">Server-side validation</span>
            </div>
        </div>
        <div class="auth-panel">
            <div class="card-header">
                <div>
                    <div class="card-title">Login</div>
                    <div class="card-subtitle">Enter your credentials to continue.</div>
                </div>
            </div>
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form method="post" data-validate="true">
                <div class="form">
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" data-required="true"
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" data-required="true">
                    </div>
                    <button type="submit" class="btn">Sign in</button>
                    <p class="helper-text">
                        No account yet?
                        <a href="register.php">Create one now</a>.
                    </p>
                </div>
            </form>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>

