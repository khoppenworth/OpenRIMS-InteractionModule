<?php
session_start();
require __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$repository = new InteractionRepository($pdo);
$service = new InteractionService($repository);

if (empty($_FILES['file']['tmp_name'])) {
    set_flash('danger', $translator->trans('import_error', ['message' => 'No file uploaded']));
    header('Location: index.php');
    exit;
}

try {
    $count = $service->importCsv($_FILES['file']['tmp_name']);
    set_flash('success', $translator->trans('import_success', ['count' => $count]));
} catch (Throwable $exception) {
    set_flash('danger', $translator->trans('import_error', ['message' => $exception->getMessage()]));
}

header('Location: index.php');
exit;
