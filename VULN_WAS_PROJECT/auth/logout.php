<?php
require_once __DIR__ . '/../includes/session.php';
$_SESSION = array();
session_destroy();
session_start();
$_SESSION['flash_message'] = 'Logged out.';
header('Location: ' . APP_URL . '/auth/login.php');
exit();
