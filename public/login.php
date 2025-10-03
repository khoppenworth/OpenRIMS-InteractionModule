<?php
session_start();
require __DIR__ . '/../app/bootstrap.php';

$auth = new Auth($pdo);
if ($auth->check()) {
    header('Location: index.php');
    exit;
}

$error = null;
$emailValue = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $emailValue = $email;
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = $translator->trans('login_failed');
    } else {
        if ($auth->attempt($email, $password)) {
            $user = $auth->user();
            set_flash('success', $translator->trans('login_success', ['name' => $user['name']]));
            header('Location: index.php');
            exit;
        }

        $error = $translator->trans('login_failed');
    }
}

$flash = get_flash();
$locale = $translator->getLocale();
?>
<!DOCTYPE html>
<html lang="<?= e($locale); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($translator->trans('sign_in')); ?> · <?= e($translator->trans('app_title')); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="assets/custom.css">
</head>
<body class="hold-transition login-page" style="background: linear-gradient(160deg, rgba(0,58,112,0.15), rgba(186,12,47,0.15));">
<div class="login-box">
    <div class="login-logo">
        <a href="index.php" class="text-dark"><b>Open</b>RIMS</a>
    </div>
    <div class="card card-outline card-primary">
        <div class="card-header text-center border-0">
            <h1 class="h5 mb-1 font-weight-bold text-dark"><?= e($translator->trans('login_title')); ?></h1>
            <p class="text-muted mb-0"><?= e($translator->trans('login_intro')); ?></p>
        </div>
        <div class="card-body">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']); ?>">
                    <?= e($flash['message']); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= e($error); ?>
                </div>
            <?php endif; ?>
            <form method="post" novalidate>
                <div class="input-group mb-3">
                    <input type="email" name="email" value="<?= e($emailValue); ?>" class="form-control" placeholder="<?= e($translator->trans('email')); ?>" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="<?= e($translator->trans('password')); ?>" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-accent btn-block"><i class="fas fa-sign-in-alt mr-2"></i><?= e($translator->trans('sign_in')); ?></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white border-0 text-center">
            <div class="btn-group">
                <a href="?lang=en" class="btn btn-sm btn-outline-secondary">🇬🇧 <?= e($translator->trans('english')); ?></a>
                <a href="?lang=fr" class="btn btn-sm btn-outline-secondary">🇫🇷 <?= e($translator->trans('french')); ?></a>
            </div>
        </div>
    </div>
    <p class="mt-3 mb-0 text-center"><a href="index.php" class="link-accent"><i class="fas fa-arrow-left mr-1"></i><?= e($translator->trans('nav_directory')); ?></a></p>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
