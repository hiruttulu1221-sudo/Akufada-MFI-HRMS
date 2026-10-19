<?php
require_once __DIR__.'/../includes/auth.php';require_once __DIR__.'/../includes/helpers.php';require_login();$pdo=db();$u=current_user();$employee=null;
if(!empty($u['employee_id'])){$st=$pdo->prepare("SELECT * FROM employees WHERE id=?");$st->execute([$u['employee_id']]);$employee=$st->fetch();}
$title='Profile';require __DIR__.'/../includes/header.php';?>
<div class="page-head"><div><h2>My Profile</h2><p>Employee profile information.</p></div></div>
<div class="card"><?php if($employee):?><div class="profile-grid"><?php foreach(['employee_code'=>'Employee ID','full_name'=>'Full Name','department'=>'Department','position'=>'Position','branch'=>'Branch','phone'=>'Phone','email'=>'Email','hire_date'=>'Hire Date'] as $k=>$label):?><div><span class="muted"><?=e($label)?></span><b><?=e($employee[$k])?></b></div><?php endforeach;?></div><?php else:?><p>No employee profile is linked to this account.</p><?php endif;?></div>
<?php require __DIR__.'/../includes/footer.php';?>
