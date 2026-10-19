<?php
require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/helpers.php';require_role(['Admin']);
$pdo=db();$title='Users';require __DIR__.'/../includes/header.php';?>
<div class="page-head"><div><h2>Users</h2><p>System users and access roles.</p></div></div>
<div class="card"><table><tr><th>Username</th><th>Name</th><th>Role</th><th>Status</th><th>Last Login</th></tr><?php foreach($pdo->query("SELECT username,full_name,role,is_active,last_login FROM users ORDER BY id") as $r):?><tr><td><?=e($r['username'])?></td><td><?=e($r['full_name'])?></td><td><?=e($r['role'])?></td><td><?=e($r['is_active']?'Active':'Disabled')?></td><td><?=e($r['last_login']??'Never')?></td></tr><?php endforeach;?></table></div>
<?php require __DIR__.'/../includes/footer.php';?>
