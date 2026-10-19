<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_login();

$pdo = db();
$user = current_user();
$error = '';
$success = isset($_GET['changed']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([(int)$user['id']]);
    $account = $stmt->fetch();

    if (!$account || !password_verify($current, (string)$account['password_hash'])) {
        $error = 'Your current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'Your new password must contain at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'The new password and confirmation do not match.';
    } elseif ($current === $new) {
        $error = 'Choose a new password different from your current password.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ? AND is_active = 1');
        $stmt->execute([$hash, (int)$user['id']]);
        redirect(APP_URL . '/change_password.php?changed=1');
    }
}

$title = 'Change Password';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div><h2>Change Password</h2><p>Change the password for your own Akufada HRMS account.</p></div>
</div>
<?php if ($success): ?><div class="alert success">Password changed successfully. Use your new password the next time you log in.</div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert danger"><?= e($error) ?></div><?php endif; ?>
<div class="card" style="max-width:680px">
  <h3>Update your password</h3>
  <form method="post" action="<?= e(APP_URL) ?>/change_password.php" autocomplete="off">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <label>Current password<input type="password" name="current_password" autocomplete="current-password" required></label>
    <label>New password<input type="password" name="new_password" minlength="8" autocomplete="new-password" required><small>Use at least 8 characters.</small></label>
    <label>Confirm new password<input type="password" name="confirm_password" minlength="8" autocomplete="new-password" required></label>
    <button class="btn primary" type="submit">Update password</button>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
