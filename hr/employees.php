<?php
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/helpers.php';
require_role(['Admin','HR Admin']);
$pdo=db(); $message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf($_POST['csrf']??null);
 $stmt=$pdo->prepare("INSERT INTO employees(employee_code,full_name,gender,department,position,branch,phone,email,hire_date,status) VALUES(?,?,?,?,?,?,?,?,?,?)");
 $stmt->execute([trim($_POST['employee_code']),trim($_POST['full_name']),$_POST['gender'],$_POST['department'],trim($_POST['position']),trim($_POST['branch']),trim($_POST['phone']),trim($_POST['email']),$_POST['hire_date'],$_POST['status']]);
 $message='Employee created successfully.';
}
$q=trim($_GET['q']??''); $stmt=$pdo->prepare("SELECT * FROM employees WHERE full_name LIKE ? OR employee_code LIKE ? OR department LIKE ? ORDER BY id DESC"); $like="%$q%"; $stmt->execute([$like,$like,$like]); $rows=$stmt->fetchAll();
$title='Employees'; require __DIR__.'/../includes/header.php';
?>
<div class="page-head"><div><h2>Employees</h2><p>Employee master data and HR records.</p></div></div>
<?php if($message): ?><div class="alert success"><?=e($message)?></div><?php endif;?>
<div class="grid two">
<div class="card"><h3>Add Employee</h3><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="form-grid">
<label>Employee ID<input name="employee_code" required></label><label>Full Name<input name="full_name" required></label>
<label>Gender<select name="gender"><option>Male</option><option>Female</option></select></label>
<label>Department<input name="department" required></label><label>Position<input name="position" required></label><label>Branch<input name="branch" value="Head Office"></label>
<label>Phone<input name="phone"></label><label>Email<input type="email" name="email"></label><label>Hire Date<input type="date" name="hire_date" required></label>
<label>Status<select name="status"><option>Active</option><option>On Leave</option><option>Terminated</option></select></label>
</div><button class="btn primary">Save Employee</button></form></div>
<div class="card"><h3>Employee Directory</h3><form class="toolbar"><input name="q" value="<?=e($q)?>" placeholder="Search name, ID, department..."><button class="btn secondary">Search</button></form>
<div class="table-wrap"><table><tr><th>ID</th><th>Employee</th><th>Department</th><th>Position</th><th>Branch</th><th>Status</th></tr>
<?php foreach($rows as $r):?><tr><td><?=e($r['employee_code'])?></td><td><b><?=e($r['full_name'])?></b><small><?=e($r['phone'])?></small></td><td><?=e($r['department'])?></td><td><?=e($r['position'])?></td><td><?=e($r['branch'])?></td><td><span class="badge green"><?=e($r['status'])?></span></td></tr><?php endforeach;?>
</table></div></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
