<?php
// login.php — Google Sign-In entry point for MiniShiksha OMR
$return_url = htmlspecialchars($_GET['return'] ?? '/test.php', ENT_QUOTES, 'UTF-8');
require_once __DIR__ . '/views/login.view.php';
