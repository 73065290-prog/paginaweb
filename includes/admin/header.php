<?php
declare(strict_types=1);

$tituloAdmin = $tituloAdmin ?? 'Admin Mass';
$adminActivo = $adminActivo ?? 'dashboard';
$adminCrumb = $adminCrumb ?? 'Gestión de Tienda e Inventario';
$adminUser = mass_admin_usuario();
$fechaHoy = 'Jueves, 24 de Octubre de 2024';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($tituloAdmin) ?></title>
  <link rel="icon" href="../assets/img/logo.svg" type="image/svg+xml" />
  <link rel="stylesheet" href="../assets/css/tokens.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
<div class="admin-shell">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="admin-main">
    <header class="admin-top">
      <form class="admin-search admin-search--wide" method="get" action="inventario.php" role="search">
        <span aria-hidden="true">🔍</span>
        <label for="adminSearchQ" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0">Buscar por producto, SKU, EAN o nombre</label>
        <input id="adminSearchQ" type="search" name="q" placeholder="Buscar por producto, SKU, EAN o nombre…" value="<?= e($_GET['q'] ?? '') ?>" />
      </form>
      <div class="admin-top-right">
        <span class="admin-sync">● Sinc: En línea <small>(Última actualización: 1m)</small></span>
        <span class="admin-date"><?= e($fechaHoy) ?></span>
        <span class="admin-store"><?= e($adminUser['sedeCorta'] ?? $adminUser['sucursal']) ?></span>
        <button type="button" class="admin-bell" aria-label="Notificaciones">🔔<i>3</i></button>
        <div class="admin-account">
          <button
            type="button"
            class="admin-account-btn"
            id="adminAccountBtn"
            aria-expanded="false"
            aria-haspopup="true"
            aria-controls="adminAccountMenu"
          >
            <img class="admin-avatar" src="<?= e($adminUser['avatar']) ?>" alt="" />
            <span class="admin-account-name"><?= e($adminUser['nombre']) ?></span>
            <span class="admin-account-caret" aria-hidden="true">▾</span>
          </button>
          <div class="admin-account-menu" id="adminAccountMenu" hidden>
            <div class="admin-account-head">
              <strong><?= e($adminUser['nombre']) ?></strong>
              <span><?= e($adminUser['rol']) ?></span>
              <small><?= e($adminUser['sucursal']) ?></small>
            </div>
            <a href="index.php">Dashboard</a>
            <a href="inventario.php">Inventario</a>
            <a class="is-logout" href="logout.php">Cerrar sesión</a>
          </div>
        </div>
      </div>
    </header>
    <div class="admin-content">
