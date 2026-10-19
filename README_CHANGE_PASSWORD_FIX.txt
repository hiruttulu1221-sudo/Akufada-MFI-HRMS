AKUFADA HRMS - Change Password Fix

Install:
1. Back up C:\xampp\htdocs\hrmsnew first.
2. Extract this ZIP. It contains hrmsnew\change_password.php.
3. Copy that file to C:\xampp\htdocs\hrmsnew\change_password.php and replace the old file.
4. Do not import any SQL file and do not change your database.
5. Sign in, then open http://localhost/hrmsnew/change_password.php

This page requires the existing users table columns: id, is_active, password_hash. It verifies the current password, enforces an 8-character minimum, checks confirmation, and stores a password_hash() result.
