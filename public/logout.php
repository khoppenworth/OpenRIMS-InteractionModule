<?php
session_start();
require __DIR__ . '/../app/bootstrap.php';

$auth = new Auth($pdo);
if ($auth->check()) {
    $auth->logout();
    session_regenerate_id(true);
    set_flash('success', $translator->trans('logout_success'));
}

header('Location: index.php');
exit;
