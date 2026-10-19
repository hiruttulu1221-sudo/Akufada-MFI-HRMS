<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['Payroll', 'Admin']);
$pdo=db();
$pdo->exec("CREATE TABLE IF NOT EXISTS hr_module_records (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, module_key VARCHAR(100) NOT NULL, title VARCHAR(190) NOT NULL, details TEXT NULL, status VARCHAR(40) NOT NULL DEFAULT 'Active', effective_date DATE NULL, created_by INT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_hr_module_key(module_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$key='payroll_approve_monthly'; $title='Approve Monthly Payroll'; $label='Monthly Payroll Approval'; $description='Review monthly payroll records and approve eligible drafts.'; $error='';
if($_SERVER['REQUEST_METHOD']==='POST') { verify_csrf($_POST['csrf']??null); try {
 $action=$_POST['action']??''; $id=(int)($_POST['id']??0);
 if($action==='save') { $name=trim((string)($_POST['record_title']??'')); $details=trim((string)($_POST['details']??'')); $status=(string)($_POST['status']??'Active'); $date=trim((string)($_POST['effective_date']??''));
 if($name==='') throw new RuntimeException('Name/title is required.');
 if(!in_array($status,['Active','Inactive','Pending','Approved','Rejected','Draft','Open','Closed','Completed'],true)) $status='Active';
 if($id>0) { $st=$pdo->prepare('UPDATE hr_module_records SET title=?,details=?,status=?,effective_date=? WHERE id=? AND module_key=?'); $st->execute([$name,$details,$status,$date!==''?$date:null,$id,$key]); }
 else { $st=$pdo->prepare('INSERT INTO hr_module_records(module_key,title,details,status,effective_date,created_by) VALUES(?,?,?,?,?,?)'); $st->execute([$key,$name,$details,$status,$date!==''?$date:null,current_user()['id']??null]); }
 redirect($_SERVER['PHP_SELF'].'?saved=1');
 } elseif($action==='delete') { $st=$pdo->prepare('DELETE FROM hr_module_records WHERE id=? AND module_key=?'); $st->execute([$id,$key]); redirect($_SERVER['PHP_SELF'].'?deleted=1'); }
 } catch(Throwable $e) { $error=$e->getMessage(); } }
$edit=null; if(isset($_GET['edit'])) { $st=$pdo->prepare('SELECT * FROM hr_module_records WHERE id=? AND module_key=?');$st->execute([(int)$_GET['edit'],$key]);$edit=$st->fetch()?:null; }
$st=$pdo->prepare('SELECT * FROM hr_module_records WHERE module_key=? ORDER BY id DESC');$st->execute([$key]);$rows=$st->fetchAll();
require __DIR__ . '/../includes/header.php'; ?>
<div class="page-head"><div><h2><?=e($title)?></h2><p>Akufada Microfinance Institution • Head Office</p></div></div>
<?php if($error):?><div class="card"><p><?=e($error)?></p></div><?php endif;?>
<?php if(isset($_GET['saved'])):?><div class="card"><p>Record saved successfully.</p></div><?php endif;?><?php if(isset($_GET['deleted'])):?><div class="card"><p>Record deleted.</p></div><?php endif;?>
<div class="card"><h3><?= $edit?'Edit':'Add' ?> <?=e($label)?></h3><p><?=e($description)?></p><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=(int)($edit['id']??0)?>"><label><?=e($label)?> name/title</label><input name="record_title" required maxlength="190" value="<?=e($edit['title']??'')?>"><label>Effective date (optional)</label><input type="date" name="effective_date" value="<?=e($edit['effective_date']??'')?>"><label>Details / notes</label><textarea name="details" rows="3"><?=e($edit['details']??'')?></textarea><label>Status</label><select name="status"><?php foreach(['Active','Inactive','Pending','Approved','Rejected','Draft','Open','Closed','Completed'] as $s):?><option <?=($edit['status']??'Active')===$s?'selected':''?>><?=e($s)?></option><?php endforeach;?></select><button type="submit"><?= $edit?'Update':'Save' ?> record</button><?php if($edit):?> <a href="<?=e($_SERVER['PHP_SELF'])?>">Cancel</a><?php endif;?></form></div>
<div class="card"><h3><?=e($title)?> records (<?=count($rows)?>)</h3><div class="table-wrap"><table><thead><tr><th>ID</th><th>Name / title</th><th>Date</th><th>Status</th><th>Details</th><th>Actions</th></tr></thead><tbody><?php if(!$rows):?><tr><td colspan="6">No records yet. Use the form above to add one.</td></tr><?php endif;?><?php foreach($rows as $r):?><tr><td><?=(int)$r['id']?></td><td><?=e($r['title'])?></td><td><?=e($r['effective_date']??'')?></td><td><?=e($r['status'])?></td><td><?=e($r['details']??'')?></td><td><a href="?edit=<?=(int)$r['id']?>">Edit</a> <form method="post" style="display:inline" onsubmit="return confirm('Delete this record?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><button type="submit">Delete</button></form></td></tr><?php endforeach;?></tbody></table></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
