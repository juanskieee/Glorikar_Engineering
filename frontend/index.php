<?php
/**
 * index.php — entry point. Redirects to the right area by role.
 */

require_once dirname(__DIR__) . '/backend/includes/env.php';
require_once dirname(__DIR__) . '/backend/includes/helpers.php';

start_session_secure();

if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

header('Location: ' . ($_SESSION['role'] === 'admin' ? '/admin/dashboard.php' : '/client/home.php'));
exit;