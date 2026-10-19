```php
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['Admin', 'HR Admin', 'Payroll']);

$pdo = db();
$page_title = 'Payroll Management';

require __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>Payroll Management</h2>
        <p>Monthly payroll preparation and employee compensation.</p>
    </div>
</div>

<div class="grid stats">
    <?php
    $rows = [
        [
            'Employees',
            (int) $pdo->query(
                "SELECT COUNT(*) FROM employees WHERE status = 'Active'"
            )->fetchColumn()
        ],
        [
            'Benefit Types',
            (int) $pdo->query(
                "SELECT COUNT(*) FROM benefit_types"
            )->fetchColumn()
        ],
        [
            'Deduction Types',
            (int) $pdo->query(
                "SELECT COUNT(*) FROM deduction_types"
            )->fetchColumn()
        ],
        [
            'Loan Types',
            (int) $pdo->query(
                "SELECT COUNT(*) FROM loan_types"
            )->fetchColumn()
        ]
    ];

    foreach ($rows as $r):
    ?>
        <div class="card stat">
            <div>
                <span class="muted"><?= e($r[0]) ?></span>
                <strong><?= e((string) $r[1]) ?></strong>
            </div>
            <div class="symbol">▣</div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card gap">
    <h3>Payroll Menu</h3>

    <div class="quick">

        <a class="quick-card" href="loans.php">
            <b>Loan Types</b>
            <small>Manage employee loans</small>
        </a>

        <a class="quick-card" href="deductions.php">
            <b>Deduction Types</b>
            <small>Configure deductions</small>
        </a>

        <a class="quick-card" href="general.php">
            <b>General Payroll</b>
            <small>Payroll workspace</small>
        </a>

        <a class="quick-card" href="monthly.php">
            <b>Monthly Payroll</b>
            <small>Prepare monthly payroll</small>
        </a>

        <a class="quick-card" href="pensions.php">
            <b>PF/Pensions</b>
            <small>Review salary and pension readiness</small>
        </a>

    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
```