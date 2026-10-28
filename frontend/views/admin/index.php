<?php
declare(strict_types=1);

/* bootstrap vía shim */
auth_requerir_admin();

$tituloAdmin = 'Dashboard — Mass Admin';
$adminActivo = 'dashboard';
$adminCrumb = 'Dashboard General de Ventas e Inventario';

$kpis = mass_admin_kpis();
$horaria = mass_admin_venta_horaria();
$abc = mass_admin_canales();
$stockGlobal = mass_admin_stock_global();
$topRotacion = mass_admin_top_rotacion();
$alertas = mass_admin_alertas_gondola();
$abcTotal = mass_admin_abc_total();

$filtroAlerta = (string) ($_GET['alerta'] ?? 'todos');
if (!in_array($filtroAlerta, ['todos', 'agotados', 'critico', 'vencer'], true)) {
    $filtroAlerta = 'todos';
}
$alertasFiltradas = array_values(array_filter(
    $alertas,
    static function (array $a) use ($filtroAlerta): bool {
        if ($filtroAlerta === 'todos') {
            return true;
        }
        return ($a['filtro'] ?? '') === $filtroAlerta;
    }
));

$maxVenta = max(array_column($horaria, 'venta'));
$donutParts = [];
$acc = 0;
foreach ($abc as $c) {
    $donutParts[] = $c['color'] . ' ' . $acc . '% ' . ($acc + $c['pct']) . '%';
    $acc += $c['pct'];
}
$donutBg = 'conic-gradient(' . implode(', ', $donutParts) . ')';

$stockTotal = array_sum(array_column($stockGlobal, 'cantidad'));

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<header class="dash-head">
  <div>
    <h1 class="admin-title">Dashboard General de Ventas e Inventario</h1>
    <p class="dash-sub">Monitoreo en tiempo real de los indicadores clave para la sucursal y gestión inteligente de inventarios.</p>
  </div>
</header>

<section class="kpi-grid kpi-grid--8" aria-label="Indicadores del día">
  <article class="kpi-card">
    <h3>Ventas del día</h3>
    <p class="kpi-value"><?= e(dinero((float) $kpis['ventaDia'])) ?></p>
    <span class="kpi-trend">↑ +<?= e(number_format((float) $kpis['ventaTrend'], 1)) ?>% vs ayer</span>
  </article>

  <article class="kpi-card">
    <h3>Pedidos en curso</h3>
    <p class="kpi-value"><?= (int) $kpis['pedidosCurso'] ?></p>
    <p class="kpi-meta"><?= (int) $kpis['pedidosPicking'] ?> picking · <?= (int) $kpis['pedidosRuta'] ?> en ruta · <?= (int) $kpis['pedidosListos'] ?> listos</p>
  </article>

  <article class="kpi-card is-warn">
    <h3>Stock crítico</h3>
    <p class="kpi-value"><?= (int) $kpis['stockCritico'] ?> <small>SKUs</small></p>
    <p class="kpi-meta">Alerta alta · reponer YA</p>
  </article>

  <article class="kpi-card is-danger">
    <h3>Quiebres góndola</h3>
    <p class="kpi-value"><?= (int) $kpis['quiebresGondola'] ?> <small>SKUs</small></p>
    <p class="kpi-meta">Atención inmediata</p>
  </article>

  <article class="kpi-card kpi-card--mini">
    <h3>Sugerencia OC</h3>
    <p class="kpi-value"><?= (int) $kpis['sugerenciaOc'] ?></p>
    <p class="kpi-meta">Órdenes de compra</p>
  </article>

  <article class="kpi-card kpi-card--mini">
    <h3>Conteos cíclicos</h3>
    <p class="kpi-value"><?= (int) $kpis['conteosPasillos'] ?></p>
    <p class="kpi-meta">Pasillos pendientes</p>
  </article>

  <article class="kpi-card kpi-card--mini">
    <h3>Mermas / desmedro</h3>
    <p class="kpi-value"><?= e(number_format((float) $kpis['mermaPct'], 2)) ?>%</p>
    <p class="kpi-meta"><?= e(dinero((float) $kpis['mermaMonto'])) ?></p>
  </article>

  <article class="kpi-card kpi-card--mini">
    <h3>Inventario total</h3>
    <p class="kpi-value">S/ <?= e(number_format((float) $kpis['inventarioTotal'] / 1000000, 2)) ?>M</p>
    <p class="kpi-meta">Valuación total</p>
  </article>
</section>

<section class="charts-row charts-row--dash" id="ventas">
  <article class="admin-card">
    <div class="card-head">
      <h2>Evolución de ventas vs proyección</h2>
      <div class="chart-legend-inline">
        <span><i class="lg-real"></i> Ventas reales</span>
        <span><i class="lg-proy"></i> Proyección</span>
      </div>
    </div>
    <div class="bar-chart bar-chart--dual" role="img" aria-label="Gráfico de ventas vs proyección">
      <?php foreach ($horaria as $h): ?>
        <?php
          $pct = $maxVenta > 0 ? ((float) $h['venta'] / $maxVenta) * 100 : 0;
          $goalPct = $maxVenta > 0 ? ((float) $h['objetivo'] / $maxVenta) * 100 : 0;
          $bajo = (float) $h['venta'] < (float) $h['objetivo'];
        ?>
        <div class="bar-col">
          <div class="bar-track bar-track--dual">
            <div class="bar-fill <?= $bajo ? 'is-bajo' : '' ?>" style="height: <?= $pct ?>%" title="Real"></div>
            <div class="bar-proy" style="height: <?= $goalPct ?>%" title="Proyección"></div>
          </div>
          <span><?= e($h['hora']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="admin-card">
    <h2>Distribución por clasificación ABC</h2>
    <div class="donut-wrap">
      <div class="donut" style="background: <?= e($donutBg) ?>">
        <strong><?= number_format($abcTotal) ?></strong>
        <em>SKUs</em>
      </div>
      <ul class="donut-legend">
        <?php foreach ($abc as $c): ?>
          <li>
            <i style="background: <?= e($c['color']) ?>"></i>
            <?= e($c['nombre']) ?>
            <em><?= (int) ($c['skus'] ?? 0) ?> · <?= (int) $c['pct'] ?>%</em>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </article>
</section>

<section class="mid-row">
  <article class="admin-card">
    <h2>Comparación de estado de stock global</h2>
    <div class="stock-stack" role="img" aria-label="Estado de stock">
      <?php foreach ($stockGlobal as $s): ?>
        <?php $w = $stockTotal > 0 ? ((int) $s['cantidad'] / $stockTotal) * 100 : 0; ?>
        <div class="stock-seg" style="width: <?= $w ?>%; background: <?= e($s['color']) ?>" title="<?= e($s['label']) ?>"></div>
      <?php endforeach; ?>
    </div>
    <ul class="stock-legend">
      <?php foreach ($stockGlobal as $s): ?>
        <li>
          <strong style="color: <?= e($s['color']) ?>"><?= number_format((int) $s['cantidad']) ?></strong>
          <span><?= e($s['label']) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </article>

  <article class="admin-card">
    <h2>Top 5 productos con mayor rotación</h2>
    <ol class="top-rotacion">
      <?php foreach ($topRotacion as $i => $t): ?>
        <li>
          <span class="top-rank"><?= $i + 1 ?></span>
          <div>
            <strong><?= e($t['nombre']) ?></strong>
            <small><?= (int) $t['unidades'] ?> und · ABC <?= e($t['abc']) ?></small>
          </div>
          <em><?= e(dinero((float) $t['monto'])) ?></em>
        </li>
      <?php endforeach; ?>
    </ol>
  </article>
</section>

<section class="admin-card alerts-card">
  <div class="card-head">
    <h2>Alertas prioritarias de góndola y almacén</h2>
    <div class="filters-inline" role="tablist">
      <a href="index.php?alerta=todos" class="<?= $filtroAlerta === 'todos' ? 'is-active' : '' ?>">Todos</a>
      <a href="index.php?alerta=agotados" class="<?= $filtroAlerta === 'agotados' ? 'is-active' : '' ?>">Agotados</a>
      <a href="index.php?alerta=critico" class="<?= $filtroAlerta === 'critico' ? 'is-active' : '' ?>">Stock crítico</a>
      <a href="index.php?alerta=vencer" class="<?= $filtroAlerta === 'vencer' ? 'is-active' : '' ?>">Próximos a vencer</a>
    </div>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>SKU / EAN</th>
          <th>Producto &amp; categoría</th>
          <th>Stock piso / almacén</th>
          <th>ABC</th>
          <th>Ubicación</th>
          <th>Estado</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($alertasFiltradas === []): ?>
          <tr><td colspan="7" class="empty-note">Sin alertas para este filtro.</td></tr>
        <?php endif; ?>
        <?php foreach ($alertasFiltradas as $row): ?>
          <?php
            $nivel = (int) $row['piso'] + (int) $row['almacen'];
            $min = max(1, (int) $row['minimo']);
            $pctNivel = min(100, ($nivel / $min) * 100);
          ?>
          <tr>
            <td>
              <strong><?= e($row['sku']) ?></strong>
              <small class="ean"><?= e($row['ean']) ?></small>
            </td>
            <td>
              <div class="prod-cell">
                <img src="<?= e($row['img']) ?>" alt="" />
                <div>
                  <strong><?= e($row['nombre']) ?></strong>
                  <small><?= e((string) $row['categoria']) ?></small>
                </div>
              </div>
            </td>
            <td>
              <div class="stock-mini">
                <div class="stock-mini-bar"><i style="width: <?= $pctNivel ?>%" class="<?= e($row['estado']) ?>"></i></div>
                <span><?= (int) $row['piso'] ?> / <?= (int) $row['almacen'] ?> · mín <?= (int) $row['minimo'] ?></span>
              </div>
            </td>
            <td><span class="abc-badge"><?= e($row['abc']) ?></span></td>
            <td><?= e($row['ubicacion']) ?></td>
            <td>
              <span class="status-pill <?= e($row['estado']) ?>"><?= e($row['estadoLabel']) ?></span>
            </td>
            <td>
              <a class="btn-mini <?= $row['estado'] === 'ok' ? 'ghost' : 'warn' ?>" href="inventario.php?q=<?= urlencode($row['sku']) ?>">
                <?= e($row['accion']) ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>

