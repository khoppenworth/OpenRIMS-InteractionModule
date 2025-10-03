<?php
$config = require __DIR__ . '/../config/database.php';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['host'],
    $config['port'],
    $config['database'],
    $config['charset']
);

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    die('Database connection failed: ' . $exception->getMessage());
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/InteractionRepository.php';
require_once __DIR__ . '/InteractionService.php';

$translator = new Translator(__DIR__ . '/../resources/lang');
$locale = determine_locale($translator);
$translator->setLocale($locale);
