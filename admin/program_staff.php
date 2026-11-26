<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/program_access.php';

requireProgramAccess($pdo, true);
$isProgrammeAdmin = true;
$pageTitle = 'Programme staff access';
$roles = ['gp' => 'Doctor', 'training' => 'Trainer'];
$_SESSION['program_staff_token'] ??= bin2hex(random_bytes(32));
$message = $_SESSION['program_staff_message'] ?? '';
unset($_SESSION['program_staff_message']);
$error = '';
$staff = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = (string) filter_input(INPUT_POST, 'token');
        $action = (string) filter_input(INPUT_POST, 'action');

        if (!hash_equals($_SESSION['program_staff_token'], $token)) {
            http_response_code(403);
            exit('Your session has expired. Refresh the page and try again.');
        }

        if ($action === 'grant') {
            $email = trim((string) filter_input(INPUT_POST, 'email'));
            $role = (string) filter_input(INPUT_POST, 'role');
            if (strlen($email) <= 100 && filter_var($email, FILTER_VALIDATE_EMAIL) && isset($roles[$role])) {
                $statement = $pdo->prepare(
                    "SELECT id, password FROM users
                     WHERE email = ? AND role = 'user' AND status = 'active' AND COALESCE(is_guest, 0) = 0
                     LIMIT 2"
                );
                $statement->execute([$email]);
                $matches = $statement->fetchAll(PDO::FETCH_ASSOC);
                if (count($matches) === 1 && !password_verify('123456', $matches[0]['password'])) {
                    $statement = $pdo->prepare(
                        'INSERT INTO tmth_program_staff (user_id, program) VALUES (?, ?)
                         ON DUPLICATE KEY UPDATE program = ?'
                    );
                    $statement->execute([$matches[0]['id'], $role, $role]);
                    $_SESSION['program_staff_message'] = 'Staff access saved.';
                    header('Location: program_staff.php', true, 303);
                    exit;
                }
                $error = 'Use the email address of one active, registered account with its own password.';
            } else {
                $error = 'Enter a valid email address and choose a role.';
            }
        } elseif ($action === 'revoke') {
            $id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!$id) {
                http_response_code(422);
                exit('Select a valid staff account.');
            }
            $statement = $pdo->prepare('DELETE FROM tmth_program_staff WHERE user_id = ?');
            $statement->execute([$id]);
            $_SESSION['program_staff_message'] = 'Staff access removed.';
            header('Location: program_staff.php', true, 303);
            exit;
        } else {
            http_response_code(422);
            exit('Select a valid access action.');
        }

        http_response_code(422);
    }

    $staff = $pdo->query(
        'SELECT users.id, users.name, users.email, users.status, staff.program
         FROM tmth_program_staff AS staff JOIN users ON users.id = staff.user_id
         ORDER BY users.name, users.id'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('TMTH programme staff operation failed');
    http_response_code(503);
    $error = 'Staff access is temporarily unavailable. Try again later.';
}

include __DIR__ . '/../includes/program_staff_header.php';
?>
<h1>Programme staff access</h1>
<p>Give an existing TMTH account access to one programme. Use the same form to change an assignment.</p>

<?php if ($message !== ''): ?>
    <p class="notice" role="status"><?= programEscape($message) ?></p>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <p class="notice error" role="alert"><?= programEscape($error) ?></p>
<?php endif; ?>

<form method="post" class="filters">
    <input type="hidden" name="token" value="<?= $_SESSION['program_staff_token'] ?>">
    <input type="hidden" name="action" value="grant">
    <label>Account email
        <input name="email" type="email" maxlength="100" required>
    </label>
    <label>Role
        <select name="role" required>
            <option value="">Choose a role</option>
            <?php foreach ($roles as $key => $label): ?>
                <option value="<?= $key ?>"><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Save staff access</button>
</form>

<h2>Assigned accounts</h2>
<?php if ($staff): ?>
    <div class="table-scroll" tabindex="0" role="region" aria-label="Assigned staff">
        <table>
            <thead>
                <tr>
                    <th scope="col">Name</th><th scope="col">Email</th><th scope="col">Role</th>
                    <th scope="col">Account status</th><th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff as $account): ?>
                    <tr>
                        <th scope="row"><?= programEscape($account['name']) ?></th>
                        <td><?= programEscape($account['email'] ?? '') ?></td>
                        <td><?= $roles[$account['program']] ?></td>
                        <td><?= programEscape(ucfirst($account['status'] ?? 'inactive')) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="token" value="<?= $_SESSION['program_staff_token'] ?>">
                                <input type="hidden" name="action" value="revoke">
                                <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                                <button type="submit" aria-label="Remove access for <?= programEscape($account['name']) ?>">Remove access</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php elseif (http_response_code() !== 503): ?>
    <p>No staff access has been assigned.</p>
<?php endif; ?>
</main>
</body>
</html>
