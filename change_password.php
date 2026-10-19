
<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_login();

$pdo = db();
$user = current_user();
$title = 'Change Password';

$message = '';
$messageType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (
        !isset($user['id']) ||
        (int)$user['id'] < 1
    ) {
        $message = 'Unable to identify your account. Please log in again.';
    } elseif (
        $currentPassword === '' ||
        $newPassword === '' ||
        $confirmPassword === ''
    ) {
        $message = 'Please complete all password fields.';
    } elseif (
        !is_string($newPassword) ||
        strlen($newPassword) < 8
    ) {
        $message = 'Your new password must contain at least 8 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'The new password and confirmation do not match.';
    } elseif ($currentPassword === $newPassword) {
        $message = 'Your new password must be different from your current password.';
    } else {
        // Read the password hash for the logged-in account only.
        $stmt = $pdo->prepare(
            'SELECT password_hash FROM users WHERE id = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([(int)$user['id']]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            !$account ||
            !password_verify($currentPassword, $account['password_hash'])
        ) {
            $message = 'Your current password is incorrect.';
        } else {
            // Hash the new password securely.
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

            if ($newHash === false) {
                $message = 'Unable to secure the new password. Please try again.';
            } else {
                $update = $pdo->prepare(
                    'UPDATE users SET password_hash = ? WHERE id = ? AND is_active = 1'
                );
                $update->execute([$newHash, (int)$user['id']]);

                if ($update->rowCount() === 1) {
                    $message = 'Your password has been changed successfully.';
                    $messageType = 'success';
                } else {
                    // Confirm the update if the database reports no changed rows.
                    $check = $pdo->prepare(
                        'SELECT password_hash FROM users WHERE id = ? AND is_active = 1 LIMIT 1'
                    );
                    $check->execute([(int)$user['id']]);
                    $saved = $check->fetch(PDO::FETCH_ASSOC);

                    if (
                        $saved &&
                        password_verify($newPassword, $saved['password_hash'])
                    ) {
                        $message = 'Your password has been changed successfully.';
                        $messageType = 'success';
                    } else {
                        $message = 'The password could not be updated. Please try again.';
                    }
                }
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h2><?= e($title) ?></h2>
        <p>Akufada Microfinance Institution • Head Office</p>
    </div>
</div>

<div class="card">
    <h3>Change Your Password</h3>
    <p>Enter your current password and choose a new password.</p>

    <?php if ($message !== ''): ?>
        <div class="alert <?= e($messageType) ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <input
            type="hidden"
            name="csrf"
            value="<?= e(csrf_token()) ?>"
        >

        <label for="current_password">Current Password</label>
        <input
            type="password"
            id="current_password"
            name="current_password"
            autocomplete="current-password"
            required
        >

        <label for="new_password">New Password</label>
        <input
            type="password"
            id="new_password"
            name="new_password"
            minlength="8"
            autocomplete="new-password"
            required
        >
        <small>Use at least 8 characters.</small>

        <label for="confirm_password">Confirm New Password</label>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <button type="submit" class="btn primary">
            Change Password
        </button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
