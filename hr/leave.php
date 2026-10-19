
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();

$pdo = db();
$user = current_user();

$employeeId = null;

if (is_array($user) && !empty($user['id'])) {
    $check = $pdo->prepare(
        "SELECT employee_id
         FROM users
         WHERE id = ? AND is_active = 1
         LIMIT 1"
    );

    $check->execute([(int)$user['id']]);
    $result = $check->fetchColumn();

    if ($result !== false && $result !== null) {
        $employeeId = (int)$result;
    }
}

$message = '';
$messageType = 'error';

$message = '';
$messageType = 'error';

// Process a new leave request.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);

    // Do not insert a request without a linked employee.
    if (empty($employeeId) || !is_numeric($employeeId)) {
        $message = 'Your login account is not linked to an employee record. Please contact HR Admin to link your account before submitting leave.';
    } else {
        $leaveType = trim($_POST['leave_type'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $reason = trim($_POST['reason'] ?? '');
        $allowedTypes = ['Annual', 'Sick', 'Maternity', 'Emergency'];

        $start = DateTime::createFromFormat('!Y-m-d', $startDate);
        $end = DateTime::createFromFormat('!Y-m-d', $endDate);

        if (
            !in_array($leaveType, $allowedTypes, true) ||
            !$start || $start->format('Y-m-d') !== $startDate ||
            !$end || $end->format('Y-m-d') !== $endDate
        ) {
            $message = 'Please enter a valid leave type and dates.';
        } elseif ($end < $start) {
            $message = 'The end date cannot be before the start date.';
        } elseif ($reason === '') {
            $message = 'Please enter the reason for your leave.';
        } else {
            // Count calendar days, including the start and end dates.
            $days = (int)$start->diff($end)->days + 1;

            $st = $pdo->prepare(
                "INSERT INTO leave_requests
                (employee_id, leave_type, start_date, end_date,
                 days, reason, status)
                VALUES (?, ?, ?, ?, ?, ?, 'Pending')"
            );

            $st->execute([
                (int)$employeeId,
                $leaveType,
                $startDate,
                $endDate,
                $days,
                $reason
            ]);

            $message = 'Leave request submitted successfully.';
            $messageType = 'success';
        }
    }
}

$title = 'Leave';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>Leave Management</h2>
        <p>Submit and track employee leave requests.</p>
    </div>
</div>

<?php if ($message !== ''): ?>
    <div class="alert <?= e($messageType) ?>">
        <?= e($message) ?>
    </div>
<?php endif; ?>

<div class="grid two">
    <div class="card">
        <h3>New Leave Request</h3>

        <form method="post">
            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >

            <label>
                Leave Type
                <select name="leave_type" required>
                    <option value="Annual">Annual</option>
                    <option value="Sick">Sick</option>
                    <option value="Maternity">Maternity</option>
                    <option value="Emergency">Emergency</option>
                </select>
            </label>

            <div class="form-grid">
                <label>
                    Start Date
                    <input type="date" name="start_date" required>
                </label>

                <label>
                    End Date
                    <input type="date" name="end_date" required>
                </label>
            </div>

            <label>
                Reason
                <textarea name="reason" rows="4" required></textarea>
            </label>

            <button class="btn primary" type="submit">
                Submit Request
            </button>
        </form>
    </div>

    <div class="card">
        <h3>My Requests</h3>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($employeeId) && is_numeric($employeeId)): ?>
                    <?php
                    $st = $pdo->prepare(
                        "SELECT * FROM leave_requests
                         WHERE employee_id = ?
                         ORDER BY id DESC"
                    );
                    $st->execute([(int)$employeeId]);
                    $requests = $st->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (!$requests): ?>
                        <tr>
                            <td colspan="4">No leave requests yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td><?= e($r['leave_type']) ?></td>
                                <td>
                                    <?= e($r['start_date']) ?>
                                    &rarr;
                                    <?= e($r['end_date']) ?>
                                </td>
                                <td><?= e((string)$r['days']) ?></td>
                                <td>
                                    <span class="badge <?= strtolower($r['status']) === 'approved' ? 'green' : 'yellow' ?>">
                                        <?= e($r['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">
                            Your account is not linked to an employee record.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
