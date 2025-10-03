<?php
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'openrims_interactions',
    'username' => getenv('DB_USER') ?: 'openrims',
    'password' => getenv('DB_PASSWORD') ?: 'secret',
    'charset' => 'utf8mb4',
];
