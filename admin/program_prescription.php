<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/program_access.php';

if (requireProgramAccess($pdo) !== 'gp') {
    http_response_code(403);
    exit('Doctor access is required.');
}
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
try {
    $stmt = $pdo->prepare(
        "SELECT encounter.request_id, encounter.patient_name, encounter.birth_date, encounter.doctor_name,
                encounter.registration_number, encounter.prescription, encounter.created_at
         FROM tmth_gp_encounters AS encounter
         JOIN tmth_program_requests AS request ON request.id = encounter.request_id
         WHERE encounter.id = ? AND request.program = 'gp' AND encounter.prescription IS NOT NULL"
    );
    $stmt->execute([$id ?: 0]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('TMTH prescription unavailable');
    http_response_code(503);
    exit('Prescription temporarily unavailable.');
}
if (!$record) {
    http_response_code(404);
    exit('Prescription not found.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TMTH GP prescription</title>
    <link rel="stylesheet" href="../assets/css/program-staff.css">
</head>
<body>
<main class="wrap">
    <h1>True Man True Help · GP prescription</h1>
    <dl>
        <?php foreach (['patient_name' => 'Patient', 'birth_date' => 'Date of birth',
            'doctor_name' => 'Doctor', 'registration_number' => 'Registration number',
            'created_at' => 'Recorded on'] as $key => $label): ?>
            <?php if ($record[$key] !== null): ?>
                <dt><?= $label ?></dt><dd><?= programEscape($record[$key]) ?></dd>
            <?php endif; ?>
        <?php endforeach; ?>
    </dl>
    <h2>Medication instructions</h2>
    <p><?= nl2br(programEscape($record['prescription'])) ?></p>
    <p>Prescriber signature __________________________________</p>
    <p class="screen-only"><a href="program_case.php?id=<?= (int) $record['request_id'] ?>">Back to consultation history</a>
        · Use your browser’s Print command to print this prescription.</p>
</main>
</body>
</html>
