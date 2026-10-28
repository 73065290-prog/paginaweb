<?php
declare(strict_types=1);
$tituloPagina = $tituloPagina ?? 'Mass Online — Tiendas Mass';
$descripcion = $descripcion ?? 'Todo lo que necesitas, más cerca de ti y al mejor precio.';
$paginaCssExtra = $paginaCssExtra ?? [];
$navActivo = $navActivo ?? 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($tituloPagina) ?></title>
  <meta name="description" content="<?= e($descripcion) ?>" />
  <meta name="theme-color" content="#00472F" />
  <meta name="color-scheme" content="light" />
  <link rel="icon" href="<?= e(mass_asset('assets/img/logo.svg')) ?>" type="image/svg+xml" />
  <link rel="stylesheet" href="<?= e(mass_asset('assets/css/styles.css')) ?>" />
  <?php foreach ($paginaCssExtra as $css): ?>
    <link rel="stylesheet" href="<?= e(mass_asset($css)) ?>" />
  <?php endforeach; ?>
</head>
<body>
<?php require __DIR__ . '/../components/topbar.php'; ?>
<?php require __DIR__ . '/../components/navbar.php'; ?>
