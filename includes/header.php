<?php
require_once __DIR__ . '/auth.php'; require_once __DIR__ . '/helpers.php'; require_login();
$user = current_user(); $role = $user['role'];

$menu = [
 
'ESS' => [
    ['MAIN','⌂','Dashboard',APP_URL.'/hr/profile.php'],
    ['MY HR','▣','My Profile',APP_URL.'/hr/profile.php'],
    ['MY HR','▣','My Leave',APP_URL.'/hr/leave.php'],
    ['MY HR','▣','Complaints',APP_URL.'/hr/complaints.php'],

    ['MY HR','▣','Vacancies (0)',APP_URL.'/hr/vacancies.php'],
    ['MY HR','▣','Fixed Asset (Employee)',APP_URL.'/hr/fixed_asset.php'],
    ['MY HR','⚙','Change Password',APP_URL.'/change_password.php'],
],

 
'Payroll' => [
    ['MAIN','⌂','Payroll Dashboard',APP_URL.'/payroll/index.php'],

    ['PAYROLL','▣','PF/Pensions',APP_URL.'/payroll/pensions.php'],
    ['PAYROLL','▣','Tax Rules',APP_URL.'/payroll/tax_rules.php'],
    ['PAYROLL','▣','Benefit Types',APP_URL.'/payroll/benefits.php'],
    ['PAYROLL','▣','General Payroll',APP_URL.'/payroll/general.php'],
    ['PAYROLL','▣','Individual Payroll',APP_URL.'/payroll/individual.php'],
    ['PAYROLL','▣','Monthly Payroll',APP_URL.'/payroll/monthly.php'],
    ['PAYROLL','✓','Approve Monthly Payroll',APP_URL.'/payroll/approve_monthly.php'],

    ['PAYROLL','▣','Loan Types',APP_URL.'/payroll/loans.php'],
    ['PAYROLL','▣','Deduction Types',APP_URL.'/payroll/deductions.php'],
    ['MY HR','▣','My Profile',APP_URL.'/hr/profile.php'],
    ['REPORTS','▤','Payroll/HR Reports',APP_URL.'/reports/index.php'],
],

 
'Supervisor' => [
    ['MAIN','⌂','Dashboard',APP_URL.'/dashboard.php'],

    ['SUPERVISOR','✓','Leave Agree',APP_URL.'/hr/leave_agree.php'],
    ['SUPERVISOR','▣','Employee Request',APP_URL.'/hr/employee_requests.php'],
    ['SUPERVISOR','✓','Approve Item Request',APP_URL.'/hr/approve_item_request.php'],
    ['SUPERVISOR','▣','Subordinates',APP_URL.'/hr/subordinates.php'],

    ['SUPERVISOR','✓','Leave Approvals',APP_URL.'/hr/leave_approve.php'],
    ['SUPERVISOR','▣','Performance',APP_URL.'/hr/performance.php'],
    ['SUPERVISOR','▣','Positions',APP_URL.'/hr/positions.php'],
    ['MY HR','▣','My Profile',APP_URL.'/hr/profile.php'],
    ['MY HR','▣','My Leave',APP_URL.'/hr/leave.php'],
    ['REPORTS','▤','HR Reports',APP_URL.'/reports/index.php'],
],

 'HR Admin' => [
   ['MAIN','⌂','Dashboard',APP_URL.'/dashboard.php'],
   ['HR ADMIN','▣','Employees',APP_URL.'/hr/employees.php'],
   ['HR ADMIN','▣','Recruitment',APP_URL.'/hr/recruitment.php'],
   ['HR ADMIN','▣','Positions',APP_URL.'/hr/positions.php'],
   ['HR ADMIN','▣','Grades',APP_URL.'/hr/grades.php'],
   ['HR ADMIN','▣','Budget Year',APP_URL.'/hr/budget_year.php'],
   ['HR ADMIN','▣','Internal Vacancies',APP_URL.'/hr/vacancies.php'],
   ['HR ADMIN','▣','Terminated Employees',APP_URL.'/hr/terminated.php'],
   ['HR ADMIN','▣','Delegations',APP_URL.'/hr/delegations.php'],
   ['HR ADMIN','✓','Leave Approvals',APP_URL.'/hr/leave_approve.php'],
   ['HR ADMIN','▣','Performance',APP_URL.'/hr/performance.php'],
   ['HR ADMIN','▣','Complaints',APP_URL.'/hr/complaints.php'],
   ['PAYROLL','▣','Payroll Management',APP_URL.'/payroll/index.php'],
   ['REPORTS','▤','HR Reports',APP_URL.'/reports/index.php'],
['HR ADMIN','▣','Transferred Employees',APP_URL.'/hr/transferred.php'],
['HR ADMIN','▣','Leave Calculator Service',APP_URL.'/hr/leave_calculator.php'],

 ],
 
 'Admin' => [
   ['MAIN','⌂','Dashboard',APP_URL.'/dashboard.php'],
   ['SYSTEM ADMIN','▣','Users',APP_URL.'/admin/users.php'],
   ['SYSTEM ADMIN','✉','Emails',APP_URL.'/admin/emails.php'],
   ['SYSTEM ADMIN','▣','SMS',APP_URL.'/admin/sms.php'],
   ['SYSTEM ADMIN','▤','Report Group',APP_URL.'/admin/report_groups.php'],
   ['SYSTEM ADMIN','▤','Reports',APP_URL.'/admin/reports.php'],
   ['SYSTEM ADMIN','▤','Report Fields',APP_URL.'/admin/report_fields.php'],
   ['SYSTEM ADMIN','▣','Groups',APP_URL.'/admin/groups.php'],
   ['SYSTEM ADMIN','⚙','Permissions',APP_URL.'/admin/permissions.php'],
   ['SYSTEM ADMIN','▣','Modules',APP_URL.'/admin/modules.php'],
   ['SYSTEM ADMIN','▣','Module Activities',APP_URL.'/admin/module_activities.php'],
   ['SYSTEM ADMIN','▣','Web Folders',APP_URL.'/admin/web_folders.php'],
   ['SYSTEM ADMIN','▤','Logs',APP_URL.'/admin/logs.php'],
   ['SYSTEM ADMIN','▤','Report Generator',APP_URL.'/admin/report_generator.php'],
   ['SYSTEM ADMIN','▣','SMS Confirmations',APP_URL.'/admin/sms_confirmations.php'],
   ['HR REPORTS','▤','HR Reports',APP_URL.'/reports/index.php'],
   ['HR REPORTS','▣','Transferred Employees',APP_URL.'/hr/transferred.php'],
   ['HR REPORTS','▣','Terminated Employees',APP_URL.'/hr/terminated.php'],
   ['HR REPORTS','▣','Recruited Employees',APP_URL.'/hr/recruited.php'],
 ],
];
$items = $menu[$role] ?? $menu['ESS'];
$lastSection = '';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/assets/css/app.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-icon">A</div>
            <div><b>AKUFADA</b><small>MFI • HRMS</small></div>
        </div>

        <div class="user-panel">
            <div class="avatar"><?= e(strtoupper(substr($user['full_name'], 0, 2))) ?></div>
            <div>
                <b><?= e($user['full_name']) ?></b>
                <small><?= e($role) ?></small>
            </div>
        </div>

        <nav>
            <?php foreach ($items as $item): ?>
                <?php if ($lastSection !== $item[0]): ?>
                    <?php $lastSection = $item[0]; ?>
                    <div class="nav-label"><?= e($lastSection) ?></div>
                <?php endif; ?>

                <a href="<?= e($item[3]) ?>">
                    <?= $item[1] ?> <span><?= e($item[2]) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            Akufada HRMS
            <small>Head Office HR</small>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <button class="icon-btn" id="menuBtn">☰</button>
                <b><?= e($title ?? 'Dashboard') ?></b>
            </div>
            <div class="top-actions">
                <span class="badge"><?= e($role) ?></span>
                <a class="logout" href="<?= e(APP_URL) ?>/logout.php">Logout</a>
            </div>
        </header>

        <section class="content">



