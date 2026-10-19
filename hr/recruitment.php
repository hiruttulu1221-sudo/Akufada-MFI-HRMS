<?php
require_once __DIR__.'/../includes/auth.php'; require_once __DIR__.'/../includes/helpers.php'; require_role(['Admin','HR Admin']);
$pdo=db();$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf($_POST['csrf']??null);$st=$pdo->prepare("INSERT INTO vacancies(title,department,description,closing_date,status) VALUES(?,?,?,?, 'Open')");$st->execute([trim($_POST['title']),trim($_POST['department']),trim($_POST['description']),$_POST['closing_date']]);$message='Vacancy created.';}
$title='Recruitment Requests';require __DIR__.'/../includes/header.php';
?>
<div class="page-head"><div><h2>Recruitment Requests</h2><p>Internal vacancies and applicant pipeline.</p></div></div>
<?php if($message):?><div class="alert success"><?=e($message)?></div><?php endif;?>
<div class="grid two"><div class="card"><h3>New Vacancy</h3><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label>Position Title<input name="title" required></label><label>Department<input name="department" required></label><label>Closing Date<input type="date" name="closing_date"></label><label>Description<textarea name="description" rows="5"></textarea></label><button class="btn primary">Create Vacancy</button></form></div>
<div class="card"><h3>Vacancies</h3><div class="table-wrap"><table><tr><th>Position</th><th>Department</th><th>Closing</th><th>Status</th></tr><?php foreach($pdo->query("SELECT * FROM vacancies ORDER BY id DESC") as $r):?><tr><td><?=e($r['title'])?></td><td><?=e($r['department'])?></td><td><?=e($r['closing_date'])?></td><td><span class="badge green"><?=e($r['status'])?></span></td></tr><?php endforeach;?></table></div></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
