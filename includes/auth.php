<?php
// includes/auth.php

// Check if user is logged in, if not redirect to login page
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// Check if user is admin, if not redirect to home page
function requireAdmin() {
    if ($_SESSION['user_role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
}