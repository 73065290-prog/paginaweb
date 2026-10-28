<?php
declare(strict_types=1);

auth_requerir_proveedor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validar(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
        flash_set('Sesión inválida. Recarga e intenta de nuevo.');
        redirect('ordenes.php');
    }
    $id = (string) ($_POST['oc_id'] ?? '');
    $accion = (string) ($_POST['accion'] ?? '');
    $mapa = [
        'confirmar' => 'confirmada',
        'rechazar' => 'rechazada',
        'despachar' => 'en_ruta',
        'entregar' => 'entregada',
    ];
    if (!isset($mapa[$accion])) {
        flash_set('Acción no válida');
        redirect('ordenes.php');
    }
    $r = proveedor_orden_cambiar_estado($id, $mapa[$accion]);
    flash_set($r['ok'] ? ('OC ' . $id . ' → ' . $mapa[$accion]) : implode(' · ', $r['errores']));
    $volverEstado = (string) ($_POST['filtro_estado'] ?? 'todos');
    redirect('ordenes.php?estado=' . urlencode($volverEstado !== '' ? $volverEstado : 'todos'));
}

$filtro = (string) ($_GET['estado'] ?? 'todos');
$validos = ['todos', 'pendiente', 'confirmada', 'en_ruta', 'entregada', 'rechazada'];
if (!in_array($filtro, $validos, true)) {
    $filtro = 'todos';
}
$ordenes = mass_proveedor_ordenes($filtro === 'todos' ? null : $filtro);

$tituloProveedor = 'Órdenes de compra — Proveedor Mass';
$provActivo = 'ordenes';
$provCrumb = 'Órdenes de compra (OC)';

require __DIR__ . '/partials/header.php';
?>

<header class="prov-hero">
  <div>
    <h1>Órdenes de compra Mass</h1>
    <p>Confirma, despacha o rechaza pedidos de reposición. Cada cambio queda en tu sesión demo.</p>
  </div>
</header>

<nav class="prov-filters" aria-label="Filtrar por estado">
  <?php foreach ($validos as $f): ?>
    <a href="ordenes.php?estado=<?= e($f) ?>" class="<?= $filtro === $f ? 'is-active' : '' ?>"><?= e(ucfirst(str_replace('_', ' ', $f))) ?></a>
  <?php endforeach; ?>
</nav>

<div class="prov-table-wrap">
  <?php if (!$ordenes): ?>
    <div class="prov-empty">No hay órdenes con este filtro.</div>
  <?php else: ?>
    <table class="prov-table">
      <thead>
        <tr><th>Orden</th><th>Tienda / entrega</th><th>Detalle</th><th>Total</th><th>Estado</th><th>Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($ordenes as $o): ?>
          <?php $est = (string) ($o['estado'] ?? ''); ?>
          <tr id="<?= e((string) $o['id']) ?>">
            <td><strong><?= e((string) $o['id']) ?></strong><div class="prov-detail">Emitida <?= e((string) $o['fecha']) ?></div></td>
            <td><?= e((string) $o['tienda']) ?><div class="prov-detail">Entrega: <?= e((string) $o['entrega']) ?></div></td>
            <td>
              <?= (int) $o['items'] ?> SKU · <?= (int) $o['unidades'] ?> und.
              <div class="prov-detail">
                <?php foreach (array_slice($o['lineas'] ?? [], 0, 3) as $ln): ?>
                  <?= e((string) $ln['sku']) ?> × <?= (int) $ln['cant'] ?><br />
                <?php endforeach; ?>
              </div>
            </td>
            <td><?= e(dinero((float) $o['total'])) ?></td>
            <td><span class="prov-badge is-<?= e($est) ?>"><?= e((string) $o['estadoLabel']) ?></span></td>
            <td>
              <div class="prov-actions">
                <?php if ($est === 'pendiente'): ?>
                  <form method="post"><?= csrf_field() ?><input type="hidden" name="filtro_estado" value="<?= e($filtro) ?>" /><input type="hidden" name="oc_id" value="<?= e((string) $o['id']) ?>" /><input type="hidden" name="accion" value="confirmar" /><button class="prov-btn" type="submit">Confirmar</button></form>
                  <form method="post"><?= csrf_field() ?><input type="hidden" name="filtro_estado" value="<?= e($filtro) ?>" /><input type="hidden" name="oc_id" value="<?= e((string) $o['id']) ?>" /><input type="hidden" name="accion" value="rechazar" /><button class="prov-btn is-danger" type="submit">Rechazar</button></form>
                <?php elseif ($est === 'confirmada'): ?>
                  <form method="post"><?= csrf_field() ?><input type="hidden" name="filtro_estado" value="<?= e($filtro) ?>" /><input type="hidden" name="oc_id" value="<?= e((string) $o['id']) ?>" /><input type="hidden" name="accion" value="despachar" /><button class="prov-btn" type="submit">Marcar en ruta</button></form>
                <?php elseif ($est === 'en_ruta'): ?>
                  <form method="post"><?= csrf_field() ?><input type="hidden" name="filtro_estado" value="<?= e($filtro) ?>" /><input type="hidden" name="oc_id" value="<?= e((string) $o['id']) ?>" /><input type="hidden" name="accion" value="entregar" /><button class="prov-btn" type="submit">Confirmar entrega</button></form>
                <?php else: ?>
                  <button class="prov-btn is-ghost" type="button" disabled>Sin acciones</button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
