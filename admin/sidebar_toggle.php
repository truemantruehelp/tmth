<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['collapsed'])) {
        $_SESSION['sidebar_collapsed'] = ($_POST['collapsed'] === '1');
        echo json_encode(['success' => true]);
        exit;
    }
}
echo json_encode(['success' => false]);
exit;
?>
