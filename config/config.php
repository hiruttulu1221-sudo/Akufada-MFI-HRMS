<?php
declare(strict_types=1);

define('APP_NAME', 'Akufada MFI HR Management System');
define('APP_URL', 'http://localhost/hrmsnew');
define('DB_HOST', 'localhost');
define('DB_NAME', 'akufada_hrms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SESSION_NAME', 'AKUFADA_HRMS');

date_default_timezone_set('Africa/Addis_Ababa');

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
