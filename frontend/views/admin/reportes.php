<?php
declare(strict_types=1);

/* bootstrap vía shim */
auth_requerir_admin();
require_once MASS_ROOT . '/includes/admin_operaciones.php';

$tituloAdmin = 'Reportes y analítica — Mass Admin';
$adminActivo = 'reportes';
$adminCrumb = 'Reportes y Analítica';

$periodo = (string) ($_GET['periodo'] ?? 'hoy');
if ((string) ($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="reporte-sgc-' . preg_replace('/[^a-z0-9]/', '', $periodo) . '.csv"');
    echo mass_ops_reporte_csv($periodo);
    exit;
}

$reporte = mass_ops_reporte($periodo);
$maxVenta = 0.0;
foreach ($reporte['horaria'] as $punto) {
    $maxVenta = max($maxVenta, (float) $punto['venta'], (float) $punto['objetivo']);
}

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<div class="page-toolbar">
  <div>
    <h1 class="admin-title" style="margin:0">Reportes y analítica</h1>
    <p class="dash-sub">Ventas del periodo demo y el estado operativo en vivo de góndola, conteos, mermas y picking.</p>
  </div>
  <div class="filters-inline">
    <a href="reportes.php?periodo=hoy" class="<?= $reporte['periodo'] === 'hoy' ? 'is-active' : '' ?>">Hoy</a>
    <a href="reportes.php?periodo=7d" class="<?= $reporte['periodo'] === '7d' ? 'is-active' : '' ?>">7 días</a>
    <a href="reportes.php?periodo=30d" class="<?= $reporte['periodo'] === '30d' ? 'is-active' : '' ?>">30 días</a>
    <a href="reportes.php?<?= e(http_build_query(['periodo' => $reporte['periodo'], 'export' => 'csv'])) ?>">Exportar CSV</a>
  </div>
</div>

<section class="kpi-grid" style="grid-template-columns: repeat(4, 1fr)" aria-label="Indicadores del reporte">
  <article class="kpi-card">
    <h3>Ventas · <?= e((string) $reporte['etiqueta']) ?></h3>
    <p class="kpi-value"><?= e(dinero((float) $reporte['venta'])) ?></p>
  </article>
  <article class="kpi-card">
    <h3>Disponibilidad</h3>
    <p class="kpi-value"><?= e(number_format((float) $reporte['disponibilidad'], 1)) ?>%</p>
  </article>
  <article class="kpi-card">
    <h3>Merma registrada</h3>
    <p class="kpi-value"><?= e(dinero((float) $reporte['mermaMonto'])) ?></p>
    <p class="kpi-meta"><?= (int) $reporte['mermaUnidades'] ?> unidades</p>
  </article>
  <article class="kpi-card <?= (int) $reporte['fueraSla'] > 0 ? 'is-warn' : '' ?>">
    <h3>Picking fuera de SLA</h3>
    <p class="kpi-value"><?= (int) $reporte['fueraSla'] ?></p>
    <p class="kpi-meta"><?= (int) $reporte['pedidosActivos'] ?> pedidos activos</p>
  </article>
</section>

<section class="charts-row charts-row--dash">
  <article class="admin-card">
    <div class="card-head">
      <h2>Ventas vs proyección</h2>
      <div class="chart-legend-inline">
        <span><i class="lg-real"></i> Ventas</span>
        <span><i class="lg-proy"></i> Proyección</span>
      </div>
    </div>
    <div class="bar-chart bar-chart--dual" role="img" aria-label="Ventas contra proyección del periodo">
      <?php foreach ($reporte['horaria'] as $punto): ?>
        <?php
          $pct = $maxVenta > 0 ? ((float) $punto['venta'] / $maxVenta) * 100 : 0;
          $goalPct = $maxVenta > 0 ? ((float) $punto['objetivo'] / $maxVenta) * 100 : 0;
          $bajo = (float) $punto['venta'] < (float) $punto['objetivo'];
        ?>
        <div class="bar-col">
          <div class="bar-track bar-track--dual">
            <div class="bar-fill <?= $bajo ? 'is-bajo' : '' ?>" style="height: <?= $pct ?>%"></div>
            <div class="bar-proy" style="height: <?= $goalPct ?>%"></div>
          </div>
          <span><?= e((string) $punto['hora']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="admin-card">
    <h2>Operación en vivo</h2>
    <ul class="top-rotacion">
      <li>
        <span class="top-rank"><?= (int) $reporte['stock']['critico'] ?></span>
        <div>
          <strong>SKUs críticos</strong>
          <small><?= (int) $reporte['stock']['bajo'] ?> en stock bajo · <?= (int) $reporte['stock']['ok'] ?> en stock OK</small>
        </div>
        <a href="inventario.php?estado=critico">Ver</a>
      </li>
      <li>
        <span class="top-rank"><?= (int) $reporte['conteosAbiertos'] ?></span>
        <div>
          <strong>Conteos abiertos</strong>
          <small>Pendientes o en curso</small>
        </div>
        <a href="conteos.php">Ver</a>
      </li>
      <li>
        <span class="top-rank"><?= (int) $reporte['pedidosActivos'] ?></span>
        <div>
          <strong>Recolección activa</strong>
          <small><?= (int) $reporte['fueraSla'] ?> fuera de SLA</small>
        </div>
        <a href="pedidos.php">Ver</a>
      </li>
    </ul>
  </article>
</section>

<section class="mid-row">
  <article class="admin-card">
    <h2>Clasificación ABC</h2>
    <ul class="donut-legend">
      <?php foreach ($reporte['canales'] as $canal): ?>
        <li>
          <i style="background: <?= e((string) $canal['color']) ?>"></i>
          <?= e((string) $canal['nombre']) ?>
          <em><?= (int) ($canal['skus'] ?? 0) ?> · <?= (int) $canal['pct'] ?>%</em>
        </li>
      <?php endforeach; ?>
    </ul>
  </article>
  <article class="admin-card">
    <h2>Top de rotación</h2>
    <ol class="top-rotacion">
      <?php foreach ($reporte['top'] as $i => $top): ?>
        <li>
          <span class="top-rank"><?= $i + 1 ?></span>
          <div>
            <strong><?= e((string) $top['nombre']) ?></strong>
            <small><?= (int) $top['unidades'] ?> und · ABC <?= e((string) $top['abc']) ?></small>
          </div>
          <em><?= e(dinero((float) $top['monto'])) ?></em>
        </li>
      <?php endforeach; ?>
    </ol>
  </article>
</section>

<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>
