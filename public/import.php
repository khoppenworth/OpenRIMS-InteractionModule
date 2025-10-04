<?php
session_start();
require __DIR__ . '/../app/bootstrap.php';

$auth = new Auth($pdo);
if (!$auth->hasRole('admin')) {
    set_flash('danger', $translator->trans('auth_import_only'));
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_FILES['file']['tmp_name'])) {
        set_flash('danger', $translator->trans('import_error', ['message' => 'No file uploaded']));
        header('Location: import.php');
        exit;
    }

    $repository = new InteractionRepository($pdo);
    $service = new InteractionService($repository);

    try {
        $count = $service->importCsv($_FILES['file']['tmp_name']);
        set_flash('success', $translator->trans('import_success', ['count' => $count]));
    } catch (Throwable $exception) {
        set_flash('danger', $translator->trans('import_error', ['message' => $exception->getMessage()]));
    }

    header('Location: import.php');
    exit;
}

$user = $auth->user();
$canExport = $auth->hasRole('staff', 'admin');
$canImport = $auth->hasRole('admin');
$roleLabel = null;
if ($user) {
    $roleKey = 'role_' . $user['role'];
    $roleLabel = $translator->trans($roleKey);
}
$flash = get_flash();
$currentPage = 'import';
?>
<!DOCTYPE html>
<html lang="<?= e($translator->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($translator->trans('nav_import')); ?> · <?= e($translator->trans('app_title')); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="assets/custom.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto align-items-center">
            <?php if ($user && $roleLabel): ?>
                <li class="nav-item d-none d-md-inline-block text-sm text-muted mr-3">
                    <i class="fas fa-user-shield mr-1 text-secondary"></i><?= e($translator->trans('welcome_user', ['name' => $user['name'], 'role' => $roleLabel])); ?>
                </li>
            <?php endif; ?>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#"><i class="fas fa-language mr-1"></i><?= e($translator->trans('language')); ?></a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="?lang=en" class="dropdown-item">🇬🇧 <?= e($translator->trans('english')); ?></a>
                    <a href="?lang=fr" class="dropdown-item">🇫🇷 <?= e($translator->trans('french')); ?></a>
                </div>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link text-center">
            <span class="brand-text font-weight-light">OpenRIMS</span>
        </a>
        <div class="sidebar">
            <nav class="mt-3">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link<?= $currentPage === 'index' ? ' active' : ''; ?>">
                            <i class="nav-icon fas fa-notes-medical"></i>
                            <p><?= e($translator->trans('nav_directory')); ?></p>
                        </a>
                    </li>
                    <?php if ($canExport): ?>
                        <li class="nav-item">
                            <a href="export.php" class="nav-link<?= $currentPage === 'export' ? ' active' : ''; ?>">
                                <i class="nav-icon fas fa-file-export"></i>
                                <p><?= e($translator->trans('nav_export')); ?></p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($canImport): ?>
                        <li class="nav-item">
                            <a href="import.php" class="nav-link<?= $currentPage === 'import' ? ' active' : ''; ?>">
                                <i class="nav-icon fas fa-file-import"></i>
                                <p><?= e($translator->trans('nav_import')); ?></p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="api/fhir/bundle.php" target="_blank" class="nav-link<?= $currentPage === 'fhir' ? ' active' : ''; ?>">
                            <i class="nav-icon fas fa-share-alt"></i>
                            <p><?= e($translator->trans('nav_fhir')); ?></p>
                        </a>
                    </li>
                </ul>

                <div class="mt-4">
                    <span class="nav-header"><?= e($translator->trans('nav_account')); ?></span>
                    <ul class="nav nav-pills nav-sidebar flex-column">
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p><?= e($translator->trans('nav_sign_out')); ?></p>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper" style="background: transparent;">
        <section class="content pt-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h1 class="h4 font-weight-bold mb-2"><i class="fas fa-file-import mr-2"></i><?= e($translator->trans('import_heading')); ?></h1>
                                <p class="text-muted mb-0"><?= e($translator->trans('import_hint')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show">
                        <?= e($flash['message']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-8 col-xl-6">
                        <div class="card">
                            <form method="post" enctype="multipart/form-data">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-semibold" for="csv-file"><?= e($translator->trans('upload_csv')); ?></label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="csv-file" name="file" accept=".csv" required>
                                            <label class="custom-file-label" for="csv-file"><?= e($translator->trans('choose_file')); ?></label>
                                        </div>
                                    </div>
                                    <p class="text-sm text-muted mb-0"><i class="fas fa-info-circle mr-2"></i><?= e($translator->trans('import_instructions')); ?></p>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <span class="text-muted text-sm"><?= e($translator->trans('import_history_note')); ?></span>
                                    <button type="submit" class="btn btn-accent"><i class="fas fa-cloud-upload-alt mr-2"></i><?= e($translator->trans('submit')); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer footer-dark mx-3 mb-3">
        <strong>&copy; <?= date('Y'); ?> OpenRIMS.</strong> <?= e($translator->trans('tagline')); ?>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
<script>
    $(function () {
        bsCustomFileInput.init();
    });
</script>
</body>
</html>
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
