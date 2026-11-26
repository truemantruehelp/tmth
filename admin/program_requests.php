<?php
require_once __DIR__ . '/../includes/config.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
    exit;
}

header('Cache-Control: no-store, private');
$_SESSION['program_admin_token'] ??= bin2hex(random_bytes(32));
$statuses = ['new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed'];
$programs = ['gp' => 'GP appointment', 'training' => 'Training registration'];
$error = '';
$requests = [];
$filter = (string) filter_input(INPUT_GET, 'program');
$before = filter_input(INPUT_GET, 'before', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$updated = !empty($_SESSION['program_status_updated']);
unset($_SESSION['program_status_updated']);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = (string) filter_input(INPUT_POST, 'token');
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $status = (string) filter_input(INPUT_POST, 'status');
        if (!hash_equals($_SESSION['program_admin_token'], $token)) {
            http_response_code(403);
            exit('Invalid request token.');
        }
        if (!$id || !isset($statuses[$status])) {
            http_response_code(422);
            exit('Invalid request status.');
        }
        $statement = $pdo->prepare('UPDATE tmth_program_requests SET status = ? WHERE id = ?');
        $statement->execute([$status, $id]);
        $_SESSION['program_status_updated'] = true;
        header('Location: program_requests.php?' . http_build_query(['program' => $filter, 'before' => $before ?: null]), true, 303);
        exit;
    }

    $conditions = [];
    $parameters = [];
    if (isset($programs[$filter])) {
        $conditions[] = 'program = ?';
        $parameters[] = $filter;
    }
    if ($before) {
        $conditions[] = 'id < ?';
        $parameters[] = $before;
    }
    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    $statement = $pdo->prepare('SELECT id, program, applicant_name, phone, status, created_at
        FROM tmth_program_requests' . $where . ' ORDER BY id DESC LIMIT 51');
    $statement->execute($parameters);
    $requests = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('TMTH program request admin operation failed');
    http_response_code(503);
    $error = 'Program requests are temporarily unavailable.';
}

$hasMore = count($requests) > 50;
$requests = array_slice($requests, 0, 50);
$page_title = 'GP and Training Requests';
include __DIR__ . '/header.php';
?>
<main class="container-fluid py-4">
    <h1 class="h3">GP and Training Requests</h1>
    <nav aria-label="Filter requests" class="mb-3">
        <a class="btn btn-outline-primary" href="program_requests.php">All requests</a>
        <a class="btn btn-outline-primary" href="?program=gp">GP appointments</a>
        <a class="btn btn-outline-primary" href="?program=training">Training registrations</a>
    </nav>
    <?php if ($updated): ?><p class="alert alert-success" role="status">Status saved.</p><?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="alert alert-danger" role="alert"><?= $error ?></p>
    <?php elseif (!$requests): ?>
        <p>No requests on this page.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th scope="col">Request</th><th scope="col">Program</th><th scope="col">Name</th><th scope="col">Phone</th><th scope="col">Received</th><th scope="col">Status</th></tr></thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= (int) $request['id'] ?></td>
                    <td><?= $programs[$request['program']] ?></td>
                    <td><?= htmlspecialchars($request['applicant_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($request['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($request['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="token" value="<?= $_SESSION['program_admin_token'] ?>">
                            <input type="hidden" name="id" value="<?= (int) $request['id'] ?>">
                            <label class="visually-hidden" for="status-<?= (int) $request['id'] ?>">Request <?= (int) $request['id'] ?> status</label>
                            <select id="status-<?= (int) $request['id'] ?>" name="status">
                                <?php foreach ($statuses as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $key === $request['status'] ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-primary" type="submit">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($hasMore): ?>
        <a href="?<?= htmlspecialchars(http_build_query(['program' => $filter, 'before' => end($requests)['id']]), ENT_QUOTES, 'UTF-8') ?>">Older requests</a>
    <?php endif; ?>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/footer.php'; ?>
