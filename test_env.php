<?php
require_once '/var/www/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable('/var/www/');
$dotenv->load();

echo getenv('DB_PASSWORD_ROOT'); // Should output your DB password
