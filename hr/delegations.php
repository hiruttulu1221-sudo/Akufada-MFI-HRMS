<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['HR Admin','Admin']);
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS delegations (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 employee_id INT UNSIGNED NOT NULL,
 delegate_employee_id INT UNSIGNED NOT NULL,
 start_date DATE NOT NULL,
 end_date DATE NOT NULL,
 responsibilities TEXT NULL,
 status ENUM('Active','Completed','Cancelled') NOT NULL DEFAULT 'Active',
 created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_delegation_dates(start_date,end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$error=''; $message='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf($_POST['csrf'] ?? null);
 $action=$_POST['action'] ?? 'save'; $id=(int)($_POST['id'] ?? 0);
 if ($action==='status' && $id>0 && in_array($_POST['status'] ?? '', ['Active','Completed','Cancelled'], true)) {
  $st=$pdo->prepare('UPDATE delegations SET status=? WHERE id=?'); $st->execute([$_POST['status'],$id]);
  redirect(APP_URL.'/hr/delegations.php?saved=1');
 }
 $employee=(int)($_POST['employee_id'] ?? 0); $delegate=(int)($_POST['delegate_employee_id'] ?? 0);
 $start=$_POST['start_date'] ?? ''; $end=$_POST['end_date'] ?? ''; $resp=trim($_POST['responsibilities'] ?? '');
 if (!$employee || !$delegate || $employee===$delegate || !$start || !$end || $end<$start) $error='Choose two different employees and a valid date range.';
 else {
  $check=$pdo->prepare("SELECT COUNT(*) FROM employees WHERE id IN (?,?) AND status='Active'"); $check->execute([$employee,$delegate]);
  if ((int)$check->fetchColumn()!==2) $error='Both employees must exist and be active.';
  else { if ($id>0) { $st=$pdo->prepare('UPDATE delegations SET employee_id=?,delegate_employee_id=?,start_date=?,end_date=?,responsibilities=? WHERE id=?'); $st->execute([$employee,$delegate,$start,$end,$resp,$id]); }
   else { $st=$pdo->prepare('INSERT INTO delegations(employee_id,delegate_employee_id,start_date,end_date,responsibilities,created_by) VALUES(?,?,?,?,?,?)'); $st->execute([$employee,$delegate,$start,$end,$resp,current_user()['id'] ?? null]); }
   redirect(APP_URL.'/hr/delegations.php?saved=1'); }
 }
}
$employees=$pdo->query("SELECT id,employee_code,full_name,department FROM employees WHERE status='Active' ORDER BY full_name")->fetchAll();
$edit=null; if(isset($_GET['edit'])) { $st=$pdo->prepare('SELECT * FROM delegations WHERE id=?'); $st->execute([(int)$_GET['edit']]); $edit=$st->fetch() ?: null; }
$rows=$pdo->query('SELECT d.*,e.full_name AS employee_name,e.employee_code AS employee_code,x.full_name AS delegate_name,x.employee_code AS delegate_code FROM delegations d JOIN employees e ON e.id=d.employee_id JOIN employees x ON x.id=d.delegate_employee_id ORDER BY d.id DESC')->fetchAll();
$title='Delegations'; require __DIR__.'/../includes/header.php'; ?>
<div class="page-head"><div><h2>Delegations</h2><p>Assign temporary responsibilities between active employees.</p></div></div>
<?php if(isset($_GET['saved'])):?><div class="alert success">Delegation saved.</div><?php endif;?><?php if($error):?><div class="alert danger"><?=e($error)?></div><?php endif;?>
<div class="card"><h3><?= $edit ? 'Edit delegation' : 'Create delegation' ?></h3><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>"><input type="hidden" name="action" value="save"><div class="form-grid">
<label>Employee<select name="employee_id" required><option value="">Choose employee</option><?php foreach($employees as $emp):?><option value="<?=$emp['id']?>" <?=((int)($edit['employee_id']??0)===(int)$emp['id']?'selected':'')?>><?=e($emp['employee_code'].' — '.$emp['full_name'])?></option><?php endforeach;?></select></label>
<label>Delegated to<select name="delegate_employee_id" required><option value="">Choose delegate</option><?php foreach($employees as $emp):?><option value="<?=$emp['id']?>" <?=((int)($edit['delegate_employee_id']??0)===(int)$emp['id']?'selected':'')?>><?=e($emp['employee_code'].' — '.$emp['full_name'])?></option><?php endforeach;?></select></label>
<label>Start date<input type="date" name="start_date" required value="<?=e($edit['start_date']??'')?>"></label><label>End date<input type="date" name="end_date" required value="<?=e($edit['end_date']??'')?>"></label></div><label>Responsibilities / notes<textarea name="responsibilities" rows="3"><?=e($edit['responsibilities']??'')?></textarea></label><button class="btn primary" type="submit">Save delegation</button><?php if($edit):?> <a class="btn" href="delegations.php">Cancel edit</a><?php endif;?></form></div>
<div class="card"><h3>Delegation records (<?=count($rows)?>)</h3><div class="table-wrap"><table><thead><tr><th>Employee</th><th>Delegate</th><th>Period</th><th>Responsibilities</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php if(!$rows):?><tr><td colspan="6">No delegations recorded.</td></tr><?php endif;?><?php foreach($rows as $r):?><tr><td><?=e($r['employee_code'].' — '.$r['employee_name'])?></td><td><?=e($r['delegate_code'].' — '.$r['delegate_name'])?></td><td><?=e($r['start_date'].' to '.$r['end_date'])?></td><td><?=e($r['responsibilities']??'')?></td><td><?=e($r['status'])?></td><td><a class="btn small" href="?edit=<?=(int)$r['id']?>">Edit</a><form method="post" style="display:inline"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><input type="hidden" name="action" value="status"><select name="status" aria-label="Delegation status"><option <?= $r['status']==='Active'?'selected':'' ?>>Active</option><option <?= $r['status']==='Completed'?'selected':'' ?>>Completed</option><option <?= $r['status']==='Cancelled'?'selected':'' ?>>Cancelled</option></select><button class="btn small" type="submit">Update</button></form></td></tr><?php endforeach;?></tbody></table></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>