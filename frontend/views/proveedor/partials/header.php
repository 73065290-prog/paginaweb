<?php
declare(strict_types=1);

$tituloProveedor = $tituloProveedor ?? 'Portal Proveedores — Mass';
$provActivo = $provActivo ?? 'dashboard';
$provCrumb = $provCrumb ?? 'Portal B2B';
$perfil = mass_proveedor_perfil();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#00472F" />
  <title><?= e($tituloProveedor) ?></title>
  <link rel="icon" href="<?= e(mass_asset('assets/img/logo.svg')) ?>" type="image/svg+xml" />
  <link rel="stylesheet" href="<?= e(mass_asset('assets/css/tokens.css')) ?>" />
  <link rel="stylesheet" href="<?= e(mass_asset('assets/css/admin.css')) ?>" />
  <link rel="stylesheet" href="<?= e(mass_asset('assets/css/proveedor.css')) ?>" />
</head>
<body class="admin-body proveedor-body">
<div class="admin-shell">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="admin-main">
    <header class="admin-top">
      <div class="prov-top-title">
        <strong><?= e($provCrumb) ?></strong>
        <span><?= e($perfil['empresa']) ?> · RUC <?= e($perfil['ruc']) ?></span>
      </div>
      <div class="admin-top-right">
        <span class="admin-sync">● Portal B2B en línea</span>
        <span class="admin-store">SLA <?= e($perfil['slaEntrega']) ?></span>
        <div class="admin-account">
          <button type="button" class="admin-account-btn" id="adminAccountBtn" aria-expanded="false" aria-haspopup="true" aria-controls="adminAccountMenu">
            <img class="admin-avatar" src="<?= e($perfil['avatar']) ?>" alt="" />
            <span class="admin-account-name"><?= e($perfil['nombre']) ?></span>
            <span class="admin-account-caret" aria-hidden="true">▾</span>
          </button>
          <div class="admin-account-menu" id="adminAccountMenu" hidden>
            <div class="admin-account-head">
              <strong><?= e($perfil['nombre']) ?></strong>
              <span><?= e($perfil['cargo']) ?></span>
              <small><?= e($perfil['empresa']) ?></small>
            </div>
            <a href="index.php">Dashboard</a>
            <a href="ordenes.php">Órdenes de compra</a>
            <a class="is-logout" href="logout.php">Cerrar sesión</a>
          </div>
        </div>
      </div>
    </header>
    <div class="admin-content">
