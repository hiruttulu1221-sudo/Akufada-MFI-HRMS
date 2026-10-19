
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_role(['Admin', 'HR Admin', 'Supervisor']);

$pdo = db();
$user = current_user();
$role = $user['role'] ?? '';
$isApprover = in_array($role, ['Admin', 'HR Admin'], true);

$title = 'Performance Management';
$message = '';
$messageType = 'error';

// Load employee choices from the existing employees table.
$employees = $pdo->query(
    "SELECT id, employee_code, full_name, department, position
     FROM employees
     WHERE status = 'Active'
     ORDER BY full_name"
)->fetchAll(PDO::FETCH_ASSOC);

// Process form submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);

    $action = $_POST['action'] ?? 'create';

    if ($action === 'approve') {
        if (!$isApprover) {
            http_response_code(403);
            exit('Only HR Admin or Admin can approve reviews.');
        }

        $reviewId = filter_var(
            $_POST['review_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$reviewId || $reviewId < 1) {
            $message = 'Invalid review ID.';
        } else {
            $stmt = $pdo->prepare(
                "UPDATE performance_reviews
                 SET status = 'Approved'
                 WHERE id = ? AND status = 'Submitted'"
            );
            $stmt->execute([$reviewId]);

            if ($stmt->rowCount() === 1) {
                $message = 'Performance review approved.';
                $messageType = 'success';
            } else {
                $message = 'Review not found or it is not awaiting approval.';
            }
        }
    } elseif ($action === 'create') {
        $employeeId = filter_var(
            $_POST['employee_id'] ?? null,
            FILTER_VALIDATE_INT
        );
        $reviewYear = filter_var(
            $_POST['review_year'] ?? null,
            FILTER_VALIDATE_INT
        );
        $scoreInput = trim($_POST['score'] ?? '');
        $comments = trim($_POST['comments'] ?? '');
        $status = $_POST['status'] ?? 'Draft';

        $validEmployee = false;

        if ($employeeId && $employeeId > 0) {
            foreach ($employees as $employee) {
                if ((int)$employee['id'] === (int)$employeeId) {
                    $validEmployee = true;
                    break;
                }
            }
        }

        if (!$validEmployee) {
            $message = 'Please select a valid active employee.';
        } elseif (
            !$reviewYear ||
            $reviewYear < 2000 ||
            $reviewYear > 2100
        ) {
            $message = 'Enter a valid review year.';
        } elseif (
            $scoreInput === '' ||
            !is_numeric($scoreInput) ||
            (float)$scoreInput < 0 ||
            (float)$scoreInput > 100
        ) {
            $message = 'Score must be between 0 and 100.';
        } elseif (!in_array($status, ['Draft', 'Submitted'], true)) {
            $message = 'Invalid review status.';
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO performance_reviews
                 (employee_id, review_year, score, comments, status)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                (int)$employeeId,
                (int)$reviewYear,
                (float)$scoreInput,
                $comments,
                $status
            ]);

            $message = $status === 'Draft'
                ? 'Performance review saved as a draft.'
                : 'Performance review submitted for approval.';

            $messageType = 'success';
        }
    } else {
        $message = 'Invalid action.';
    }
}

// Load review history and employee names.
$reviews = $pdo->query(
    "SELECT pr.id, pr.employee_id, pr.review_year, pr.score,
            pr.comments, pr.status, pr.created_at,
            e.employee_code, e.full_name, e.department
     FROM performance_reviews pr
     INNER JOIN employees e ON e.id = pr.employee_id
     ORDER BY pr.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>Performance Management</h2>
        <p>Create, submit and review employee performance records.</p>
    </div>
</div>

<?php if ($message !== ''): ?>
    <div class="alert <?= e($messageType) ?>">
        <?= e($message) ?>
    </div>
<?php endif; ?>

<div class="card">
    <h3>New Performance Review</h3>

    <?php if (!$employees): ?>
        <p>No active employees are available for selection.</p>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="csrf"
                   value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="create">

            <div class="form-grid">
                <label>
                    Employee
                    <select name="employee_id" required>
                        <option value="">Select employee</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= (int)$employee['id'] ?>">
                                <?= e($employee['employee_code']) ?>
                                - <?= e($employee['full_name']) ?>
                                (<?= e($employee['department']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Review Year
                    <input type="number" name="review_year"
                           min="2000" max="2100"
                           value="<?= date('Y') ?>" required>
                </label>

                <label>
                    Performance Score (0–100)
                    <input type="number" name="score"
                           min="0" max="100" step="0.01"
                           required>
                </label>

                <label>
                    Status
                    <select name="status" required>
                        <option value="Draft">Save as Draft</option>
                        <option value="Submitted">Submit for Approval</option>
                    </select>
                </label>
            </div>

            <label>
                Comments and Recommendations
                <textarea name="comments" rows="4"
                          placeholder="Describe performance, strengths and areas for improvement."></textarea>
            </label>

            <button class="btn primary" type="submit">
                Save Performance Review
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Performance Review History</h3>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Year</th>
                    <th>Score</th>
                    <th>Comments</th>
                    <th>Status</th>
                    <?php if ($isApprover): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!$reviews): ?>
                <tr>
                    <td colspan="<?= $isApprover ? 7 : 6 ?>">
                        No performance reviews have been created yet.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td>
                            <?= e($review['employee_code']) ?>
                            - <?= e($review['full_name']) ?>
                        </td>
                        <td><?= e($review['department']) ?></td>
                        <td><?= e((string)$review['review_year']) ?></td>
                        <td>
                            <?= $review['score'] === null
                                ? '—'
                                : e((string)$review['score']) ?>
                        </td>
                        <td><?= e($review['comments'] ?? '') ?></td>
                        <td>
                            <?= e($review['status']) ?>
                        </td>

                        <?php if ($isApprover): ?>
                            <td>
                                <?php if ($review['status'] === 'Submitted'): ?>
                                    <form method="post">
                                        <input type="hidden" name="csrf"
                                               value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action"
                                               value="approve">
                                        <input type="hidden" name="review_id"
                                               value="<?= (int)$review['id'] ?>">
                                        <button class="btn primary"
                                                type="submit">
                                            Approve
                                        </button>
                                    </form>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
