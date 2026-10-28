<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'app' => 'Mass Online',
    'ok' => true,
    'db' => Database::connected(),
    'db_error' => Database::lastError(),
], JSON_UNESCAPED_UNICODE);
