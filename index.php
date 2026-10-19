<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (is_logged_in()) { header('Location: ' . role_home()); exit; }


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);

    if (login_user(
        trim($_POST['username'] ?? ''),
        $_POST['password'] ?? ''
    )) {
        header('Location: ' . role_home());
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}


?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e(APP_NAME) ?></title><link rel="stylesheet" href="assets/css/app.css"></head>
<body class="login-bg"><div class="login-card">
<div class="brand-icon big">A</div><h1>Akufada MFI</h1><p>Human Resource Management System</p>
<?php if($error): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="login-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Username<input name="username" value="admin" required></label>
<label>Password<input type="password" name="password" value="admin123" required></label>
<button class="btn primary full">Sign In</button></form>
<div class="alert info" style="margin-top:14px"><b>Role accounts:</b><br>Admin: admin / admin123<br>HR Admin: hradmin / hradmin123<br>Supervisor: supervisor / supervisor123<br>Payroll: payroll / payroll123<br>ESS: ess / ess123</div></div></body></html>
