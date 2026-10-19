<?php
require_once __DIR__.'/../includes/auth.php'; require_role(['Admin','HR Admin','Supervisor','Payroll']);
$report=$_GET['report']??'Report';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="'.preg_replace('/[^A-Za-z0-9_-]/','_', $report).'.csv"');
$out=fopen('php://output','w'); fputcsv($out,['Akufada MFI','Report',$report]); fputcsv($out,['Generated',date('Y-m-d H:i:s')]); fputcsv($out,['Note','Prototype export. Connect report-specific SQL for production.']); fclose($out);
