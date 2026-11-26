<?php

function programAccess(PDO $pdo): ?string
{
    if (!isLoggedIn() || empty($_SESSION['password_authenticated']) || isGuestUser()) {
        return null;
    }

    $statement = $pdo->prepare(
        'SELECT users.role, staff.program
         FROM users LEFT JOIN tmth_program_staff AS staff ON staff.user_id = users.id
         WHERE users.id = ? AND users.status = ? AND COALESCE(users.is_guest, 0) = 0'
    );
    $statement->execute([$_SESSION['user_id'], 'active']);
    $account = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        return null;
    }

    return $account['role'] === 'admin' ? 'admin' : $account['program'];
}

function programLoginDestination(PDO $pdo): string
{
    if (isAdmin()) {
        return 'admin/index.php';
    }

    try {
        if (programAccess($pdo) !== null) {
            return 'admin/program_requests.php';
        }
    } catch (PDOException $exception) {
        error_log('TMTH programme access lookup unavailable');
    }

    return 'dashboard.php';
}

function requireProgramAccess(PDO $pdo, bool $adminOnly = false): string
{
    header('Cache-Control: no-store, private');
    header('Referrer-Policy: no-referrer');
    header('X-Robots-Tag: noindex, nofollow');

    if (!isLoggedIn() || empty($_SESSION['password_authenticated'])) {
        redirect('../login.php?next=programs');
    }

    try {
        $access = programAccess($pdo);
    } catch (PDOException $exception) {
        error_log('TMTH programme access lookup unavailable');
        http_response_code(503);
        exit('Programme services are temporarily unavailable.');
    }

    if ($access === null || ($adminOnly && $access !== 'admin')) {
        http_response_code(403);
        exit('Your account does not have access to this service.');
    }

    return $access;
}

function programEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
