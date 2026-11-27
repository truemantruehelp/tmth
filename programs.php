<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/program_access.php';
require_once __DIR__ . '/includes/program_workflow.php';

header('Cache-Control: no-store, private');
$programs = ['gp' => 'TMTH GP', 'training' => 'দক্ষতা উন্নয়ন প্রশিক্ষণ'];
$_SESSION['program_request_token'] ??= bin2hex(random_bytes(32));
$submitted = !empty($_SESSION['program_request_received']);
unset($_SESSION['program_request_received']);
$error = '';
$trainingFields = [
    'email' => ['ইমেইল (ঐচ্ছিক)', 254, false, 'email'],
    'locality' => ['এলাকা ও জেলা', 120, true, 'text'],
    'education' => ['শিক্ষাগত যোগ্যতা', 120, true, 'text'],
    'experience' => ['কম্পিউটার ব্যবহারের অভিজ্ঞতা', 32, true, 'text'],
    'availability' => ['কোন দিন ও সময়ে প্রশিক্ষণ নিতে পারবেন?', 120, true, 'text'],
    'motivation' => ['কী শিখতে চান?', 1500, true, 'textarea'],
];
$trainingValues = [];
$name = '';
$phone = '';
$selected = (string) filter_input(INPUT_GET, 'program');
$selected = isset($programs[$selected]) ? $selected : 'gp';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) filter_input(INPUT_POST, 'name'));
    $phone = trim((string) filter_input(INPUT_POST, 'phone'));
    $selected = (string) filter_input(INPUT_POST, 'program');
    $token = (string) filter_input(INPUT_POST, 'token');
    if ($selected === 'training') {
        foreach ($trainingFields as $key => $spec) {
            $trainingValues[$key] = trim((string) filter_input(INPUT_POST, $key));
        }
    }

    if (!hash_equals($_SESSION['program_request_token'], $token)) {
        http_response_code(403);
        $error = 'অনুগ্রহ করে পেজটি রিফ্রেশ করে আবার চেষ্টা করুন।';
    } elseif (!isset($programs[$selected]) ||
        !preg_match('/^[^\p{C}]{2,100}$/u', $name) ||
        !preg_match('/^(?:\+880|880|0)1[3-9][0-9]{8}$/D', $phone)) {
        http_response_code(422);
        $error = 'সেবার ধরন, নাম এবং সঠিক বাংলাদেশি মোবাইল নম্বর দিন।';
    } else {
        $phone = '+880' . substr($phone, -10);
        try {
            $details = $selected === 'training' ? workflowFields($trainingFields) : null;
            if ($details !== null && $details['email'] !== null &&
                !filter_var($details['email'], FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException($trainingFields['email'][0]);
            }
            $pdo->beginTransaction();
            $statement = $pdo->prepare(
                'INSERT INTO tmth_program_requests (program, applicant_name, phone, submission_key)
                 VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
            );
            $statement->execute([$selected, $name, $phone, hash('sha256', $selected . ':' . $token)]);
            if ($details !== null) {
                $requestId = $pdo->lastInsertId();
                $statement = $pdo->prepare(
                    'INSERT INTO tmth_training_applications
                     (request_id, email, locality, education, experience, availability, motivation)
                     VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE request_id = request_id'
                );
                $statement->execute([$requestId, ...array_values($details)]);
            }
            $pdo->commit();
            unset($_SESSION['program_request_token']);
            $_SESSION['program_request_received'] = true;
            header('Location: programs.php?program=' . $selected . '#request', true, 303);
            exit;
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            $error = 'অনুগ্রহ করে এই তথ্যটি যাচাই করুন: ' . $exception->getMessage();
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('TMTH program request could not be recorded');
            http_response_code(503);
            $error = 'অনুরোধ জমা দেওয়া যায়নি। অনুগ্রহ করে পরে চেষ্টা করুন অথবা ফোন করুন।';
        }
    }
}

$selected = isset($programs[$selected]) ? $selected : 'gp';
$page_title = 'GP and Skill Development Training';
include __DIR__ . '/includes/header.php';
?>
<main class="container py-5" lang="bn">
    <h1>স্বাস্থ্যসেবা ও দক্ষতা উন্নয়ন</h1>

    <div class="row g-4 my-3">
        <section class="col-md-6" id="gp">
            <div class="card p-4 h-100">
                <h2 class="h4">TMTH GP</h2>
                <p>চিকিৎসকের সঙ্গে প্রাথমিক পরামর্শের জন্য অ্যাপয়েন্টমেন্টের অনুরোধ পাঠান।</p>
                <a class="btn btn-primary align-self-start mb-2" href="?program=gp#request">অ্যাপয়েন্টমেন্টের অনুরোধ করুন</a>
                <a href="tel:+8801859135478">GP সেবা সম্পর্কে ফোন করুন</a>
            </div>
        </section>
        <section class="col-md-6" id="training">
            <div class="card p-4 h-100">
                <h2 class="h4">দক্ষতা উন্নয়ন প্রশিক্ষণ</h2>
                <p>কম্পিউটারের প্রাথমিক দক্ষতা বিষয়ক প্রশিক্ষণের পরবর্তী ব্যাচের জন্য আগ্রহ জানান।</p>
                <a class="btn btn-primary align-self-start mb-2" href="?program=training#request">প্রশিক্ষণের জন্য আবেদন করুন</a>
                <a href="tel:+8801859135478">প্রশিক্ষণ সম্পর্কে ফোন করুন</a>
            </div>
        </section>
    </div>
    <section id="request" class="card p-4">
        <h2 class="h4"><?= $selected === 'training' ? 'প্রশিক্ষণের আবেদন' : 'GP অ্যাপয়েন্টমেন্টের অনুরোধ' ?></h2>
        <?php if ($submitted): ?>
            <p class="alert alert-success" role="status">অনুরোধ পেয়েছি। সময় ও অংশগ্রহণের বিষয়টি নিশ্চিত করতে TMTH আপনার সঙ্গে যোগাযোগ করবে।</p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="programs.php?program=<?= programEscape($selected) ?>#request">
            <input type="hidden" name="token" value="<?= $_SESSION['program_request_token'] ?>">
            <input type="hidden" name="program" value="<?= programEscape($selected) ?>">
            <div class="mb-3">
                <label class="form-label" for="name">নাম</label>
                <input class="form-control" id="name" name="name" autocomplete="name" maxlength="100" required value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="phone">মোবাইল নম্বর</label>
                <input class="form-control" id="phone" name="phone" type="tel" autocomplete="tel" maxlength="14" required value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <?php if ($selected === 'training'): ?>
                <div class="vstack gap-3 mb-3">
                    <?php workflowFieldInputs($trainingFields, $trainingValues, true); ?>
                </div>
                <p>TMTH আপনার আবেদন পর্যালোচনা ও যোগাযোগের জন্য এই তথ্য ব্যবহার করবে।</p>
            <?php else: ?>
                <p>TMTH এই অনুরোধের বিষয়ে যোগাযোগের জন্য আপনার নাম ও নম্বর ব্যবহার করবে।</p>
            <?php endif; ?>
            <button class="btn btn-primary" type="submit">অনুরোধ জমা দিন</button>
        </form>
    </section>
    <p class="mt-4"><a href="admin/program_requests.php" lang="en">Staff sign-in</a></p>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
