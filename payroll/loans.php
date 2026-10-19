<?php
require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/helpers.php';require_role(['Admin','HR Admin','Payroll']);
$pdo=db();$title='Loan Types';require __DIR__.'/../includes/header.php';?>
<div class="page-head"><div><h2>Loan Types</h2><p>Configure employee loan products.</p></div></div><div class="card"><table><tr><th>Name</th><th>Maximum Amount</th><th>Interest %</th></tr><?php foreach($pdo->query("SELECT * FROM loan_types ORDER BY id") as $r):?><tr><td><?=e($r['name'])?></td><td><?=number_format((float)$r['max_amount'],2)?></td><td><?=e((string)$r['interest_rate'])?></td></tr><?php endforeach;?></table></div>
<?php require __DIR__.'/../includes/footer.php';?>
