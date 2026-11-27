<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/program_access.php';
require_once __DIR__ . '/../includes/program_workflow.php';

$access = requireProgramAccess($pdo);
$isProgrammeAdmin = $access === 'admin';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$error = '';
$records = [];
$application = null;
$values = [];
$decisions = [
    'under_review' => 'Under review', 'waitlisted' => 'Waitlisted',
    'accepted' => 'Accepted', 'declined' => 'Declined', 'completed' => 'Completed',
];

try {
    $stmt = $pdo->prepare('SELECT program, applicant_name, phone FROM tmth_program_requests WHERE id = ?');
    $stmt->execute([$id ?: 0]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    $gp = $request && $request['program'] === 'gp';
    if (!$request || ($gp ? $access !== 'gp' : !in_array($access, ['training', 'admin'], true))) {
        http_response_code(404);
        exit('Record not found.');
    }

    $fields = $gp ? [
        'patient_name' => ['Patient name', 100, true, 'text'],
        'birth_date' => ['Date of birth', 10, false, 'date'],
        'history' => ['Symptoms, relevant history and allergies', 4000, false, 'textarea'],
        'assessment' => ['Clinical assessment', 4000, true, 'textarea'],
        'recommendations' => ['Recommendations and referrals', 4000, true, 'textarea'],
        'prescription' => ['Prescription (medicine, strength, dose, route, frequency and duration)', 4000, false, 'textarea'],
        'registration_number' => ['Doctor registration number (required for prescriptions)', 60, false, 'text'],
        'follow_up' => ['Follow-up date', 10, false, 'date'],
    ] : [
        'batch' => ['Batch or enrolment reference', 120, false, 'text'],
        'notes' => ['Review notes', 4000, false, 'textarea'],
    ];
    $values = $gp ? ['patient_name' => $request['applicant_name']] : [];
    $decision = '';
    $_SESSION['tmth_case_token'] ??= bin2hex(random_bytes(32));
    $token = $_SESSION['tmth_case_token'];
    $nonce = bin2hex(random_bytes(16));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $provided = (string) filter_input(INPUT_POST, 'token');
        $nonce = (string) filter_input(INPUT_POST, 'nonce');
        if (!hash_equals($token, $provided) || !preg_match('/\A[a-f0-9]{32}\z/', $nonce)) {
            http_response_code(403);
            exit('Refresh the page and try again.');
        }
        $submissionKey = hash_hmac('sha256', $id . ':' . $nonce, $token);
        try {
            foreach ($fields as $key => $spec) {
                $values[$key] = trim((string) filter_input(INPUT_POST, $key));
            }
            $data = workflowFields($fields);
            if ($gp) {
                if (!workflowDate($data['birth_date']) || !workflowDate($data['follow_up']) ||
                    ($data['prescription'] !== null && $data['registration_number'] === null)) {
                    throw new InvalidArgumentException('Dates or doctor registration number');
                }
                $doctor = $pdo->prepare('SELECT name FROM users WHERE id = ?');
                $doctor->execute([$_SESSION['user_id']]);
                $stmt = $pdo->prepare(
                    'INSERT INTO tmth_gp_encounters
                     (request_id, doctor_id, doctor_name, patient_name, birth_date, history, assessment,
                      recommendations, prescription, registration_number, follow_up, submission_key)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id = id'
                );
                $stmt->execute([$id, $_SESSION['user_id'], $doctor->fetchColumn(), ...array_values($data), $submissionKey]);
            } else {
                $decision = (string) filter_input(INPUT_POST, 'decision');
                if (!isset($decisions[$decision]) ||
                    (in_array($decision, ['accepted', 'completed'], true) && $data['batch'] === null)) {
                    throw new InvalidArgumentException('Decision or batch');
                }
                $stmt = $pdo->prepare(
                    'INSERT INTO tmth_training_reviews
                     (request_id, reviewer_id, decision, batch, notes, submission_key)
                     VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id = id'
                );
                $stmt->execute([$id, $_SESSION['user_id'], $decision, $data['batch'], $data['notes'], $submissionKey]);
            }
            $_SESSION['tmth_case_saved'] = $id;
            header('Location: program_case.php?id=' . $id, true, 303);
            exit;
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            $error = 'Check the following field: ' . $exception->getMessage();
        }
    }

    if (!$gp) {
        $stmt = $pdo->prepare(
            'SELECT email, locality, education, experience, availability, motivation
             FROM tmth_training_applications WHERE request_id = ?'
        );
        $stmt->execute([$id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    $before = filter_input(INPUT_GET, 'before', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $table = $gp ? 'tmth_gp_encounters' : 'tmth_training_reviews';
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE request_id = ?" .
        ($before ? ' AND id < ?' : '') . ' ORDER BY id DESC LIMIT 51');
    $stmt->execute($before ? [$id, $before] : [$id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('TMTH programme record unavailable');
    http_response_code(503);
    exit('Programme records are temporarily unavailable.');
}

$pageTitle = $gp ? 'Patient consultations' : 'Training application';
$saved = ($_SESSION['tmth_case_saved'] ?? null) === $id;
if ($saved) {
    unset($_SESSION['tmth_case_saved']);
}
include __DIR__ . '/../includes/program_staff_header.php';
?>
<h1><?= programEscape($pageTitle) ?></h1>
<p><?= programEscape($request['applicant_name']) ?> · <?= programEscape($request['phone']) ?></p>
<?php if ($saved): ?><p class="notice" role="status">Record saved.</p><?php endif; ?>
<?php if ($error !== ''): ?><p class="notice error" role="alert"><?= programEscape($error) ?></p><?php endif; ?>
<?php if (!$gp): ?>
    <h2>Application details</h2>
    <?php if ($application): ?>
        <dl>
            <?php foreach (['email' => 'Email', 'locality' => 'Area', 'education' => 'Education',
                'experience' => 'Computer experience', 'availability' => 'Availability', 'motivation' => 'Learning goals'] as $key => $label): ?>
                <dt><?= $label ?></dt><dd><?= programEscape($application[$key] ?? '—') ?></dd>
            <?php endforeach; ?>
        </dl>
    <?php else: ?>
        <p>This application was received using the original contact form.</p>
    <?php endif; ?>
<?php endif; ?>
<h2><?= $gp ? 'Record consultation' : 'Record application decision' ?></h2>
<form method="post" class="workflow-form">
    <input type="hidden" name="token" value="<?= $token ?>">
    <input type="hidden" name="nonce" value="<?= $nonce ?>">
    <?php if (!$gp): ?>
        <label>Decision
            <select name="decision" required>
                <option value="">Choose a decision</option>
                <?php foreach ($decisions as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $decision === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>
    <?php workflowFieldInputs($fields, $values); ?>
    <button type="submit">Save record</button>
</form>
<h2><?= $gp ? 'Consultation history for this request' : 'Application decision history' ?></h2>
<?php if (!$records): ?><p>No records yet.</p><?php endif; ?>
<?php foreach (array_slice($records, 0, 50) as $record): ?>
    <article class="workflow-record">
        <h3><?= programEscape($gp ? $record['doctor_name'] : $decisions[$record['decision']]) ?></h3>
        <p><?= programEscape($record['created_at']) ?></p>
        <dl>
            <?php foreach ($fields as $key => [$label]): ?>
                <?php if ($record[$key] !== null): ?>
                    <dt><?= programEscape($label) ?></dt><dd><?= nl2br(programEscape($record[$key])) ?></dd>
                <?php endif; ?>
            <?php endforeach; ?>
        </dl>
        <?php if ($gp && $record['prescription'] !== null): ?>
            <a href="program_prescription.php?id=<?= (int) $record['id'] ?>">Open printable prescription</a>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
<?php if (count($records) > 50): ?>
    <a href="?id=<?= $id ?>&amp;before=<?= (int) $records[49]['id'] ?>">Older records</a>
<?php endif; ?>
</main>
</body>
</html>
