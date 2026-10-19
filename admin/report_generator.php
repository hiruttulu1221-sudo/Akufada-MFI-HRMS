<?php
require_once __DIR__.'/../includes/auth.php'; require_once __DIR__.'/../includes/helpers.php'; require_role(['Admin']);
$title='Report Generator'; $pdo=db();
$type=$_GET['type']??'employees'; $allowed=['employees','leave','payroll','recruitment']; if(!in_array($type,$allowed,true))$type='employees';
$rows=[]; $headers=[]; $sql='';
switch($type){
case 'leave': $headers=['Employee','Leave type','Start','End','Days','Status']; $sql="SELECT e.full_name AS Employee,l.leave_type AS `Leave type`,l.start_date AS Start,l.end_date AS End,l.days AS Days,l.status AS Status FROM leave_requests l JOIN employees e ON e.id=l.employee_id ORDER BY l.created_at DESC"; break;
case 'payroll': $headers=['Employee','Month','Basic salary','Benefits','Deductions','Net salary','Status']; $sql="SELECT e.full_name AS Employee,p.payroll_month AS Month,p.basic_salary AS `Basic salary`,p.benefits AS Benefits,p.deductions AS Deductions,p.net_salary AS `Net salary`,p.status AS Status FROM payroll_records p JOIN employees e ON e.id=p.employee_id ORDER BY p.payroll_month DESC"; break;
case 'recruitment': $headers=['Employee code','Full name','Department','Position','Hire date','Status']; $sql="SELECT employee_code AS `Employee code`,full_name AS `Full name`,department AS Department,position AS Position,hire_date AS `Hire date`,status AS Status FROM employees ORDER BY hire_date DESC"; break;
default: $headers=['Employee code','Full name','Department','Position','Branch','Phone','Email','Hire date','Status']; $sql="SELECT employee_code AS `Employee code`,full_name AS `Full name`,department AS Department,position AS Position,branch AS Branch,phone AS Phone,email AS Email,hire_date AS `Hire date`,status AS Status FROM employees ORDER BY full_name";
}
$rows=$pdo->query($sql)->fetchAll();
if(isset($_GET['export']) && $_GET['export']==='csv') { header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="akufada_'.$type.'_report.csv"'); $out=fopen('php://output','w'); fputcsv($out,$headers); foreach($rows as $r){$line=[];foreach($headers as $h)$line[]=$r[$h]??'';fputcsv($out,$line);}fclose($out);exit; }
require __DIR__.'/../includes/header.php';
?>
<div class="page-head"><div><h2><?=e($title)?></h2><p>Build and export a live report from HRMS records.</p></div></div>
<div class="card"><form method="get"><label>Report type</label><select name="type"><?php foreach(['employees'=>'Employee register','leave'=>'Leave requests','payroll'=>'Payroll records','recruitment'=>'Recruitment / hired employees'] as $k=>$v): ?><option value="<?=e($k)?>" <?=$type===$k?'selected':''?>><?=e($v)?></option><?php endforeach; ?></select> <button type="submit">Generate report</button> <a href="?type=<?=e($type)?>&export=csv">Export CSV</a></form></div>
<div class="card"><h3>Results: <?=count($rows)?></h3><div class="table-wrap"><table><thead><tr><?php foreach($headers as $h): ?><th><?=e($h)?></th><?php endforeach; ?></tr></thead><tbody><?php if(!$rows): ?><tr><td colspan="<?=count($headers)?>">No matching records found.</td></tr><?php endif; ?><?php foreach($rows as $r): ?><tr><?php foreach($headers as $h): ?><td><?=e((string)($r[$h]??''))?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>