<?php
declare(strict_types=1);

$provActivo = $provActivo ?? 'dashboard';
$perfil = mass_proveedor_perfil();
$kpis = mass_proveedor_kpis();
?>
<aside class="admin-sidebar proveedor-sidebar" aria-label="Menú proveedor">
  <a class="admin-brand" href="index.php">
    <img src="<?= e(mass_asset('assets/img/logo.svg')) ?>" alt="Mass Proveedores" width="140" height="34" />
    <small>PORTAL PROVEEDORES · B2B</small>
  </a>

  <div class="admin-sede-chip" title="<?= e($perfil['empresa']) ?>">
    🏭 <?= e($perfil['empresa']) ?>
  </div>

  <nav class="admin-nav">
    <a href="index.php" class="<?= $provActivo === 'dashboard' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">▣</span>
      Dashboard
    </a>
    <a href="ordenes.php" class="<?= $provActivo === 'ordenes' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">◈</span>
      Órdenes de compra
      <?php if ((int) $kpis['ocPendientes'] > 0): ?>
        <span class="nav-badge"><?= (int) $kpis['ocPendientes'] ?></span>
      <?php endif; ?>
    </a>
    <a href="catalogo.php" class="<?= $provActivo === 'catalogo' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">▦</span>
      Mi catálogo SKU
    </a>
    <a href="entregas.php" class="<?= $provActivo === 'entregas' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">🚚</span>
      Entregas a tienda
      <?php if ((int) $kpis['entregasRuta'] > 0): ?>
        <span class="nav-badge"><?= (int) $kpis['entregasRuta'] ?></span>
      <?php endif; ?>
    </a>
  </nav>

  <div class="admin-user">
    <img src="<?= e($perfil['avatar']) ?>" alt="" />
    <div>
      <strong><?= e($perfil['nombre']) ?></strong>
      <span><?= e($perfil['cargo']) ?></span>
    </div>
  </div>
  <a class="admin-logout-btn" href="logout.php">Cerrar sesión</a>
</aside>
