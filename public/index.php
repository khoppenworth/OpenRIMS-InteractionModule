<?php
session_start();
require __DIR__ . '/../app/bootstrap.php';

$auth = new Auth($pdo);
$user = $auth->user();
$canExport = $auth->hasRole('staff', 'admin');
$canImport = $auth->hasRole('admin');

$repository = new InteractionRepository($pdo);
$service = new InteractionService($repository);

$filters = [
    'query' => $_GET['query'] ?? '',
    'severity' => $_GET['severity'] ?? '',
];
$filtersQuery = http_build_query(array_filter($filters));

$interactions = $service->list($filters);
$severityOptions = [
    '' => $translator->trans('severity_all'),
    'major' => $translator->trans('severity_major'),
    'moderate' => $translator->trans('severity_moderate'),
    'minor' => $translator->trans('severity_minor'),
];

$flash = get_flash();
$roleLabel = null;
if ($user) {
    $roleKey = 'role_' . $user['role'];
    $roleLabel = $translator->trans($roleKey);
}
$currentPage = 'index';

function severity_badge(string $severity): string
{
    $class = match ($severity) {
        'major' => 'badge-severity-major',
        'minor' => 'badge-severity-minor',
        default => 'badge-severity-moderate',
    };

    return sprintf('<span class="badge %s">%s</span>', $class, ucfirst($severity));
}
?>
<!DOCTYPE html>
<html lang="<?= e($translator->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($translator->trans('app_title')); ?></title>
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
            <li class="nav-item d-none d-sm-inline-block">
                <a href="index.php" class="nav-link text-bold"><i class="fas fa-notes-medical mr-2"></i><?= e($translator->trans('app_title')); ?></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
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
                        <a href="api/fhir/bundle.php" target="_blank" class="nav-link">
                            <i class="nav-icon fas fa-share-alt"></i>
                            <p><?= e($translator->trans('nav_fhir')); ?></p>
                        </a>
                    </li>
                </ul>

                <div class="mt-4">
                    <span class="nav-header"><?= e($translator->trans('nav_account')); ?></span>
                    <ul class="nav nav-pills nav-sidebar flex-column">
                        <?php if ($user): ?>
                            <li class="nav-item">
                                <a href="logout.php" class="nav-link">
                                    <i class="nav-icon fas fa-sign-out-alt"></i>
                                    <p><?= e($translator->trans('nav_sign_out')); ?></p>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="login.php" class="nav-link">
                                    <i class="nav-icon fas fa-sign-in-alt"></i>
                                    <p><?= e($translator->trans('nav_sign_in')); ?></p>
                                </a>
                            </li>
                        <?php endif; ?>
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
                        <div class="welcome-banner p-4 mb-4">
                            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                                <div class="mb-3 mb-lg-0">
                                    <h1 class="h3 font-weight-bold mb-2"><i class="fas fa-notes-medical mr-2"></i><?= e($translator->trans('app_title')); ?></h1>
                                    <p class="mb-0 lead" style="max-width: 640px;">
                                        <?= e($translator->trans('tagline')); ?>
                                    </p>
                                </div>
                                <div class="text-lg-right">
                                    <div class="text-uppercase small text-white-50 mb-1"><?= e($translator->trans('fhir_endpoint')); ?></div>
                                    <a href="api/fhir/bundle.php?<?= http_build_query(array_filter($filters)); ?>" target="_blank" class="btn btn-light btn-sm shadow-sm">
                                        <i class="fas fa-share-alt mr-2"></i><?= e($translator->trans('download_fhir')); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($user): ?>
                    <div class="row mb-4">
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h2 class="h6 text-uppercase text-muted mb-3"><i class="fas fa-tools mr-2 text-secondary"></i><?= e($translator->trans('nav_tools')); ?></h2>
                                    <ul class="list-unstyled mb-0">
                                        <?php if ($canExport): ?>
                                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i><a href="export.php" class="text-body"><?= e($translator->trans('nav_export')); ?></a></li>
                                        <?php endif; ?>
                                        <?php if ($canImport): ?>
                                            <li><i class="fas fa-check text-success mr-2"></i><a href="import.php" class="text-body"><?= e($translator->trans('nav_import')); ?></a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h1 class="h3 text-bold mb-2"><?= e($translator->trans('app_title')); ?></h1>
                                <p class="text-muted mb-0"><?= e($translator->trans('tagline')); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h2 class="h5 text-bold"><i class="fas fa-exchange-alt mr-2"></i><?= e($translator->trans('manage_data')); ?></h2>
                                <p class="mb-3"><?= e($translator->trans('table_hint')); ?></p>
                                <div class="d-flex flex-wrap" style="gap:0.5rem;">
                                    <a class="btn btn-sm btn-outline-dark" href="export.php?<?= http_build_query(array_filter($filters)); ?>" title="<?= e($translator->trans('export')); ?>"><i class="fas fa-file-export mr-1"></i><?= e($translator->trans('export')); ?></a>
                                    <button class="btn btn-sm btn-outline-dark" data-toggle="modal" data-target="#importModal"><i class="fas fa-file-import mr-1"></i><?= e($translator->trans('import')); ?></button>
                                    <a class="btn btn-sm btn-outline-dark" href="api/fhir/bundle.php?<?= http_build_query(array_filter($filters)); ?>" target="_blank"><i class="fas fa-share-alt mr-1"></i><?= e($translator->trans('download_fhir')); ?></a>
                                </div>
                                <p class="mt-2 mb-0"><i class="fas fa-link mr-1"></i><?= e($translator->trans('fhir_endpoint')); ?>: <code>/api/fhir/bundle.php</code></p>
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

                <div class="card">
                    <div class="card-header border-0 d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between">
                        <form class="form-inline flex-grow-1" method="get">
                            <div class="input-group mr-2 flex-grow-1">
                                <input type="text" class="form-control" name="query" value="<?= e($filters['query']); ?>" placeholder="<?= e($translator->trans('search_placeholder')); ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-accent" type="submit"><i class="fas fa-search"></i></button>
                    <div class="card-header border-0">
                        <form class="form-inline" method="get">
                            <div class="input-group mr-2 flex-grow-1">
                                <input type="text" class="form-control" name="query" value="<?= e($filters['query']); ?>" placeholder="<?= e($translator->trans('search_placeholder')); ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-warning" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                            <div class="form-group mr-2">
                                <select class="form-control" name="severity">
                                    <?php foreach ($severityOptions as $value => $label): ?>
                                        <option value="<?= e($value); ?>" <?= $filters['severity'] === $value ? 'selected' : ''; ?>><?= e($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <a class="btn btn-outline-secondary" href="index.php"><?= e($translator->trans('reset')); ?></a>
                        </form>
                        <?php if ($canExport || $canImport): ?>
                            <div class="mt-3 mt-lg-0">
                                <?php if ($canExport): ?>
                                    <a class="btn btn-sm btn-outline-secondary mr-2" href="export.php<?= $filtersQuery ? '?' . e($filtersQuery) : ''; ?>">
                                        <i class="fas fa-file-export mr-1"></i><?= e($translator->trans('export')); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($canImport): ?>
                                    <a class="btn btn-sm btn-accent" href="import.php">
                                        <i class="fas fa-file-import mr-1"></i><?= e($translator->trans('import')); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                            <a class="btn btn-outline-secondary" href="index.php">Reset</a>
                        </form>
                    </div>
                    <div class="card-body">
                        <?php if (!$interactions): ?>
                            <div class="alert alert-light border text-center mb-0"><?= e($translator->trans('no_results')); ?></div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= e($translator->trans('drug_a')); ?></th>
                                            <th><?= e($translator->trans('drug_b')); ?></th>
                                            <th><?= e($translator->trans('severity')); ?></th>
                                            <th><?= e($translator->trans('clinical_management')); ?></th>
                                            <th><?= e($translator->trans('evidence_level')); ?></th>
                                            <th><?= e($translator->trans('source')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($interactions as $interaction): ?>
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold"><?= e($interaction['drug_a_name']); ?></div>
                                                    <small class="text-muted"><?= e($translator->trans('atc_code')); ?>: <?= e($interaction['drug_a_atc']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold"><?= e($interaction['drug_b_name']); ?></div>
                                                    <small class="text-muted"><?= e($translator->trans('atc_code')); ?>: <?= e($interaction['drug_b_atc']); ?></small>
                                                </td>
                                                <td><?= severity_badge($interaction['severity']); ?></td>
                                                <td>
                                                    <div><?= e($interaction['clinical_management']); ?></div>
                                                    <small class="text-muted d-block mt-1"><?= e($translator->trans('description')); ?>: <?= e($interaction['description']); ?></small>
                                                </td>
                                                <td><?= e($interaction['evidence_level']); ?></td>
                                                <td>
                                                    <?php if (!empty($interaction['source_url'])): ?>
                                                        <a href="<?= e($interaction['source_url']); ?>" target="_blank" rel="noopener" class="link-accent"><?= e(parse_url($interaction['source_url'], PHP_URL_HOST) ?: $interaction['source_url']); ?></a>
                                                        <a href="<?= e($interaction['source_url']); ?>" target="_blank" rel="noopener" class="text-warning"><?= e(parse_url($interaction['source_url'], PHP_URL_HOST) ?: $interaction['source_url']); ?></a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content" method="post" action="import.php" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-import mr-2"></i><?= e($translator->trans('upload_csv')); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><?= e($translator->trans('import_instructions')); ?></p>
                <div class="form-group">
                    <label class="btn btn-block btn-outline-secondary">
                        <input type="file" name="file" accept=".csv" class="d-none" required>
                        <span><i class="fas fa-upload mr-2"></i><?= e($translator->trans('choose_file')); ?></span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><?= e($translator->trans('cancel')); ?></button>
                <button type="submit" class="btn btn-warning"><?= e($translator->trans('submit')); ?></button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
</body>
</html>
