<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
auth_requerir_admin();

$tituloAdmin = 'Pedidos & Picking — Mass Admin';
$adminActivo = 'pedidos';
$adminCrumb = 'Pedidos & Picking';

$filtro = (string) ($_GET['modo'] ?? 'todos');
$codigo = (string) ($_GET['codigo'] ?? '');
$pedidos = mass_admin_pedidos();

if ($filtro === 'express' || $filtro === 'retiro') {
    $pedidos = array_values(array_filter(
        $pedidos,
        static fn (array $p): bool => ($p['modo'] ?? '') === $filtro
    ));
}

if ($codigo !== '') {
    $pedidos = array_values(array_filter(
        $pedidos,
        static fn (array $p): bool => ($p['codigo'] ?? '') === $codigo
    ));
}

require dirname(__DIR__) . '/includes/admin/header.php';
?>

<div class="page-toolbar">
  <h1 class="admin-title" style="margin:0">Pedidos &amp; Picking</h1>
  <div class="filters-inline">
    <a href="pedidos.php" class="<?= $filtro === 'todos' && $codigo === '' ? 'is-active' : '' ?>">Todos</a>
    <a href="pedidos.php?modo=express" class="<?= $filtro === 'express' ? 'is-active' : '' ?>">Delivery</a>
    <a href="pedidos.php?modo=retiro" class="<?= $filtro === 'retiro' ? 'is-active' : '' ?>">Retiro tienda</a>
  </div>
</div>

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
            <td><?= (int) ($p['mins'] ?? 0) > 0 ? ((int) $p['mins'] . ' min') : '—' ?></td>
            <td>
              <?php if (in_array($p['estado'], ['preparacion', 'por_recoger'], true)): ?>
                <button type="button" class="btn-mini">Picking</button>
              <?php else: ?>
                <button type="button" class="btn-mini ghost">Detalle</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</article>

<?php require dirname(__DIR__) . '/includes/admin/footer.php'; ?>
