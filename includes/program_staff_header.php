<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= programEscape($pageTitle) ?> | TMTH staff</title>
    <link rel="stylesheet" href="../assets/css/program-staff.css">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to main content</a>
<header class="masthead">
    <div class="wrap">
        <strong>True Man True Help</strong>
        <nav aria-label="Staff navigation">
            <a href="program_requests.php">Requests</a>
            <?php if ($isProgrammeAdmin): ?>
                <a href="program_staff.php">Staff access</a>
                <a href="index.php">Admin dashboard</a>
            <?php endif; ?>
            <a href="../logout.php">Sign out</a>
        </nav>
    </div>
</header>
<main class="wrap" id="main-content" tabindex="-1">
