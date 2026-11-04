<?php
declare(strict_types=1);

/* bootstrap vía shim */
auth_requerir_admin();
require_once MASS_ROOT . '/includes/admin_operaciones.php';

$tituloAdmin = 'Pedidos & Picking — Mass Admin';
$adminActivo = 'pedidos';
$adminCrumb = 'Pedidos & Picking';

$errores = [];
$modo = (string) ($_GET['modo'] ?? 'todos');
$estado = (string) ($_GET['estado'] ?? 'todos');
$q = trim((string) ($_GET['q'] ?? ''));
$pagina = (string) ($_GET['pagina'] ?? '');
$codigo = strtoupper(trim((string) ($_GET['codigo'] ?? '')));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modo = (string) ($_POST['modo'] ?? 'todos');
    $estado = (string) ($_POST['estado'] ?? 'todos');
    $q = trim((string) ($_POST['q'] ?? ''));
    $pagina = (string) ($_POST['pagina'] ?? '');
    $codigo = strtoupper(trim((string) ($_POST['codigo_vista'] ?? $codigo)));
    $volver = ['modo' => $modo, 'estado' => $estado, 'q' => $q, 'pagina' => $pagina, 'codigo' => $codigo];
    if (!csrf_validar(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
        $errores[] = 'Sesión inválida. Recarga la página e intenta de nuevo.';
    } else {
        $accion = (string) ($_POST['accion'] ?? '');
        if ($accion === 'avanzar') {
            $codigoPedido = strtoupper(trim((string) ($_POST['codigo'] ?? '')));
            $resultado = mass_ops_pedido_avanzar($codigoPedido);
            if ($resultado['ok']) {
                $volver['codigo'] = $codigoPedido;
                flash_set('Pedido ' . $codigoPedido . ' pasó a ' . str_replace('_', ' ', (string) $resultado['estado']) . '.');
                mass_ops_volver('pedidos.php', $volver);
            }
            $errores = $resultado['errores'];
        }
        if ($accion === 'meta') {
            $codigoPedido = strtoupper(trim((string) ($_POST['codigo'] ?? '')));
            $resultado = mass_ops_pedido_actualizar($codigoPedido, (string) ($_POST['prioridad'] ?? ''), (string) ($_POST['nota'] ?? ''));
            if ($resultado['ok']) {
                $volver['codigo'] = $codigoPedido;
                flash_set('Prioridad y nota guardadas en ' . $codigoPedido . '.');
                mass_ops_volver('pedidos.php', $volver);
            }
            $errores = $resultado['errores'];
        }
    }
}

$codigo = preg_replace('/[^A-Z0-9\-]/', '', $codigo) ?? '';
$vista = mass_ops_pedidos_vista([
    'modo' => $modo,
    'estado' => $estado,
    'q' => $q,
    'pagina' => $pagina,
]);
$opsQuery = ['modo' => $vista['modo'], 'estado' => $vista['estado'], 'q' => $vista['q']];
$detalle = $codigo !== '' ? mass_ops_pedido_por_codigo($codigo) : null;
$ruta = $detalle !== null ? mass_ops_pedido_ruta($detalle) : [];
$encabezados = ['Pedido', 'Cliente', 'Modalidad', 'Ítems', 'Total', 'Estado', 'Prioridad', 'SLA'];
$planas = [];
foreach ($vista['exportar'] as $pedido) {
    $planas[] = [
        (string) $pedido['codigo'],
        (string) $pedido['cliente'],
        (string) $pedido['modo'],
        (int) $pedido['items'],
        number_format((float) ($pedido['total'] ?? 0), 2, '.', ''),
        (string) $pedido['estadoLabel'],
        (string) ($pedido['prioridad'] ?? ''),
        !empty($pedido['fueraSla']) ? 'fuera' : (string) ($pedido['mins'] ?? 0),
    ];
}
$export = (string) ($_GET['export'] ?? '');
if ($export === 'csv') {
    mass_ops_descargar('pedidos-picking.csv', 'text/csv; charset=UTF-8', mass_ops_csv($encabezados, $planas));
}
if ($export === 'xls') {
    mass_ops_descargar('pedidos-picking.xls', 'application/vnd.ms-excel; charset=UTF-8', mass_ops_xls('Pedidos y picking', $encabezados, $planas));
}
if ($export === 'pdf') {
    echo mass_ops_documento_html('Pedidos y recolección', 'Corte ' . mass_ops_hoy(), $encabezados, $planas);
    exit;
}

$cfg = mass_ops_config();
$embudo = $vista['embudo'];

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<div class="ops-suite">
<div class="page-toolbar">
  <div>
    <h1 class="admin-title" style="margin:0">Pedidos / Recolección &amp; Surtido</h1>
    <p class="dash-sub">Arrastra un pedido a la columna siguiente o usa el botón. SLA de alerta: <?= (int) $cfg['slaPickingMin'] ?> min.</p>
  </div>
  <?php $opsExportTitulo = 'pedidos'; require MASS_ROOT . '/frontend/views/admin/partials/ops-export.php'; ?>
</div>

<section class="kpi-grid" style="grid-template-columns: repeat(4, 1fr)" aria-label="Embudo de picking">
  <article class="kpi-card"><h3>Preparación</h3><p class="kpi-value"><?= (int) $embudo['preparacion'] ?></p></article>
  <article class="kpi-card"><h3>Por recoger</h3><p class="kpi-value"><?= (int) $embudo['por_recoger'] ?></p></article>
  <article class="kpi-card"><h3>Cerrados</h3><p class="kpi-value"><?= (int) $embudo['cerrados'] ?></p></article>
  <article class="kpi-card <?= (int) $embudo['fuera'] > 0 ? 'is-warn' : '' ?>"><h3>Fuera de SLA</h3><p class="kpi-value"><?= (int) $embudo['fuera'] ?></p></article>
</section>

<?php if (($flash ?? '') !== ''): ?>
  <div class="prod-toast prod-toast--page" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>
<?php if ($errores !== []): ?>
  <div class="ops-alert" role="alert"><strong>No se pudo actualizar el pedido</strong><ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form class="ops-filters" method="get" action="pedidos.php">
  <label>Buscar <input type="search" name="q" value="<?= e((string) $vista['q']) ?>" placeholder="Código, cliente o nota" /></label>
  <label>Modalidad
    <select name="modo">
      <option value="todos" <?= $vista['modo'] === 'todos' ? 'selected' : '' ?>>Todas</option>
      <option value="express" <?= $vista['modo'] === 'express' ? 'selected' : '' ?>>Delivery</option>
      <option value="retiro" <?= $vista['modo'] === 'retiro' ? 'selected' : '' ?>>Retiro</option>
    </select>
  </label>
  <label>Estado
    <select name="estado">
      <?php foreach (['todos' => 'Todos', 'activos' => 'Activos', 'preparacion' => 'Preparación', 'por_recoger' => 'Por recoger', 'entregado' => 'Entregado', 'retirado' => 'Retirado'] as $valor => $texto): ?>
        <option value="<?= e($valor) ?>" <?= $vista['estado'] === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit" class="btn-mini">Aplicar</button>
</form>

<form id="opsDragForm" method="post" action="pedidos.php" hidden>
  <?= csrf_field() ?>
  <input type="hidden" name="accion" value="avanzar" />
  <input type="hidden" name="codigo" value="" />
  <input type="hidden" name="modo" value="<?= e((string) $vista['modo']) ?>" />
  <input type="hidden" name="estado" value="<?= e((string) $vista['estado']) ?>" />
  <input type="hidden" name="q" value="<?= e((string) $vista['q']) ?>" />
  <input type="hidden" name="codigo_vista" value="<?= e($codigo) ?>" />
  <button type="submit">Avanzar</button>
</form>

<div class="ops-board" aria-label="Tablero de recolección">
  <?php
    $columnas = [
        'preparacion' => 'Preparación',
        'por_recoger' => 'Por recoger',
        'cerrado' => 'Cerrados',
    ];
  ?>
  <?php foreach ($columnas as $colId => $colNombre): ?>
    <section class="ops-col" data-ops-col="<?= e($colId) ?>">
      <h2><?= e($colNombre) ?></h2>
      <?php foreach ($vista['tablero'] as $card): ?>
        <?php
          $enColumna = ($colId === 'cerrado' && !in_array((string) $card['estado'], ['preparacion', 'por_recoger'], true))
              || (string) $card['estado'] === $colId;
        ?>
        <?php if (!$enColumna) { continue; } ?>
        <article class="ops-card" draggable="true" data-ops-card data-codigo="<?= e((string) $card['codigo']) ?>" data-estado="<?= e((string) $card['estado']) ?>">
          <strong>#<?= e((string) $card['codigo']) ?></strong>
          <span><?= e((string) $card['cliente']) ?></span>
          <small class="status-pill <?= e((string) ($card['prioridad'] ?? 'media')) ?>"><?= e((string) ($card['prioridad'] ?? 'media')) ?></small>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endforeach; ?>
</div>

<?php if ($codigo !== '' && $detalle === null): ?>
  <p class="ops-alert">No hay un pedido con el código <?= e($codigo) ?>.</p>
<?php endif; ?>

<?php if ($detalle !== null): ?>
  <article class="admin-card ops-detail">
    <div class="card-head">
      <h2>Ruta de recolección #<?= e((string) $detalle['codigo']) ?></h2>
      <a class="ops-btn" href="pedidos.php?<?= e(mass_ops_query($opsQuery)) ?>">Cerrar detalle</a>
    </div>
    <div class="ops-kv">
      <div><span>Cliente</span> <strong><?= e((string) $detalle['cliente']) ?></strong></div>
      <div><span>Modalidad</span> <strong><?= ($detalle['modo'] ?? '') === 'express' ? 'Delivery Express' : 'Retiro en tienda' ?></strong></div>
      <div><span>Estado</span> <strong><?= e((string) $detalle['estadoLabel']) ?></strong></div>
      <div><span>Total</span> <strong><?= e(dinero((float) ($detalle['total'] ?? 0))) ?></strong></div>
      <div><span>SLA</span> <strong><?= !empty($detalle['fueraSla']) ? 'Fuera de SLA' : ((int) ($detalle['mins'] ?? 0) > 0 ? (int) $detalle['mins'] . ' min' : 'En tiempo') ?></strong></div>
    </div>
    <form method="post" action="pedidos.php" class="ops-form" data-ops-form>
      <?= csrf_field() ?>
      <input type="hidden" name="accion" value="meta" />
      <input type="hidden" name="codigo" value="<?= e((string) $detalle['codigo']) ?>" />
      <input type="hidden" name="codigo_vista" value="<?= e((string) $detalle['codigo']) ?>" />
      <input type="hidden" name="modo" value="<?= e((string) $vista['modo']) ?>" />
      <input type="hidden" name="estado" value="<?= e((string) $vista['estado']) ?>" />
      <input type="hidden" name="q" value="<?= e((string) $vista['q']) ?>" />
      <label>Prioridad
        <select name="prioridad">
          <?php foreach (['alta', 'media', 'baja'] as $prioridad): ?>
            <option value="<?= e($prioridad) ?>" <?= ($detalle['prioridad'] ?? '') === $prioridad ? 'selected' : '' ?>><?= e(ucfirst($prioridad)) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Nota de surtido
        <input type="text" name="nota" maxlength="180" value="<?= e((string) ($detalle['nota'] ?? '')) ?>" />
      </label>
      <button type="submit" class="btn-mini">Guardar nota</button>
    </form>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>SKU</th><th>Producto</th><th>Ubicar</th><th>Recoger</th><th>Góndola</th></tr></thead>
        <tbody>
          <?php foreach ($ruta as $linea): ?>
            <tr>
              <td><strong><?= e((string) $linea['sku']) ?></strong></td>
              <td><?= e((string) $linea['nombre']) ?></td>
              <td><?= e((string) $linea['ubicacion']) ?></td>
              <td><?= (int) $linea['cantidad'] ?></td>
              <td><?= (int) $linea['gondola'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php $accionDetalle = mass_ops_pedido_accion_label($detalle); ?>
    <?php if ($accionDetalle !== null): ?>
      <form method="post" action="pedidos.php" class="ops-actions" data-ops-form>
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="avanzar" />
        <input type="hidden" name="codigo" value="<?= e((string) $detalle['codigo']) ?>" />
        <input type="hidden" name="modo" value="<?= e((string) $vista['modo']) ?>" />
        <input type="hidden" name="estado" value="<?= e((string) $vista['estado']) ?>" />
        <input type="hidden" name="q" value="<?= e((string) $vista['q']) ?>" />
        <input type="hidden" name="codigo_vista" value="<?= e((string) $detalle['codigo']) ?>" />
        <button type="submit" class="btn-mini"><?= e($accionDetalle) ?></button>
      </form>
    <?php endif; ?>
  </article>
<?php endif; ?>

<article class="admin-card">
  <label class="ops-live-label">Filtrar esta página
    <input type="search" data-ops-live="[data-ops-table]" placeholder="Texto visible en la tabla" />
  </label>
  <div class="table-wrap">
    <table class="admin-table" data-ops-table>
      <thead>
        <tr>
          <th>Pedido</th><th>Cliente</th><th>Modalidad</th><th>Ítems</th><th>Total</th><th>Estado</th><th>SLA</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$vista['filas']): ?>
          <tr><td colspan="8"><p class="empty-note">No hay pedidos con ese filtro.</p></td></tr>
        <?php endif; ?>
        <?php foreach ($vista['filas'] as $p): ?>
          <?php $accion = mass_ops_pedido_accion_label($p); ?>
          <tr data-ops-row="<?= e(mb_strtolower((string) $p['codigo'] . ' ' . (string) $p['cliente'] . ' ' . (string) ($p['nota'] ?? ''))) ?>">
            <td><strong>#<?= e((string) $p['codigo']) ?></strong></td>
            <td><?= e((string) $p['cliente']) ?><?php if (($p['nota'] ?? '') !== ''): ?><small class="ean"><?= e((string) $p['nota']) ?></small><?php endif; ?></td>
            <td><span class="modo-tag <?= e((string) $p['modo']) ?>"><?= ($p['modo'] ?? '') === 'express' ? 'Delivery Express' : 'Retiro en tienda' ?></span></td>
            <td><?= (int) $p['items'] ?></td>
            <td><?= e(dinero((float) ($p['total'] ?? 0))) ?></td>
            <td><span class="status-pill <?= e((string) $p['estado']) ?>"><?= e((string) $p['estadoLabel']) ?></span></td>
            <td><?php if (!empty($p['fueraSla'])): ?><span class="status-pill critico">Fuera de SLA</span><?php else: ?><?= (int) ($p['mins'] ?? 0) > 0 ? ((int) $p['mins'] . ' min') : '—' ?><?php endif; ?></td>
            <td>
              <div class="ops-actions">
                <?php if ($accion !== null): ?>
                  <form method="post" class="inline-form" action="pedidos.php" data-ops-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="avanzar" />
                    <input type="hidden" name="codigo" value="<?= e((string) $p['codigo']) ?>" />
                    <input type="hidden" name="modo" value="<?= e((string) $vista['modo']) ?>" />
                    <input type="hidden" name="estado" value="<?= e((string) $vista['estado']) ?>" />
                    <input type="hidden" name="q" value="<?= e((string) $vista['q']) ?>" />
                    <input type="hidden" name="codigo_vista" value="<?= e($codigo) ?>" />
                    <button type="submit" class="btn-mini"><?= e($accion) ?></button>
                  </form>
                <?php endif; ?>
                <a class="ops-btn" href="pedidos.php?<?= e(mass_ops_query($opsQuery + ['codigo' => (string) $p['codigo']])) ?>">Detalle</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php $opsPager = $vista['pager']; require MASS_ROOT . '/frontend/views/admin/partials/ops-pager.php'; ?>
</article>
</div>
<script src="<?= e(mass_asset('assets/js/admin-ops.js')) ?>" defer></script>
<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>
