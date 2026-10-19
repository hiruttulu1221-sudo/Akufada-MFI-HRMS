<?php
require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/helpers.php';require_role(['Admin']);
$pdo=db();$title='Logs';require __DIR__.'/../includes/header.php';?>
<div class="page-head"><div><h2>System Logs</h2><p>Audit activity for administrative review.</p></div></div>
<div class="card"><table><tr><th>Date</th><th>User</th><th>Action</th><th>Module</th><th>IP</th></tr><?php foreach($pdo->query("SELECT l.*,u.username FROM audit_logs l LEFT JOIN users u ON u.id=l.user_id ORDER BY l.id DESC LIMIT 100") as $r):?><tr><td><?=e($r['created_at'])?></td><td><?=e($r['username']??'system')?></td><td><?=e($r['action'])?></td><td><?=e($r['module'])?></td><td><?=e($r['ip_address'])?></td></tr><?php endforeach;?></table></div>
<?php require __DIR__.'/../includes/footer.php';?>
