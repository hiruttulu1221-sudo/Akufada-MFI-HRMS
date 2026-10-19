<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_role(['Admin']);
$title = 'Role Account Setup';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><h2>Role Account Setup</h2></div></div>
<div class="card">
<p>For initial installation or account recovery, use <code>setup/upgrade_roles.php</code> once, then delete the <code>setup</code> folder.</p>
<p>Do not run the setup script again after users have changed their passwords, because it resets the demo account passwords.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
