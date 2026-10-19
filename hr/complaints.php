<?php
require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/helpers.php';require_login();$pdo=db();$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf($_POST['csrf']??null);$st=$pdo->prepare("INSERT INTO complaints(employee_id,subject,details,status) VALUES(?,?,?,'Open')");$st->execute([current_user()['employee_id']??1,trim($_POST['subject']),trim($_POST['details'])]);$message='Complaint submitted.';}
$title='Complaints';require __DIR__.'/../includes/header.php';?>
<div class="page-head"><div><h2>Complaints</h2><p>Employee complaints and follow-up.</p></div></div>
<?php if($message):?><div class="alert success"><?=e($message)?></div><?php endif;?>
<div class="grid two"><div class="card"><h3>New Complaint</h3><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Subject<input name="subject" required></label><label>Details<textarea name="details" rows="6" required></textarea></label><button class="btn primary">Submit</button></form></div>
<div class="card"><h3>Recent Complaints</h3><table><tr><th>Subject</th><th>Date</th><th>Status</th></tr><?php foreach($pdo->query("SELECT * FROM complaints ORDER BY id DESC LIMIT 20") as $r):?><tr><td><?=e($r['subject'])?></td><td><?=e($r['created_at'])?></td><td><?=e($r['status'])?></td></tr><?php endforeach;?></table></div></div>
<?php require __DIR__.'/../includes/footer.php';?>
