<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

if (isLoggedIn()) {
    logAudit($pdo, 'logout', $_SESSION['user_nama'] ?? '');
}

session_destroy();
redirect(BASE_URL . 'index.php');