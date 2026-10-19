<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

function is_logged_in(): bool { return !empty($_SESSION['user']); }
function require_login(): void {
    if (!is_logged_in()) { header('Location: ' . APP_URL . '/index.php'); exit; }
}
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function login_user(string $username, string $password): bool {
    $stmt = db()->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$username]); $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']); $_SESSION['user'] = $user;
        db()->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
        return true;
    }
    return false;
}
function logout_user(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}
function require_role(array $roles): void {
    require_login();
    if (!in_array($_SESSION['user']['role'], $roles, true)) { http_response_code(403); exit('Access denied'); }
}
function role_is(string ...$roles): bool { return is_logged_in() && in_array($_SESSION['user']['role'], $roles, true); }
function role_home(?string $role = null): string {
    $role = $role ?? ($_SESSION['user']['role'] ?? 'ESS');
    return match ($role) {
        'Admin', 'HR Admin', 'Supervisor' => APP_URL . '/dashboard.php',
        'Payroll' => APP_URL . '/payroll/index.php',
        default => APP_URL . '/hr/profile.php',
    };
}
