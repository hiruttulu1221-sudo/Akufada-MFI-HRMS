<?php
require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/helpers.php';require_role(['Admin','HR Admin','Payroll']);
$pdo=db();$title='Deduction Types';require __DIR__.'/../includes/header.php';?>
<div class="page-head"><div><h2>Deduction Types</h2><p>Configure payroll deductions.</p></div></div><div class="card"><table><tr><th>Name</th><th>Method</th><th>Value</th></tr><?php foreach($pdo->query("SELECT * FROM deduction_types ORDER BY id") as $r):?><tr><td><?=e($r['name'])?></td><td><?=e($r['method'])?></td><td><?=e((string)$r['value'])?></td></tr><?php endforeach;?></table></div>
<?php require __DIR__.'/../includes/footer.php';?>
