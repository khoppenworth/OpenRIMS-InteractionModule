<?php
session_start();
require __DIR__ . '/../../../app/bootstrap.php';

$repository = new InteractionRepository($pdo);
$service = new InteractionService($repository);

$filters = [
    'query' => $_GET['query'] ?? '',
    'severity' => $_GET['severity'] ?? '',
];

$bundle = $service->toFhirBundle($filters);

header('Content-Type: application/fhir+json');
echo json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
