<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/program_access.php';

$access = requireProgramAccess($pdo);
$isProgrammeAdmin = $access === 'admin';
$programmes = ['gp' => 'GP appointments', 'training' => 'Training registrations'];
$statuses = ['new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed'];
$pageTitle = $isProgrammeAdmin ? 'GP and training requests' : $programmes[$access];
$programmeFilter = (string) filter_input(INPUT_GET, 'program');
$programme = $isProgrammeAdmin ? (isset($programmes[$programmeFilter]) ? $programmeFilter : '') : $access;
$statusFilter = (string) filter_input(INPUT_GET, 'status');
$statusFilter = isset($statuses[$statusFilter]) ? $statusFilter : '';
$before = filter_input(INPUT_GET, 'before', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$filters = array_filter(['program' => $isProgrammeAdmin ? $programme : '', 'status' => $statusFilter]);
$_SESSION['program_admin_token'] ??= bin2hex(random_bytes(32));
$message = $_SESSION['program_queue_message'] ?? '';
unset($_SESSION['program_queue_message']);
$error = '';
$requests = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = (string) filter_input(INPUT_POST, 'token');
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $from = (string) filter_input(INPUT_POST, 'previous_status');
        $to = (string) filter_input(INPUT_POST, 'status');

        if (!hash_equals($_SESSION['program_admin_token'], $token)) {
            http_response_code(403);
            exit('Your session has expired. Refresh the page and try again.');
        }

        if (!$id || !isset($statuses[$from], $statuses[$to])) {
            http_response_code(422);
            exit('Select a valid request status.');
        }

        $pdo->beginTransaction();
        $conditions = ['id = ?'];
        $parameters = [$id];
        if (!$isProgrammeAdmin) {
            $conditions[] = 'program = ?';
            $parameters[] = $access;
        }
        $where = implode(' AND ', $conditions);
        $statement = $pdo->prepare("SELECT status FROM tmth_program_requests WHERE $where FOR UPDATE");
        $statement->execute($parameters);
        $current = $statement->fetchColumn();

        if ($current === false) {
            $pdo->rollBack();
            http_response_code(404);
            exit('Request not found.');
        }

        $notice = 'Status saved.';
        if ($current !== $from) {
            $notice = 'Another member of staff updated this request. Review the current status before trying again.';
        } elseif ($current !== $to) {
            $statement = $pdo->prepare("UPDATE tmth_program_requests SET status = ? WHERE $where");
            $statement->execute(array_merge([$to], $parameters));
            $statement = $pdo->prepare(
                'INSERT INTO tmth_program_status_history (request_id, changed_by, previous_status, current_status)
                 VALUES (?, ?, ?, ?)'
            );
            $statement->execute([$id, $_SESSION['user_id'], $from, $to]);
        }
        $pdo->commit();

        $_SESSION['program_queue_message'] = $notice;
        header('Location: program_requests.php?' . http_build_query($filters + ['before' => $before ?: null]), true, 303);
        exit;
    }

    $conditions = [];
    $parameters = [];
    foreach (['program' => $programme, 'status' => $statusFilter] as $field => $value) {
        if ($value !== '') {
            $conditions[] = "$field = ?";
            $parameters[] = $value;
        }
    }

    if ($before) {
        $conditions[] = 'id < ?';
        $parameters[] = $before;
    }

    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    $statement = $pdo->prepare(
        'SELECT id, program, applicant_name, phone, status, created_at
         FROM tmth_program_requests' . $where . ' ORDER BY id DESC LIMIT 51'
    );
    $statement->execute($parameters);
    $requests = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('TMTH programme queue operation failed');
    http_response_code(503);
    $error = 'Requests are temporarily unavailable. Try again later.';
}

$hasMore = count($requests) > 50;
$requests = array_slice($requests, 0, 50);
include __DIR__ . '/../includes/program_staff_header.php';
?>
<h1><?= programEscape($pageTitle) ?></h1>
<p>Contact applicants using the number they supplied. Update the request after you speak to them.</p>

<form method="get" class="filters" aria-label="Filter requests">
    <?php if ($isProgrammeAdmin): ?>
        <label>Programme
            <select name="program">
                <option value="">All programmes</option>
                <?php foreach ($programmes as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $programme === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>
    <label>Status
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $key => $label): ?>
                <option value="<?= $key ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Apply filters</button>
</form>

<?php if ($message !== ''): ?>
    <p class="notice" role="status"><?= programEscape($message) ?></p>
<?php endif; ?>
<?php if ($error !== ''): ?>
    <p class="notice error" role="alert"><?= programEscape($error) ?></p>
<?php elseif (!$requests): ?>
    <p>No requests match these filters.</p>
<?php else: ?>
    <div class="table-scroll" tabindex="0" role="region" aria-label="Requests">
        <table>
            <thead>
                <tr>
                    <th scope="col">Applicant</th>
                    <th scope="col">Phone</th>
                    <?php if ($isProgrammeAdmin): ?><th scope="col">Programme</th><?php endif; ?>
                    <th scope="col">Received</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <?php $id = (int) $request['id']; ?>
                    <tr>
                        <th scope="row"><?= programEscape($request['applicant_name']) ?><small>Request <?= $id ?></small></th>
                        <td><a href="tel:<?= programEscape($request['phone']) ?>"><?= programEscape($request['phone']) ?></a></td>
                        <?php if ($isProgrammeAdmin): ?><td><?= $programmes[$request['program']] ?></td><?php endif; ?>
                        <td><?= programEscape($request['created_at']) ?></td>
                        <td>
                            <form method="post" class="status-form">
                                <input type="hidden" name="token" value="<?= $_SESSION['program_admin_token'] ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <input type="hidden" name="previous_status" value="<?= $request['status'] ?>">
                                <label class="visually-hidden" for="status-<?= $id ?>">Status for request <?= $id ?></label>
                                <select id="status-<?= $id ?>" name="status">
                                    <?php foreach ($statuses as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= $key === $request['status'] ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" aria-label="Save status for request <?= $id ?>">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <nav class="pagination" aria-label="Request pages">
        <?php if ($before): ?>
            <a href="program_requests.php?<?= programEscape(http_build_query($filters)) ?>">Newest requests</a>
        <?php endif; ?>
        <?php if ($hasMore): ?>
            <a href="?<?= programEscape(http_build_query($filters + ['before' => end($requests)['id']])) ?>">Older requests</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
</main>
</body>
</html>
