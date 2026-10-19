```php
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['Payroll', 'Admin', 'HR Admin']);

$pdo = db();
$page_title = 'PF/Pensions';

$selectedMonth = $_GET['month'] ?? date('Y-m');

if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
    $selectedMonth = date('Y-m');
}

$monthDate = $selectedMonth . '-01';
$message = '';
$error = '';

/*
 * Standard rates used for the initial draft calculation.
 * Confirm applicable Ethiopian pension rules before live payroll.
 */
$employeeRate = 7.00;
$employerRate = 11.00;

if (empty($_SESSION['pension_csrf'])) {
    $_SESSION['pension_csrf'] = bin2hex(random_bytes(32));
}

/* Generate or refresh draft pension records */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (
            !isset($_POST['csrf_token']) ||
            !hash_equals($_SESSION['pension_csrf'], $_POST['csrf_token'])
        ) {
            throw new RuntimeException(
                'Invalid form submission. Refresh the page and try again.'
            );
        }

        $postMonth = $_POST['month'] ?? '';

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $postMonth)) {
            throw new RuntimeException('Select a valid payroll month.');
        }

        $monthDate = $postMonth . '-01';
        $selectedMonth = $postMonth;

        $pdo->beginTransaction();

        /*
         * Use only active employees who already have a payroll record.
         * Do not create salary records or change payroll status here.
         */
        $stmt = $pdo->prepare(
            "SELECT
                pr.employee_id,
                pr.basic_salary,
                pr.status AS payroll_status
             FROM payroll_records pr
             INNER JOIN employees e ON e.id = pr.employee_id
             WHERE pr.payroll_month = ?
               AND e.status = 'Active'
             FOR UPDATE"
        );
        $stmt->execute([$monthDate]);
        $salaryRows = $stmt->fetchAll();

        if (!$salaryRows) {
            throw new RuntimeException(
                'No payroll records were found for this month. Save salary drafts first.'
            );
        }

        $saved = 0;
        $skipped = 0;

        foreach ($salaryRows as $salary) {
            /*
             * Do not create pension records for approved or paid payroll
             * through this draft-generation screen.
             */
            if ($salary['payroll_status'] !== 'Draft') {
                $skipped++;
                continue;
            }

            $employeeId = (int) $salary['employee_id'];
            $pensionableSalary = (float) $salary['basic_salary'];

            $employeeAmount = round(
                $pensionableSalary * $employeeRate / 100,
                2
            );

            $employerAmount = round(
                $pensionableSalary * $employerRate / 100,
                2
            );

            /*
             * The unique employee/month key prevents duplicates.
             * Existing Draft entries can be recalculated.
             * Approved or Paid pension records remain unchanged.
             */
            $check = $pdo->prepare(
                "SELECT id, status
                 FROM pension_records
                 WHERE employee_id = ? AND payroll_month = ?
                 FOR UPDATE"
            );
            $check->execute([$employeeId, $monthDate]);
            $existing = $check->fetch();

            if ($existing) {
                if ($existing['status'] !== 'Draft') {
                    $skipped++;
                    continue;
                }

                $update = $pdo->prepare(
                    "UPDATE pension_records
                     SET pensionable_salary = ?,
                         employee_rate = ?,
                         employer_rate = ?,
                         employee_amount = ?,
                         employer_amount = ?
                     WHERE id = ? AND status = 'Draft'"
                );

                $update->execute([
                    $pensionableSalary,
                    $employeeRate,
                    $employerRate,
                    $employeeAmount,
                    $employerAmount,
                    $existing['id']
                ]);
            } else {
                $insert = $pdo->prepare(
                    "INSERT INTO pension_records
                     (
                         employee_id,
                         payroll_month,
                         pensionable_salary,
                         employee_rate,
                         employer_rate,
                         employee_amount,
                         employer_amount,
                         status
                     )
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'Draft')"
                );

                $insert->execute([
                    $employeeId,
                    $monthDate,
                    $pensionableSalary,
                    $employeeRate,
                    $employerRate,
                    $employeeAmount,
                    $employerAmount
                ]);
            }

            $saved++;
        }

        $pdo->commit();

        $message = $saved . ' draft pension record(s) created or updated.';

        if ($skipped > 0) {
            $message .= ' ' . $skipped .
                ' record(s) skipped because payroll or pension was not in Draft status.';
        }

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($e instanceof PDOException) {
            error_log('Pension generation error: ' . $e->getMessage());
            $error = 'Unable to generate pension records. Check the database structure.';
        } else {
            $error = $e->getMessage();
        }
    }
}

/* Load employee salary and pension status for the selected month */
$stmt = $pdo->prepare(
    "SELECT
        e.employee_code,
        e.full_name,
        e.department,
        pr.basic_salary,
        pr.status AS payroll_status,
        pen.pensionable_salary,
        pen.employee_rate,
        pen.employer_rate,
        pen.employee_amount,
        pen.employer_amount,
        pen.status AS pension_status
     FROM employees e
     LEFT JOIN payroll_records pr
        ON pr.employee_id = e.id
       AND pr.payroll_month = ?
     LEFT JOIN pension_records pen
        ON pen.employee_id = e.id
       AND pen.payroll_month = ?
     WHERE e.status = 'Active'
     ORDER BY e.full_name"
);
$stmt->execute([$monthDate, $monthDate]);
$employees = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>PF/Pensions</h2>
        <p>Review salaries and prepare draft pension contributions.</p>
    </div>
</div>

<div class="card">

    <?php if ($message !== ''): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="get" style="margin-bottom:20px">
        <label for="month">Payroll month</label>
        <input
            type="month"
            id="month"
            name="month"
            value="<?= e($selectedMonth) ?>"
            required
        >
        <button type="submit" class="btn primary">View Month</button>
    </form>

    <p>
        Draft calculation rates: employee
        <?= number_format($employeeRate, 2) ?>% and employer
        <?= number_format($employerRate, 2) ?>%.
        Confirm the applicable rates before using these figures for real payroll.
    </p>

    <form method="post"
          onsubmit="return confirm('Generate or update draft pensions for this month? Existing approved or paid pension records will be skipped.');">

        <input type="hidden" name="csrf_token"
               value="<?= e($_SESSION['pension_csrf']) ?>">

        <input type="hidden" name="month"
               value="<?= e($selectedMonth) ?>">

        <button type="submit" class="btn primary">
            Generate Draft Pensions
        </button>
    </form>

    <div style="overflow-x:auto; margin-top:20px">
        <table>
            <thead>
                <tr>
                    <th>Employee Code</th>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th>Basic Salary</th>
                    <th>Employee Pension</th>
                    <th>Employer Pension</th>
                    <th>Payroll Status</th>
                    <th>Pension Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$employees): ?>
                    <tr>
                        <td colspan="8">No active employees found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?= e($employee['employee_code']) ?></td>
                            <td><?= e($employee['full_name']) ?></td>
                            <td><?= e($employee['department']) ?></td>

                            <td>
                                <?php if ($employee['basic_salary'] !== null): ?>
                                    <?= number_format(
                                        (float) $employee['basic_salary'], 2
                                    ) ?> ETB
                                <?php else: ?>
                                    No salary record
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($employee['pensionable_salary'] !== null): ?>
                                    <?= number_format(
                                        (float) $employee['employee_amount'], 2
                                    ) ?> ETB
                                <?php elseif ($employee['basic_salary'] !== null): ?>
                                    <?= number_format(
                                        (float) $employee['basic_salary']
                                        * $employeeRate / 100, 2
                                    ) ?> ETB (preview)
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($employee['pensionable_salary'] !== null): ?>
                                    <?= number_format(
                                        (float) $employee['employer_amount'], 2
                                    ) ?> ETB
                                <?php elseif ($employee['basic_salary'] !== null): ?>
                                    <?= number_format(
                                        (float) $employee['basic_salary']
                                        * $employerRate / 100, 2
                                    ) ?> ETB (preview)
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= e($employee['payroll_status'] ?? 'No payroll') ?>
                            </td>

                            <td>
                                <?= e($employee['pension_status'] ?? 'Not generated') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p style="margin-top:15px">
        This page creates or updates draft pension records only.
        It does not approve or pay pensions, and it does not modify salaries.
    </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```