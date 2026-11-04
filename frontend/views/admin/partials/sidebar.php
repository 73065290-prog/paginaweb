<?php
declare(strict_types=1);

/** @var string $adminActivo */
$adminActivo = $adminActivo ?? 'dashboard';
require_once MASS_ROOT . '/includes/admin_operaciones.php';
$adminUser = mass_admin_usuario();
$pickingCount = mass_ops_picking_activos();
$kpisSide = mass_admin_kpis();
?>
<aside class="admin-sidebar" aria-label="Menú administrador">
  <a class="admin-brand" href="index.php">
    <img src="<?= e(mass_asset('assets/img/logo.svg')) ?>" alt="Mass Admin" width="140" height="34" />
    <small>TIENDAS MASS · <?= e($adminUser['panelVersion'] ?? 'web panel v1.2') ?></small>
  </a>

  <div class="admin-sede-chip" title="<?= e($adminUser['sucursal']) ?>">
    📍 <?= e($adminUser['sucursal']) ?>
  </div>

  <nav class="admin-nav">
    <a href="index.php" class="<?= $adminActivo === 'dashboard' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">▣</span>
      Dashboard
    </a>
    <a href="productos.php" class="<?= $adminActivo === 'productos' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">▦</span>
      Administración de Productos
    </a>
    <a href="inventario.php" class="<?= $adminActivo === 'inventario' || $adminActivo === 'gondolas' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">▤</span>
      Control de Inventario &amp; Góndolas
      <?php if ((int) $kpisSide['stockCritico'] > 0): ?>
        <span class="nav-badge"><?= (int) $kpisSide['stockCritico'] ?></span>
      <?php endif; ?>
    </a>
    <a href="pedidos.php" class="<?= $adminActivo === 'pedidos' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">◈</span>
      Pedidos / Recolección &amp; Surtido
      <?php if ($pickingCount > 0): ?>
        <span class="nav-badge"><?= (int) $pickingCount ?></span>
      <?php endif; ?>
    </a>
    <a href="conteos.php" class="<?= $adminActivo === 'conteos' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">↻</span>
      Conteos Cíclicos &amp; Auditorías
    </a>
    <a href="mermas.php" class="<?= $adminActivo === 'mermas' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">⚠</span>
      Mermas y Caducidades
    </a>
    <a href="reportes.php" class="<?= $adminActivo === 'reportes' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">◉</span>
      Reportes y Analítica
    </a>
    <a href="configuracion.php" class="<?= $adminActivo === 'config' ? 'is-active' : '' ?>">
      <span class="nav-ico" aria-hidden="true">⚙</span>
      Configuración del Sistema
    </a>
  </nav>

  <div class="admin-user">
    <img src="<?= e($adminUser['avatar']) ?>" alt="" />
    <div>
      <strong><?= e($adminUser['nombre']) ?></strong>
      <span><?= e($adminUser['rol']) ?></span>
    </div>
  </div>
  <a class="admin-logout-btn" href="logout.php">Cerrar sesión</a>
</aside>
