<?php
require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/helpers.php';require_role(['Admin','HR Admin','Supervisor','Payroll']);$pdo=db();$title='Reports';require __DIR__.'/../includes/header.php';
$reports=['HR Reports','Transferred Employees','Terminated Employees','Recruited Employees','Employee Headcount','Leave Summary','Recruitment Summary','Performance Summary','Payroll Summary','Loan Summary','Deduction Summary'];
?>
<div class="page-head"><div><h2>Reports</h2><p>Management reports for HR, payroll, inventory and administration.</p></div></div>
<div class="grid three"><?php foreach($reports as $r):?><div class="card"><h3><?=e($r)?></h3><p class="muted">Generate or view <?=e(strtolower($r))?>.</p><a class="btn secondary" href="export.php?report=<?=urlencode($r)?>">Export CSV</a></div><?php endforeach;?></div>
<?php require __DIR__.'/../includes/footer.php';?>
