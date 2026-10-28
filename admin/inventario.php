<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
auth_requerir_admin();

$tituloAdmin = 'Inventario & Góndolas — Mass Admin';
$adminActivo = 'inventario';
$adminCrumb = 'Inventario & Góndolas';

$q = mb_strtolower(trim((string) ($_GET['q'] ?? '')));
$estado = (string) ($_GET['estado'] ?? 'todos');
$filas = mass_admin_gondola();

// Enriquecer con catálogo completo para vista inventario
$extra = [];
$idsGondola = array_column($filas, 'id');
foreach (mass_productos() as $p) {
    if (in_array((int) $p['id'], $idsGondola, true)) {
        continue;
    }
    $extra[] = [
        'id' => (int) $p['id'],
        'nombre' => $p['nombre'],
        'sku' => 'SKU-' . str_pad((string) $p['id'], 4, '0', STR_PAD_LEFT),
        'img' => $p['img'],
        'marca' => $p['marca'],
        'gondola' => $p['stockTone'] === 'bajo' ? 5 : 20,
        'trastienda' => $p['stockTone'] === 'bajo' ? 3 : 15,
        'estado' => $p['stockTone'] === 'bajo' ? 'bajo' : 'ok',
    ];
}
$filas = array_merge($filas, $extra);

if ($q !== '') {
    $filas = array_values(array_filter(
        $filas,
        static function (array $r) use ($q): bool {
            $hay = mb_strtolower($r['nombre'] . ' ' . $r['sku'] . ' ' . $r['marca']);
            return str_contains($hay, $q);
        }
    ));
}

if (in_array($estado, ['ok', 'bajo', 'critico'], true)) {
    $filas = array_values(array_filter(
        $filas,
        static fn (array $r): bool => ($r['estado'] ?? '') === $estado
    ));
}

$kpis = mass_admin_kpis();

require dirname(__DIR__) . '/includes/admin/header.php';
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
                <?= match ($row['estado']) {
                    'ok' => 'Stock OK',
                    'bajo' => 'Bajo',
                    default => 'Crítico',
                } ?>
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

<?php require dirname(__DIR__) . '/includes/admin/footer.php'; ?>
