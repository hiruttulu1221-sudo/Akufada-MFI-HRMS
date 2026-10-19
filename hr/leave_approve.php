<?php
require_once __DIR__.'/../includes/auth.php'; require_once __DIR__.'/../includes/helpers.php'; require_role(['Admin','HR Admin','Supervisor']);
$pdo=db();
if(isset($_GET['approve'])){$id=(int)$_GET['approve'];$st=$pdo->prepare("UPDATE leave_requests SET status='Approved',approved_by=?,approved_at=NOW() WHERE id=?");$st->execute([current_user()['id'],$id]);}
if(isset($_GET['reject'])){$id=(int)$_GET['reject'];$st=$pdo->prepare("UPDATE leave_requests SET status='Rejected',approved_by=?,approved_at=NOW() WHERE id=?");$st->execute([current_user()['id'],$id]);}
$title='Leave Approve'; require __DIR__.'/../includes/header.php';
?>
<div class="page-head"><div><h2>Leave Approvals</h2><p>Review and approve employee leave requests.</p></div></div>
<div class="card"><div class="table-wrap"><table><tr><th>Employee</th><th>Type</th><th>Start</th><th>End</th><th>Days</th><th>Status</th><th>Action</th></tr>
<?php foreach($pdo->query("SELECT l.*,e.full_name FROM leave_requests l JOIN employees e ON e.id=l.employee_id ORDER BY l.id DESC") as $r):?>
<tr><td><?=e($r['full_name'])?></td><td><?=e($r['leave_type'])?></td><td><?=e($r['start_date'])?></td><td><?=e($r['end_date'])?></td><td><?=e((string)$r['days'])?></td><td><?=e($r['status'])?></td><td><?php if($r['status']==='Pending'):?><a class="btn small primary" href="?approve=<?=$r['id']?>">Approve</a> <a class="btn small danger" href="?reject=<?=$r['id']?>">Reject</a><?php else:?>—<?php endif;?></td></tr>
<?php endforeach;?></table></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
