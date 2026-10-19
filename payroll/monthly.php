```php
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['Payroll', 'Admin', 'HR Admin']);

$page_title = 'Monthly Payroll';
$pdo = db();

$selectedMonth = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
    $selectedMonth = date('Y-m');
}

$monthDate = $selectedMonth . '-01';
$message = '';
$error = '';

if (empty($_SESSION['payroll_csrf'])) {
    $_SESSION['payroll_csrf'] = bin2hex(random_bytes(32));
}

/* Save a payroll record */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (
            !isset($_POST['csrf_token']) ||
            !hash_equals($_SESSION['payroll_csrf'], $_POST['csrf_token'])
        ) {
            throw new RuntimeException('Invalid form submission. Refresh the page and try again.');
        }

        $employeeId = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        $postMonth = $_POST['month'] ?? '';
        $basicSalary = filter_input(INPUT_POST, 'basic_salary', FILTER_VALIDATE_FLOAT);
        $benefits = filter_input(INPUT_POST, 'benefits', FILTER_VALIDATE_FLOAT);
        $deductions = filter_input(INPUT_POST, 'deductions', FILTER_VALIDATE_FLOAT);

        if (!$employeeId || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $postMonth)) {
            throw new RuntimeException('Select a valid employee and payroll month.');
        }

        if (
            $basicSalary === false || $basicSalary < 0 ||
            $benefits === false || $benefits < 0 ||
            $deductions === false || $deductions < 0
        ) {
            throw new RuntimeException('Salary, benefits, and deductions must be valid non-negative numbers.');
        }

        $postMonthDate = $postMonth . '-01';

        $check = $pdo->prepare(
            "SELECT id FROM employees WHERE id = ? AND status = 'Active'"
        );
        $check->execute([$employeeId]);

        if (!$check->fetch()) {
            throw new RuntimeException('The selected employee was not found or is not active.');
        }

        $existing = $pdo->prepare(
            'SELECT id, status FROM payroll_records
             WHERE employee_id = ? AND payroll_month = ?'
        );
        $existing->execute([$employeeId, $postMonthDate]);
        $record = $existing->fetch();

        $netSalary = round($basicSalary + $benefits - $deductions, 2);

        if ($netSalary < 0) {
            throw new RuntimeException('Deductions cannot exceed salary plus benefits.');
        }

        if ($record && $record['status'] !== 'Draft') {
            throw new RuntimeException(
                'This payroll record is already ' . $record['status'] .
                '. Approved or paid records cannot be changed here.'
            );
        }

        if ($record) {
            $stmt = $pdo->prepare(
                "UPDATE payroll_records
                 SET basic_salary = ?, benefits = ?, deductions = ?, net_salary = ?
                 WHERE id = ? AND status = 'Draft'"
            );
            $stmt->execute([
                $basicSalary, $benefits, $deductions, $netSalary, $record['id']
            ]);
            $message = 'Draft payroll record updated successfully.';
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO payroll_records
                 (employee_id, payroll_month, basic_salary, benefits,
                  deductions, net_salary, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'Draft')"
            );
            $stmt->execute([
                $employeeId, $postMonthDate, $basicSalary,
                $benefits, $deductions, $netSalary
            ]);
            $message = 'Draft payroll record saved successfully.';
        }

        $selectedMonth = $postMonth;
        $monthDate = $postMonthDate;

    } catch (Throwable $e) {
        if ($e instanceof PDOException) {
            error_log('Monthly payroll save error: ' . $e->getMessage());
            $error = 'Unable to save payroll. Check the database and try again.';
        } else {
            $error = $e->getMessage();
        }
    }
}

/* Load active employees */
$employees = $pdo->query(
    "SELECT id, employee_code, full_name, department
     FROM employees
     WHERE status = 'Active'
     ORDER BY full_name"
)->fetchAll();

/* Load payroll records for the selected month */
$stmt = $pdo->prepare(
    "SELECT pr.id, e.employee_code, e.full_name,
            pr.basic_salary, pr.benefits, pr.deductions,
            pr.net_salary, pr.status
     FROM payroll_records pr
     JOIN employees e ON e.id = pr.employee_id
     WHERE pr.payroll_month = ?
     ORDER BY e.full_name"
);
$stmt->execute([$monthDate]);
$payrollRows = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2>Monthly Payroll</h2>
    <p>Enter and review salary records for active employees.</p>

    <?php if ($message !== ''): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="get" style="margin-bottom:20px">
        <label for="month_filter">Payroll month</label>
        <input type="month" id="month_filter" name="month"
               value="<?= e($selectedMonth) ?>" required>
        <button type="submit" class="btn primary">View Month</button>
    </form>

    <h3>Enter Salary</h3>

    <?php if (!$employees): ?>
        <p>No active employees were found.</p>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="csrf_token"
                   value="<?= e($_SESSION['payroll_csrf']) ?>">

            <label for="employee_id">Employee</label>
            <select id="employee_id" name="employee_id" required>
                <option value="">Select employee</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= (int)$employee['id'] ?>">
                        <?= e($employee['employee_code'] . ' - ' .
                            $employee['full_name'] . ' (' .
                            $employee['department'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="pay_month">Payroll month</label>
            <input type="month" id="pay_month" name="month"
                   value="<?= e($selectedMonth) ?>" required>

            <label for="basic_salary">Basic salary (ETB)</label>
            <input type="number" id="basic_salary" name="basic_salary"
                   min="0" step="0.01" required>

            <label for="benefits">Total benefits (ETB)</label>
            <input type="number" id="benefits" name="benefits"
                   min="0" step="0.01" value="0" required>

            <label for="deductions">Total deductions (ETB)</label>
            <input type="number" id="deductions" name="deductions"
                   min="0" step="0.01" value="0" required>

            <p>Net salary = Basic salary + Benefits − Deductions.</p>

            <button type="submit" class="btn primary">Save Draft Payroll</button>
        </form>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:20px">
    <h3>Payroll Records — <?= e($selectedMonth) ?></h3>

    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>Employee Code</th>
                    <th>Employee Name</th>
                    <th>Basic Salary</th>
                    <th>Benefits</th>
                    <th>Deductions</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$payrollRows): ?>
                    <tr>
                        <td colspan="7">No payroll records for this month.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payrollRows as $row): ?>
                        <tr>
                            <td><?= e($row['employee_code']) ?></td>
                            <td><?= e($row['full_name']) ?></td>
                            <td><?= number_format((float)$row['basic_salary'], 2) ?></td>
                            <td><?= number_format((float)$row['benefits'], 2) ?></td>
                            <td><?= number_format((float)$row['deductions'], 2) ?></td>
                            <td><?= number_format((float)$row['net_salary'], 2) ?></td>
                            <td><?= e($row['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p style="margin-top:15px">
        Records are saved as drafts. This page does not approve or pay salaries
        and does not yet calculate pension contributions.
    </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```