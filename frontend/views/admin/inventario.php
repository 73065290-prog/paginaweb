<?php
declare(strict_types=1);

/* bootstrap vía shim */
auth_requerir_admin();

$tituloAdmin = 'Inventario & Góndolas — Mass Admin';
$adminActivo = 'inventario';
$adminCrumb = 'Inventario & Góndolas';

$inventarioService = new InventarioService();

$q = trim((string) ($_GET['q'] ?? ''));
$estado = (string) ($_GET['estado'] ?? 'todos');
$filas = $inventarioService->listarInventarioFiltrado($q, $estado);
$kpis = $inventarioService->obtenerKpis();

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<div class="page-toolbar">
  <h1 class="admin-title" style="margin:0">Inventario &amp; Góndolas</h1>
  <div class="filters-inline">
    <a href="inventario.php" class="<?= $estado === 'todos' ? 'is-active' : '' ?>">Todos</a>
    <a href="inventario.php?estado=ok" class="<?= $estado === 'ok' ? 'is-active' : '' ?>">Stock OK</a>
    <a href="inventario.php?estado=bajo" class="<?= $estado === 'bajo' ? 'is-active' : '' ?>">Bajo</a>
    <a href="inventario.php?estado=critico" class="<?= $estado === 'critico' ? 'is-active' : '' ?>">Crítico</a>
  </div>
</div>

<section class="kpi-grid" style="grid-template-columns: repeat(3, 1fr)">
  <article class="kpi-card">
    <h3>Disponibilidad</h3>
    <p class="kpi-value"><?= e(number_format((float) $kpis['disponibilidad'], 1)) ?>%</p>
  </article>
  <article class="kpi-card" id="mermas">
    <h3>Aprovechamiento</h3>
    <p class="kpi-value"><?= e(dinero((float) $kpis['aprovechamiento'])) ?></p>
  </article>
  <article class="kpi-card">
    <h3>SKUs listados</h3>
    <p class="kpi-value"><?= count($filas) ?></p>
  </article>
</section>

<article class="admin-card">
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Góndola</th>
          <th>Trastienda</th>
          <th>Total</th>
          <th>Estado</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$filas): ?>
          <tr><td colspan="6"><p class="empty-note">Sin resultados para la búsqueda.</p></td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $row): ?>
          <tr>
            <td>
              <div class="prod-cell">
                <img src="<?= e($row['img']) ?>" alt="" />
                <div>
                  <strong><?= e($row['nombre']) ?></strong>
                  <small><?= e($row['sku']) ?> · <?= e($row['marca']) ?></small>
                </div>
              </div>
            </td>
            <td><?= (int) $row['gondola'] ?></td>
            <td><?= (int) $row['trastienda'] ?></td>
            <td><strong><?= (int) $row['gondola'] + (int) $row['trastienda'] ?></strong></td>
            <td>
              <span class="status-pill <?= e($row['estado']) ?>">
                <?= e($inventarioService->etiquetaEstado((string) $row['estado'])) ?>
              </span>
            </td>
            <td>
              <?php if ($row['estado'] !== 'ok'): ?>
                <button type="button" class="btn-mini warn">Reponer góndola</button>
              <?php else: ?>
                <button type="button" class="btn-mini ghost">Ajustar</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</article>

<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>
