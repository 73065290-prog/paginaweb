<?php
declare(strict_types=1);

/* bootstrap vía shim */
auth_requerir_admin();
require_once MASS_ROOT . '/includes/admin_operaciones.php';

$tituloAdmin = 'Inventario & Góndolas — Mass Admin';
$adminActivo = 'inventario';
$adminCrumb = 'Inventario & Góndolas';

$errores = [];
$q = trim((string) ($_GET['q'] ?? ''));
$estado = (string) ($_GET['estado'] ?? 'todos');
$ajustarId = (int) ($_GET['ajustar'] ?? 0);
$stickyGondola = '';
$stickyTrastienda = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q = trim((string) ($_POST['q'] ?? ''));
    $estado = (string) ($_POST['estado'] ?? 'todos');
    $ajustarId = (int) ($_POST['id'] ?? 0);
    if (!csrf_validar(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
        $errores[] = 'Sesión inválida. Recarga la página e intenta de nuevo.';
    } else {
        $accion = (string) ($_POST['accion'] ?? '');
        if ($accion === 'reponer') {
            $resultado = mass_ops_reponer((int) ($_POST['id'] ?? 0));
            if ($resultado['ok']) {
                flash_set('Reposición aplicada: ' . (int) ($resultado['movidas'] ?? 0) . ' unidades pasaron de trastienda a góndola.');
                mass_ops_volver('inventario.php', ['q' => $q, 'estado' => $estado]);
            }
            $errores = $resultado['errores'];
            $ajustarId = 0;
        }
        if ($accion === 'ajustar') {
            $idAjuste = (int) ($_POST['id'] ?? 0);
            $stickyGondola = trim((string) ($_POST['gondola'] ?? ''));
            $stickyTrastienda = trim((string) ($_POST['trastienda'] ?? ''));
            $resultado = mass_ops_ajustar($idAjuste, $stickyGondola, $stickyTrastienda);
            if ($resultado['ok']) {
                flash_set('Ajuste de stock guardado para ' . mass_ops_sku($idAjuste) . '.');
                mass_ops_volver('inventario.php', ['q' => $q, 'estado' => $estado]);
            }
            $errores = $resultado['errores'];
            $ajustarId = $idAjuste;
        }
    }
}

if (!in_array($estado, ['todos', 'ok', 'bajo', 'critico'], true)) {
    $estado = 'todos';
}

$filas = mass_ops_inventario();
$busqueda = mb_strtolower($q);
if ($busqueda !== '') {
    $filas = array_values(array_filter(
        $filas,
        static function (array $r) use ($busqueda): bool {
            $hay = mb_strtolower($r['nombre'] . ' ' . $r['sku'] . ' ' . $r['marca']);
            return str_contains($hay, $busqueda);
        }
    ));
}
if (in_array($estado, ['ok', 'bajo', 'critico'], true)) {
    $filas = array_values(array_filter(
        $filas,
        static fn (array $r): bool => ($r['estado'] ?? '') === $estado
    ));
}

$ajustarFila = $ajustarId > 0 ? mass_ops_inventario_por_id($ajustarId) : null;
if ($ajustarFila !== null && $stickyGondola === '' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stickyGondola = (string) $ajustarFila['gondola'];
    $stickyTrastienda = (string) $ajustarFila['trastienda'];
}

$kpis = mass_admin_kpis();
$cfg = mass_ops_config();

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<div class="page-toolbar">
  <div>
    <h1 class="admin-title" style="margin:0">Inventario &amp; Góndolas</h1>
    <p class="dash-sub">Reponer mueve stock de trastienda a góndola hasta el umbral <?= (int) $cfg['umbralBajo'] ?> + 4. Ajustar fija el conteo de piso y trastienda.</p>
  </div>
  <div class="filters-inline">
    <a href="inventario.php<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>" class="<?= $estado === 'todos' ? 'is-active' : '' ?>">Todos</a>
    <a href="inventario.php?<?= e(http_build_query(array_filter(['q' => $q, 'estado' => 'ok']))) ?>" class="<?= $estado === 'ok' ? 'is-active' : '' ?>">Stock OK</a>
    <a href="inventario.php?<?= e(http_build_query(array_filter(['q' => $q, 'estado' => 'bajo']))) ?>" class="<?= $estado === 'bajo' ? 'is-active' : '' ?>">Bajo</a>
    <a href="inventario.php?<?= e(http_build_query(array_filter(['q' => $q, 'estado' => 'critico']))) ?>" class="<?= $estado === 'critico' ? 'is-active' : '' ?>">Crítico</a>
  </div>
</div>

<?php if (($flash ?? '') !== ''): ?>
  <div class="prod-toast prod-toast--page" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>
<?php if ($errores !== []): ?>
  <div class="ops-alert" role="alert">
    <strong>No se pudo actualizar el stock</strong>
    <ul>
      <?php foreach ($errores as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($ajustarFila !== null): ?>
  <article class="admin-card ops-detail">
    <h2>Ajustar <?= e((string) $ajustarFila['sku']) ?> · <?= e((string) $ajustarFila['nombre']) ?></h2>
    <form method="post" class="ops-form" action="inventario.php">
      <?= csrf_field() ?>
      <input type="hidden" name="accion" value="ajustar" />
      <input type="hidden" name="id" value="<?= (int) $ajustarFila['id'] ?>" />
      <input type="hidden" name="q" value="<?= e($q) ?>" />
      <input type="hidden" name="estado" value="<?= e($estado) ?>" />
      <label>Góndola
        <input type="number" name="gondola" min="0" max="9999" required value="<?= e($stickyGondola) ?>" />
      </label>
      <label>Trastienda
        <input type="number" name="trastienda" min="0" max="9999" required value="<?= e($stickyTrastienda) ?>" />
      </label>
      <div class="ops-actions">
        <button type="submit" class="btn-mini">Guardar ajuste</button>
        <a class="btn-mini ghost" href="inventario.php?<?= e(http_build_query(array_filter(['q' => $q, 'estado' => $estado === 'todos' ? null : $estado]))) ?>">Cancelar</a>
      </div>
    </form>
  </article>
<?php endif; ?>

<section class="kpi-grid" style="grid-template-columns: repeat(3, 1fr)">
  <article class="kpi-card">
    <h3>Disponibilidad</h3>
    <p class="kpi-value"><?= e(number_format((float) $kpis['disponibilidad'], 1)) ?>%</p>
  </article>
  <article class="kpi-card">
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
                <?= e(mass_ops_estado_stock_label((string) $row['estado'])) ?>
              </span>
            </td>
            <td>
              <div class="ops-actions">
                <?php if ($row['estado'] !== 'ok'): ?>
                  <form method="post" class="inline-form" action="inventario.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="reponer" />
                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                    <input type="hidden" name="q" value="<?= e($q) ?>" />
                    <input type="hidden" name="estado" value="<?= e($estado) ?>" />
                    <button type="submit" class="btn-mini warn">Reponer góndola</button>
                  </form>
                <?php endif; ?>
                <a class="btn-mini ghost" href="inventario.php?<?= e(http_build_query(array_filter([
                    'q' => $q,
                    'estado' => $estado === 'todos' ? null : $estado,
                    'ajustar' => (int) $row['id'],
                ], static fn ($v) => $v !== null && $v !== ''))) ?>">Ajustar</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</article>

<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>
