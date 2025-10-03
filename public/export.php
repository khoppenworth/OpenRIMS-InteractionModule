<?php
session_start();
require __DIR__ . '/../app/bootstrap.php';

$auth = new Auth($pdo);
if (!$auth->hasRole('staff', 'admin')) {
    set_flash('danger', $translator->trans('auth_export_only'));
    header('Location: index.php');
    exit;
}

$repository = new InteractionRepository($pdo);
$service = new InteractionService($repository);

$filters = [
    'query' => $_GET['query'] ?? '',
    'severity' => $_GET['severity'] ?? '',
];

$csv = $service->exportCsv($filters);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="interactions-' . gmdate('Ymd-His') . '.csv"');
header('Content-Length: ' . strlen($csv));

echo $csv;
exit;
