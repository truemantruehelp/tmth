<?php
require_once __DIR__ . '/includes/config.php';

header('Cache-Control: no-store, private');
$programs = ['gp' => 'TMTH GP', 'training' => 'দক্ষতা উন্নয়ন প্রশিক্ষণ'];
$_SESSION['program_request_token'] ??= bin2hex(random_bytes(32));
$submitted = !empty($_SESSION['program_request_received']);
unset($_SESSION['program_request_received']);
$error = '';
$name = '';
$phone = '';
$selected = (string) filter_input(INPUT_GET, 'program');
$selected = isset($programs[$selected]) ? $selected : 'gp';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) filter_input(INPUT_POST, 'name'));
    $phone = trim((string) filter_input(INPUT_POST, 'phone'));
    $selected = (string) filter_input(INPUT_POST, 'program');
    $token = (string) filter_input(INPUT_POST, 'token');

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
            $statement = $pdo->prepare(
                'INSERT INTO tmth_program_requests (program, applicant_name, phone, submission_key)
                 VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE id = id'
            );
            $statement->execute([$selected, $name, $phone, hash('sha256', $token)]);
            unset($_SESSION['program_request_token']);
            $_SESSION['program_request_received'] = true;
            header('Location: programs.php#request', true, 303);
            exit;
        } catch (PDOException $exception) {
            error_log('TMTH program request could not be recorded');
            http_response_code(503);
            $error = 'অনুরোধ জমা দেওয়া যায়নি। অনুগ্রহ করে পরে চেষ্টা করুন অথবা ফোন করুন।';
        }
    }
}

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
                <a class="btn btn-primary align-self-start mb-2" href="?program=training#request">প্রশিক্ষণে আগ্রহ জানান</a>
                <a href="tel:+8801859135478">প্রশিক্ষণ সম্পর্কে ফোন করুন</a>
            </div>
        </section>
    </div>
    <section id="request" class="card p-4">
        <h2 class="h4">অনুরোধ পাঠান</h2>
        <?php if ($submitted): ?>
            <p class="alert alert-success" role="status">অনুরোধ পেয়েছি। সময় ও অংশগ্রহণের বিষয়টি নিশ্চিত করতে TMTH আপনার সঙ্গে যোগাযোগ করবে।</p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form method="post" action="programs.php#request">
            <input type="hidden" name="token" value="<?= $_SESSION['program_request_token'] ?>">
            <div class="mb-3">
                <label class="form-label" for="program">সেবার ধরন</label>
                <select class="form-select" id="program" name="program" required>
                    <?php foreach ($programs as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $id === $selected ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="name">নাম</label>
                <input class="form-control" id="name" name="name" autocomplete="name" maxlength="100" required value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="phone">মোবাইল নম্বর</label>
                <input class="form-control" id="phone" name="phone" type="tel" autocomplete="tel" maxlength="14" required value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <p>TMTH এই অনুরোধের বিষয়ে যোগাযোগের জন্য আপনার নাম ও নম্বর ব্যবহার করবে।</p>
            <button class="btn btn-primary" type="submit">অনুরোধ জমা দিন</button>
        </form>
    </section>
    <p class="mt-4"><a href="admin/program_requests.php" lang="en">Staff sign-in</a></p>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
