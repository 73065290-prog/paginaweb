<?php
declare(strict_types=1);

/* bootstrap vía shim */
auth_requerir_admin();
require_once MASS_ROOT . '/includes/admin_operaciones.php';

$tituloAdmin = 'Pedidos & Picking — Mass Admin';
$adminActivo = 'pedidos';
$adminCrumb = 'Pedidos & Picking';

$errores = [];
$filtroRaw = (string) ($_GET['modo'] ?? 'todos');
$codigo = strtoupper(trim((string) ($_GET['codigo'] ?? '')));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filtroRaw = (string) ($_POST['modo'] ?? 'todos');
    $codigo = strtoupper(trim((string) ($_POST['codigo_vista'] ?? '')));
    if (!csrf_validar(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
        $errores[] = 'Sesión inválida. Recarga la página e intenta de nuevo.';
    } elseif ((string) ($_POST['accion'] ?? '') === 'avanzar') {
        $codigoPedido = strtoupper(trim((string) ($_POST['codigo'] ?? '')));
        $resultado = mass_ops_pedido_avanzar($codigoPedido);
        if ($resultado['ok']) {
            $codigo = $codigoPedido;
            flash_set('Pedido ' . $codigoPedido . ' pasó a ' . str_replace('_', ' ', (string) $resultado['estado']) . '.');
            mass_ops_volver('pedidos.php', ['modo' => $filtroRaw, 'codigo' => $codigoPedido]);
        }
        $errores = $resultado['errores'];
    }
}

$filtro = in_array($filtroRaw, ['todos', 'express', 'retiro'], true) ? $filtroRaw : 'todos';
$codigo = preg_replace('/[^A-Z0-9\-]/', '', $codigo) ?? '';
$pedidos = mass_ops_pedidos();
$detalle = $codigo !== '' ? mass_ops_pedido_por_codigo($codigo) : null;
$ruta = $detalle !== null ? mass_ops_pedido_ruta($detalle) : [];

if ($filtro === 'express' || $filtro === 'retiro') {
    $pedidos = array_values(array_filter(
        $pedidos,
        static fn (array $p): bool => ($p['modo'] ?? '') === $filtro
    ));
}

$cfg = mass_ops_config();

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<div class="page-toolbar">
  <div>
    <h1 class="admin-title" style="margin:0">Pedidos / Recolección &amp; Surtido</h1>
    <p class="dash-sub">El picking avanza de preparación a listo para recoger y después a entregado o retirado. SLA de alerta: <?= (int) $cfg['slaPickingMin'] ?> min.</p>
  </div>
  <div class="filters-inline">
    <a href="pedidos.php" class="<?= $filtro === 'todos' && $codigo === '' ? 'is-active' : '' ?>">Todos</a>
    <a href="pedidos.php?modo=express" class="<?= $filtro === 'express' ? 'is-active' : '' ?>">Delivery</a>
    <a href="pedidos.php?modo=retiro" class="<?= $filtro === 'retiro' ? 'is-active' : '' ?>">Retiro tienda</a>
  </div>
</div>

<?php if (($flash ?? '') !== ''): ?>
  <div class="prod-toast prod-toast--page" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>
<?php if ($errores !== []): ?>
  <div class="ops-alert" role="alert">
    <strong>No se pudo actualizar el pedido</strong>
    <ul>
      <?php foreach ($errores as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($codigo !== '' && $detalle === null): ?>
  <p class="ops-alert">No hay un pedido con el código <?= e($codigo) ?>.</p>
<?php endif; ?>

<?php if ($detalle !== null): ?>
  <article class="admin-card ops-detail">
    <div class="card-head">
      <h2>Ruta de recolección #<?= e((string) $detalle['codigo']) ?></h2>
      <a class="btn-mini ghost" href="pedidos.php<?= $filtro === 'todos' ? '' : '?modo=' . e($filtro) ?>">Cerrar detalle</a>
    </div>
    <div class="ops-kv">
      <div><span>Cliente</span> <strong><?= e((string) $detalle['cliente']) ?></strong></div>
      <div><span>Modalidad</span> <strong><?= ($detalle['modo'] ?? '') === 'express' ? 'Delivery Express' : 'Retiro en tienda' ?></strong></div>
      <div><span>Estado</span> <strong><?= e((string) $detalle['estadoLabel']) ?></strong></div>
      <div><span>Total</span> <strong><?= e(dinero((float) ($detalle['total'] ?? 0))) ?></strong></div>
      <div><span>SLA</span> <strong><?= !empty($detalle['fueraSla']) ? 'Fuera de SLA' : ((int) ($detalle['mins'] ?? 0) > 0 ? (int) $detalle['mins'] . ' min' : 'En tiempo') ?></strong></div>
    </div>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>SKU</th>
            <th>Producto</th>
            <th>Ubicar</th>
            <th>Recoger</th>
            <th>Góndola</th>
          </tr>
        </thead>
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
      <form method="post" action="pedidos.php" class="ops-actions" style="margin-top:0.8rem">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="avanzar" />
        <input type="hidden" name="codigo" value="<?= e((string) $detalle['codigo']) ?>" />
        <input type="hidden" name="modo" value="<?= e($filtro) ?>" />
        <input type="hidden" name="codigo_vista" value="<?= e((string) $detalle['codigo']) ?>" />
        <button type="submit" class="btn-mini"><?= e($accionDetalle) ?></button>
      </form>
    <?php endif; ?>
  </article>
<?php endif; ?>

<article class="admin-card">
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Pedido</th>
          <th>Cliente</th>
          <th>Modalidad</th>
          <th>Ítems</th>
          <th>Total</th>
          <th>Estado</th>
          <th>SLA</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$pedidos): ?>
          <tr><td colspan="8"><p class="empty-note">No hay pedidos con ese filtro.</p></td></tr>
        <?php endif; ?>
        <?php foreach ($pedidos as $p): ?>
          <?php $accion = mass_ops_pedido_accion_label($p); ?>
          <tr>
            <td><strong>#<?= e($p['codigo']) ?></strong></td>
            <td><?= e($p['cliente']) ?></td>
            <td>
              <span class="modo-tag <?= e($p['modo']) ?>">
                <?= ($p['modo'] ?? '') === 'express' ? 'Delivery Express' : 'Retiro en tienda' ?>
              </span>
            </td>
            <td><?= (int) $p['items'] ?></td>
            <td><?= e(dinero((float) ($p['total'] ?? 0))) ?></td>
            <td><span class="status-pill <?= e($p['estado']) ?>"><?= e($p['estadoLabel']) ?></span></td>
            <td>
              <?php if (!empty($p['fueraSla'])): ?>
                <span class="status-pill critico">Fuera de SLA</span>
              <?php else: ?>
                <?= (int) ($p['mins'] ?? 0) > 0 ? ((int) $p['mins'] . ' min') : '—' ?>
              <?php endif; ?>
            </td>
            <td>
              <div class="ops-actions">
                <?php if ($accion !== null): ?>
                  <form method="post" class="inline-form" action="pedidos.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="avanzar" />
                    <input type="hidden" name="codigo" value="<?= e((string) $p['codigo']) ?>" />
                    <input type="hidden" name="modo" value="<?= e($filtro) ?>" />
                    <input type="hidden" name="codigo_vista" value="<?= e($codigo) ?>" />
                    <button type="submit" class="btn-mini"><?= e($accion) ?></button>
                  </form>
                <?php endif; ?>
                <a class="btn-mini ghost" href="pedidos.php?<?= e(http_build_query(array_filter([
                    'modo' => $filtro === 'todos' ? null : $filtro,
                    'codigo' => (string) $p['codigo'],
                ], static fn ($v) => $v !== null && $v !== ''))) ?>">Detalle</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</article>

<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>
