# Akufada MFI HRMS — PHP/MySQL Full-Stack Package

This package is a runnable PHP + MySQL HR Management System based on the HR/admin menus and screenshots supplied in the conversation.

## Stack
- PHP 8+
- MySQL 8+ / MariaDB
- HTML5
- CSS3
- Vanilla JavaScript
- PDO prepared statements
- PHP sessions and password hashing

## Modules included
- Login/logout
- Dashboard
- ESS: Leave, Profile, Item Request, Complaints
- HR Admin: Employees, Positions, Recruitment, Leave Approve, Performance
- Payroll: Payroll dashboard, Loan Types, Deduction Types
- Reports with CSV export
- Admin: Users and Audit Logs
- Database schema and seed data

## Installation with XAMPP
1. Install XAMPP.
2. Copy the `Akufada_MFI_HRMS_Full_Stack` folder into `C:/xampp/htdocs/`.
3. Start Apache and MySQL.
4. Open `http://localhost/phpmyadmin`.
5. Import `database/akufada_hrms.sql`, OR open `http://localhost/Akufada_MFI_HRMS/setup/install.php`.
6. If the demo admin password does not work, open `http://localhost/Akufada_MFI_HRMS/setup/create_admin.php` once.
7. Login:
   - Username: admin
   - Password: admin123
8. Delete the `setup` directory after installation.

## Database configuration
Edit `config/config.php`:
DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL.

## Important production security
This is a development/reference system, not a production banking-grade application. Before production deployment:
- Use HTTPS.
- Change all demo passwords.
- Use environment variables/secrets rather than committed credentials.
- Add MFA/SSO.
- Enforce granular role/permission checks for every endpoint.
- Add server-side validation and transaction boundaries.
- Add full audit logging for create/update/delete/approval/payroll operations.
- Encrypt backups and protect database credentials.
- Configure secure session cookies, CSRF protection on every state-changing endpoint, rate limiting, account lockout and password policy.
- Implement payroll/tax/pension rules according to current Ethiopian legal requirements and your institution's policies.
- Conduct penetration testing and code review.

## Architecture
config/       database and application configuration
includes/     authentication, helpers, shared layout
hr/           HR modules
payroll/      payroll modules
reports/      reports and exports
admin/        administration
api/          reserved for JSON API expansion
database/     SQL schema and seed data
assets/       CSS/JavaScript/images
setup/        installation helpers

## Note
The menu structure is intentionally aligned with the screenshots supplied by the requester. Some inventory/procurement report names are included in the Reports menu, but their business logic is not implemented as a complete ERP/procurement system.

## Role-based access
The HRMS now has five separate role areas with role-specific menus and logout:
- Admin — full HRMS administration, users, logs, HR, payroll, reports
- HR Admin — HR management, payroll, reports
- Supervisor — approvals, performance, positions, own ESS functions, reports
- Payroll — payroll, loans, deductions, reports
- ESS — employee self-service: profile, leave, complaints

Demo accounts after running setup/upgrade_roles.php:
- admin / admin123
- hradmin / hradmin123
- supervisor / supervisor123
- payroll / payroll123
- ess / ess123

Inventory/procurement/stock/SIRV/GRN/fixed-asset/tender functions are intentionally excluded.
