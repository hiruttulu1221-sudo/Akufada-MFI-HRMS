<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['Admin', 'HR Admin', 'Supervisor']);
$pdo = db();

// Shared module-record storage is also used by Grades and other HR setup pages.
$pdo->exec("CREATE TABLE IF NOT EXISTS hr_module_records (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 module_key VARCHAR(100) NOT NULL,
 title VARCHAR(190) NOT NULL,
 details TEXT NULL,
 status VARCHAR(40) NOT NULL DEFAULT 'Active',
 effective_date DATE NULL,
 created_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_hr_module_key(module_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$key = 'hr_positions';
$error = '';
$allowedStatuses = ['Active', 'Inactive'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? null);
    try {
        $action = (string)($_POST['action'] ?? 'save');
        $id = (int)($_POST['id'] ?? 0);
        if ($action === 'delete') {
            if ($id < 1) throw new RuntimeException('Invalid position record.');
            $st = $pdo->prepare('DELETE FROM hr_module_records WHERE id = ? AND module_key = ?');
            $st->execute([$id, $key]);
            redirect($_SERVER['PHP_SELF'] . '?deleted=1');
        }
        $name = trim((string)($_POST['position_name'] ?? ''));
        $department = trim((string)($_POST['department'] ?? ''));
        $grade = trim((string)($_POST['grade'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $status = (string)($_POST['status'] ?? 'Active');
        if ($name === '') throw new RuntimeException('Position name is required.');
        if (!in_array($status, $allowedStatuses, true)) $status = 'Active';
        $details = "Department: " . $department . "\nGrade: " . $grade . "\nDescription: " . $description;
        if ($id > 0) {
            $st = $pdo->prepare('UPDATE hr_module_records SET title=?, details=?, status=? WHERE id=? AND module_key=?');
            $st->execute([$name, $details, $status, $id, $key]);
        } else {
            $st = $pdo->prepare('INSERT INTO hr_module_records (module_key,title,details,status,created_by) VALUES (?,?,?,?,?)');
            $st->execute([$key, $name, $details, $status, current_user()['id'] ?? null]);
        }
        redirect($_SERVER['PHP_SELF'] . '?saved=1');
    } catch (Throwable $ex) {
        $error = $ex instanceof PDOException ? 'Could not save the position. Check the database connection and table structure.' : $ex->getMessage();
    }
}
$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM hr_module_records WHERE id=? AND module_key=?');
    $st->execute([(int)$_GET['edit'], $key]);
    $edit = $st->fetch() ?: null;
}
$search = trim((string)($_GET['q'] ?? ''));
$st = $pdo->prepare('SELECT * FROM hr_module_records WHERE module_key=? AND (title LIKE ? OR details LIKE ?) ORDER BY title');
$like = '%' . $search . '%';
$st->execute([$key, $like, $like]);
$rows = $st->fetchAll();
function position_detail(?string $details, string $field): string {
    $prefix = $field . ': ';
    foreach (preg_split('/\r?\n/', $details ?? '') as $line) {
        if (str_starts_with($line, $prefix)) return substr($line, strlen($prefix));
    }
    return '';
}
$title = 'Positions';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><div><h2>Positions</h2><p>Manage job positions and their department and grade information.</p></div></div>
<?php if ($error !== ''): ?><div class="alert error"><?=e($error)?></div><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><div class="alert success">Position saved successfully.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert success">Position deleted successfully.</div><?php endif; ?>
<div class="grid two">
 <div class="card">
  <h3><?= $edit ? 'Edit Position' : 'Add Position' ?></h3>
  <form method="post">
   <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
   <input type="hidden" name="action" value="save">
   <input type="hidden" name="id" value="<?=(int)($edit['id'] ?? 0)?>">
   <label>Position name<input name="position_name" maxlength="190" required value="<?=e($edit['title'] ?? '')?>"></label>
   <label>Department<input name="department" maxlength="150" value="<?=e(position_detail($edit['details'] ?? null, 'Department'))?>"></label>
   <label>Grade<input name="grade" maxlength="100" value="<?=e(position_detail($edit['details'] ?? null, 'Grade'))?>"></label>
   <label>Description<textarea name="description" rows="3"><?=e(position_detail($edit['details'] ?? null, 'Description'))?></textarea></label>
   <label>Status<select name="status"><option value="Active" <?=($edit['status'] ?? 'Active') === 'Active' ? 'selected' : ''?>>Active</option><option value="Inactive" <?=($edit['status'] ?? '') === 'Inactive' ? 'selected' : ''?>>Inactive</option></select></label>
   <button class="btn primary" type="submit"><?= $edit ? 'Update Position' : 'Save Position' ?></button>
   <?php if ($edit): ?> <a href="<?=e($_SERVER['PHP_SELF'])?>">Cancel</a><?php endif; ?>
  </form>
 </div>
 <div class="card">
  <h3>Position Directory (<?=count($rows)?>)</h3>
  <form class="toolbar" method="get"><input name="q" value="<?=e($search)?>" placeholder="Search position or department"><button class="btn secondary" type="submit">Search</button></form>
  <div class="table-wrap"><table><thead><tr><th>Position</th><th>Department</th><th>Grade</th><th>Status</th><th>Actions</th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="5">No positions found. Use the form to add the first position.</td></tr><?php endif; ?>
  <?php foreach ($rows as $row): ?><tr><td><?=e($row['title'])?></td><td><?=e(position_detail($row['details'], 'Department'))?></td><td><?=e(position_detail($row['details'], 'Grade'))?></td><td><?=e($row['status'])?></td><td><a href="?edit=<?=(int)$row['id']?>">Edit</a> <form method="post" style="display:inline" onsubmit="return confirm('Delete this position?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$row['id']?>"><button type="submit">Delete</button></form></td></tr><?php endforeach; ?>
  </tbody></table></div>
 </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
