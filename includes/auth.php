<?php
// Authentication helpers and session management.

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to login page if user is not authenticated.
 */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Returns the current logged-in user's name, if available.
 */
function current_user_name(): ?string
{
    return $_SESSION['user_name'] ?? null;
}

/**
 * Log the user out and destroy the session.
 */
function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Location: index.php');
    exit;
}

