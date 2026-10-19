<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['Admin']);
$modules = [
 'emails'=>['Emails','Email queue / templates','Email'], 'sms'=>['SMS','SMS message queue / templates','SMS'],
 'report_groups'=>['Report Groups','Organize reports into groups','Report group'], 'reports'=>['Reports','Saved report definitions','Report'],
 'report_fields'=>['Report Fields','Fields available to reports','Report field'], 'groups'=>['Groups','Administrative user groups','Group'],
 'permissions'=>['Permissions','Permission definitions','Permission'], 'modules'=>['Modules','System module registry','Module'],
 'module_activities'=>['Module Activities','Activities available within modules','Activity'], 'web_folders'=>['Web Folders','Application folder registry','Web folder'],
 'sms_confirmations'=>['SMS Confirmations','SMS delivery / confirmation records','SMS confirmation']
];
$key = basename($_SERVER['SCRIPT_NAME'], '.php');
if (!isset($modules[$key])) { http_response_code(404); exit('Unknown module'); }
[$title,$description,$itemLabel] = $modules[$key];
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS admin_module_records (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, module_key VARCHAR(50) NOT NULL, title VARCHAR(190) NOT NULL, details TEXT NULL, status VARCHAR(40) NOT NULL DEFAULT 'Active', created_by INT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_module_key(module_key)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf($_POST['csrf'] ?? null);
 $action=$_POST['action'] ?? '';
 try {
  if ($action==='save') {
   $label=trim((string)($_POST['label']??'')); $details=trim((string)($_POST['details']??'')); $status=trim((string)($_POST['status']??'Active'));
   if ($label==='') throw new RuntimeException('Name/title is required.');
   if (!in_array($status,['Active','Inactive','Pending','Sent','Failed','Confirmed','Draft'],true)) $status='Active';
   $id=(int)($_POST['id']??0);
   if ($id>0) $st=$pdo->prepare('UPDATE admin_module_records SET title=?,details=?,status=? WHERE id=? AND module_key=?');
   else $st=$pdo->prepare('INSERT INTO admin_module_records(title,details,status,created_by,module_key) VALUES(?,?,?,?,?)');
   if ($id>0) $st->execute([$label,$details,$status,$id,$key]); else $st->execute([$label,$details,$status,current_user()['id']??null,$key]);
   redirect(APP_URL.'/admin/'.$key.'.php?saved=1');
  } elseif ($action==='delete') {
   $pdo->prepare('DELETE FROM admin_module_records WHERE id=? AND module_key=?')->execute([(int)($_POST['id']??0),$key]);
   redirect(APP_URL.'/admin/'.$key.'.php?deleted=1');
  }
 } catch (Throwable $e) { $error=$e->getMessage(); }
}
$edit=null;
if (isset($_GET['edit'])) { $st=$pdo->prepare('SELECT * FROM admin_module_records WHERE id=? AND module_key=?'); $st->execute([(int)$_GET['edit'],$key]); $edit=$st->fetch() ?: null; }
$st=$pdo->prepare('SELECT * FROM admin_module_records WHERE module_key=? ORDER BY id DESC'); $st->execute([$key]); $rows=$st->fetchAll();
require __DIR__.'/../includes/header.php';
?>
<div class="page-head"><div><h2><?=e($title)?></h2><p>Akufada Microfinance Institution • Head Office</p></div></div>
<?php if($error!==''): ?><div class="card"><p><?=e($error)?></p></div><?php endif; ?>
<?php if(isset($_GET['saved'])): ?><div class="card"><p>Record saved successfully.</p></div><?php endif; ?><?php if(isset($_GET['deleted'])): ?><div class="card"><p>Record deleted.</p></div><?php endif; ?>
<div class="card"><h3><?= $edit?'Edit':'Add' ?> <?=e($itemLabel)?></h3><p><?=e($description)?>. This page stores administrative records; email and SMS delivery requires a configured provider.</p>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=e((string)($edit['id']??0))?>">
<label>Name / title</label><input name="label" required maxlength="190" value="<?=e($edit['title']??'')?>" placeholder="Enter name or title">
<label>Details / configuration</label><textarea name="details" rows="3" placeholder="Description, recipient, report notes, or configuration"><?=e($edit['details']??'')?></textarea>
<label>Status</label><select name="status"><?php foreach(['Active','Inactive','Pending','Sent','Failed','Confirmed','Draft'] as $s): ?><option value="<?=e($s)?>" <?=($edit['status']??'Active')===$s?'selected':''?>><?=e($s)?></option><?php endforeach; ?></select>
<button type="submit"><?= $edit?'Update':'Save' ?> record</button> <?php if($edit): ?><a href="<?=e(APP_URL.'/admin/'.$key.'.php')?>">Cancel</a><?php endif; ?></form></div>
<div class="card"><h3>Saved records (<?=count($rows)?>)</h3><div class="table-wrap"><table><thead><tr><th>ID</th><th>Name / title</th><th>Details</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody><?php if(!$rows): ?><tr><td colspan="6">No records yet. Use the form above to add one.</td></tr><?php endif; ?><?php foreach($rows as $r): ?><tr><td><?=e((string)$r['id'])?></td><td><?=e($r['title'])?></td><td><?=e($r['details']??'')?></td><td><?=e($r['status'])?></td><td><?=e($r['created_at'])?></td><td><a href="?edit=<?=(int)$r['id']?>">Edit</a> <form method="post" style="display:inline" onsubmit="return confirm('Delete this record?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><button type="submit">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>