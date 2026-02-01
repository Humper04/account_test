<?php
require_once '../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

echo getenv('DB_PASSWORD_ROOT'); // Should output your DB password
