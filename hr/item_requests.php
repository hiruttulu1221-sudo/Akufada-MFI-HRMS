<?php
require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/helpers.php';require_login();$pdo=db();$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf($_POST['csrf']??null);$st=$pdo->prepare("INSERT INTO item_requests(employee_id,item_name,quantity,reason,status) VALUES(?,?,?,?, 'Pending')");$st->execute([current_user()['employee_id']??1,trim($_POST['item_name']),(int)$_POST['quantity'],trim($_POST['reason'])]);$message='Item request submitted.';}
$title='Item Request';require __DIR__.'/../includes/header.php';?>
<div class="page-head"><div><h2>Item Request</h2><p>Request office supplies and fixed assets.</p></div></div>
<?php if($message):?><div class="alert success"><?=e($message)?></div><?php endif;?>
<div class="grid two"><div class="card"><h3>New Request</h3><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Item Name<input name="item_name" required></label><label>Quantity<input type="number" name="quantity" value="1" min="1"></label><label>Reason<textarea name="reason"></textarea></label><button class="btn primary">Submit Request</button></form></div>
<div class="card"><h3>Requests</h3><table><tr><th>Item</th><th>Qty</th><th>Status</th></tr><?php foreach($pdo->query("SELECT * FROM item_requests ORDER BY id DESC LIMIT 20") as $r):?><tr><td><?=e($r['item_name'])?></td><td><?=e((string)$r['quantity'])?></td><td><?=e($r['status'])?></td></tr><?php endforeach;?></table></div></div>
<?php require __DIR__.'/../includes/footer.php';?>
