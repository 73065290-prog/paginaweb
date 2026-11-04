<?php
declare(strict_types=1);

/* bootstrap vía shim */
auth_requerir_admin();
require_once MASS_ROOT . '/includes/admin_operaciones.php';

$tituloAdmin = 'Inventario & Góndolas — Mass Admin';
$adminActivo = 'inventario';
$adminCrumb = 'Inventario & Góndolas';

$errores = [];
$avisos = [];
$q = trim((string) ($_GET['q'] ?? ''));
$estado = (string) ($_GET['estado'] ?? 'todos');
$marca = (string) ($_GET['marca'] ?? 'todas');
$orden = (string) ($_GET['orden'] ?? 'nombre');
$pagina = (string) ($_GET['pagina'] ?? '');
$ajustarId = (int) ($_GET['ajustar'] ?? 0);
$stickyGondola = '';
$stickyTrastienda = '';
$stickyTransfer = '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q = trim((string) ($_POST['q'] ?? ''));
    $estado = (string) ($_POST['estado'] ?? 'todos');
    $marca = (string) ($_POST['marca'] ?? 'todas');
    $orden = (string) ($_POST['orden'] ?? 'nombre');
    $pagina = (string) ($_POST['pagina'] ?? '');
    $volver = ['q' => $q, 'estado' => $estado, 'marca' => $marca, 'orden' => $orden, 'pagina' => $pagina];
    if (!csrf_validar(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
        $errores[] = 'Sesión inválida. Recarga la página e intenta de nuevo.';
    } else {
        $accion = (string) ($_POST['accion'] ?? '');
        if ($accion === 'reponer') {
            $resultado = mass_ops_reponer((int) ($_POST['id'] ?? 0));
            if ($resultado['ok']) {
                flash_set('Reposición aplicada: ' . (int) ($resultado['movidas'] ?? 0) . ' unidades pasaron de trastienda a góndola.');
                mass_ops_volver('inventario.php', $volver);
            }
            $errores = $resultado['errores'];
        }
        if ($accion === 'reponer_lote') {
            $ids = is_array($_POST['ids'] ?? null) ? $_POST['ids'] : [];
            $resultado = mass_ops_reponer_lote($ids);
            if ($resultado['ok']) {
                flash_set('Lote aplicado en ' . (int) ($resultado['aplicados'] ?? 0) . ' SKU · ' . (int) ($resultado['movidas'] ?? 0) . ' unidades.');
                mass_ops_volver('inventario.php', $volver);
            }
            $errores = $resultado['errores'];
        }
        if ($accion === 'ajustar') {
            $idAjuste = (int) ($_POST['id'] ?? 0);
            $stickyGondola = trim((string) ($_POST['gondola'] ?? ''));
            $stickyTrastienda = trim((string) ($_POST['trastienda'] ?? ''));
            $resultado = mass_ops_ajustar($idAjuste, $stickyGondola, $stickyTrastienda);
            if ($resultado['ok']) {
                flash_set('Ajuste de stock guardado para ' . mass_ops_sku($idAjuste) . '.');
                mass_ops_volver('inventario.php', $volver);
            }
            $errores = $resultado['errores'];
            $ajustarId = $idAjuste;
        }
        if ($accion === 'transferir') {
            $resultado = mass_ops_transferir((int) ($_POST['id'] ?? 0), (string) ($_POST['destino'] ?? ''), $_POST['cantidad'] ?? '');
            if ($resultado['ok']) {
                flash_set('Transferencia registrada en ' . mass_ops_sku((int) ($_POST['id'] ?? 0)) . '.');
                mass_ops_volver('inventario.php', $volver);
            }
            $errores = $resultado['errores'];
            $stickyTransfer = trim((string) ($_POST['cantidad'] ?? '1'));
            $ajustarId = (int) ($_POST['id'] ?? 0);
        }
    }
}

$consulta = [
    'q' => $q,
    'estado' => $estado,
    'marca' => $marca,
    'orden' => $orden,
    'pagina' => $pagina,
];
$vista = mass_ops_inventario_vista($consulta);
$opsQuery = [
    'q' => $vista['q'],
    'estado' => $vista['estado'],
    'marca' => $vista['marca'],
    'orden' => $vista['orden'],
];
$encabezados = ['SKU', 'Producto', 'Marca', 'Góndola', 'Trastienda', 'Total', 'Estado'];
$planas = mass_ops_inventario_filas_planas($vista['exportar']);
$export = (string) ($_GET['export'] ?? '');
if ($export === 'csv') {
    mass_ops_descargar('inventario-gondolas.csv', 'text/csv; charset=UTF-8', mass_ops_csv($encabezados, $planas));
}
if ($export === 'xls') {
    mass_ops_descargar('inventario-gondolas.xls', 'application/vnd.ms-excel; charset=UTF-8', mass_ops_xls('Inventario y góndolas', $encabezados, $planas));
}
if ($export === 'pdf') {
    echo mass_ops_documento_html('Inventario y góndolas', 'Corte operativo ' . mass_ops_hoy(), $encabezados, $planas);
    exit;
}

$ajustarFila = $ajustarId > 0 ? mass_ops_inventario_por_id($ajustarId) : null;
if ($ajustarFila !== null && $stickyGondola === '' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stickyGondola = (string) $ajustarFila['gondola'];
    $stickyTrastienda = (string) $ajustarFila['trastienda'];
}
$resumen = $vista['resumen'];
$filas = $vista['filas'];
$movimientos = array_slice(mass_ops_movimientos(), 0, 6);

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<div class="ops-suite">
<div class="page-toolbar">
  <div>
    <h1 class="admin-title" style="margin:0">Inventario &amp; Góndolas</h1>
    <p class="dash-sub">Reposición al umbral, ajuste de piso, transferencia y lote. El valor usa el precio de catálogo.</p>
  </div>
  <div class="ops-toolbar">
    <?php $opsExportTitulo = 'inventario'; require MASS_ROOT . '/frontend/views/admin/partials/ops-export.php'; ?>
  </div>
</div>

<section class="kpi-grid" style="grid-template-columns: repeat(4, 1fr)" aria-label="Resumen de góndola">
  <article class="kpi-card"><h3>Stock OK</h3><p class="kpi-value"><?= (int) $resumen['ok'] ?></p></article>
  <article class="kpi-card"><h3>Bajo</h3><p class="kpi-value"><?= (int) $resumen['bajo'] ?></p></article>
  <article class="kpi-card is-danger"><h3>Crítico</h3><p class="kpi-value"><?= (int) $resumen['critico'] ?></p></article>
  <article class="kpi-card"><h3>Valor en piso</h3><p class="kpi-value"><?= e(dinero((float) $resumen['valor'])) ?></p><p class="kpi-meta"><?= (int) $resumen['unidades'] ?> unidades</p></article>
</section>

<?php if (($flash ?? '') !== ''): ?>
  <div class="prod-toast prod-toast--page" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>
<?php if ($errores !== []): ?>
  <div class="ops-alert" role="alert">
    <strong>No se pudo actualizar el stock</strong>
    <ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form class="ops-filters" method="get" action="inventario.php">
  <label>Buscar
    <input type="search" name="q" value="<?= e((string) $vista['q']) ?>" placeholder="Nombre, SKU o marca" />
  </label>
  <label>Estado
    <select name="estado">
      <?php foreach (['todos' => 'Todos', 'ok' => 'Stock OK', 'bajo' => 'Bajo', 'critico' => 'Crítico'] as $valor => $texto): ?>
        <option value="<?= e($valor) ?>" <?= $vista['estado'] === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Marca
    <select name="marca">
      <option value="todas">Todas</option>
      <?php foreach ($vista['marcas'] as $opcion): ?>
        <option value="<?= e($opcion) ?>" <?= $vista['marca'] === $opcion ? 'selected' : '' ?>><?= e($opcion) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Orden
    <select name="orden">
      <option value="nombre" <?= $vista['orden'] === 'nombre' ? 'selected' : '' ?>>Nombre</option>
      <option value="gondola" <?= $vista['orden'] === 'gondola' ? 'selected' : '' ?>>Góndola</option>
      <option value="critico" <?= $vista['orden'] === 'critico' ? 'selected' : '' ?>>Más crítico</option>
    </select>
  </label>
  <button type="submit" class="btn-mini">Aplicar</button>
</form>

<label class="ops-live-label">Filtrar esta página
  <input type="search" data-ops-live="[data-ops-table]" placeholder="Texto visible en la tabla" />
</label>

<?php if ($ajustarFila !== null): ?>
  <article class="admin-card ops-detail">
    <h2>Ajustar <?= e((string) $ajustarFila['sku']) ?> · <?= e((string) $ajustarFila['nombre']) ?></h2>
    <form method="post" class="ops-form" action="inventario.php" data-ops-form>
      <?= csrf_field() ?>
      <input type="hidden" name="accion" value="ajustar" />
      <input type="hidden" name="id" value="<?= (int) $ajustarFila['id'] ?>" />
      <input type="hidden" name="q" value="<?= e((string) $vista['q']) ?>" />
      <input type="hidden" name="estado" value="<?= e((string) $vista['estado']) ?>" />
      <input type="hidden" name="marca" value="<?= e((string) $vista['marca']) ?>" />
      <input type="hidden" name="orden" value="<?= e((string) $vista['orden']) ?>" />
      <label>Góndola <input type="number" name="gondola" min="0" max="9999" required value="<?= e($stickyGondola) ?>" /></label>
      <label>Trastienda <input type="number" name="trastienda" min="0" max="9999" required value="<?= e($stickyTrastienda) ?>" /></label>
      <button type="submit" class="btn-mini">Guardar ajuste</button>
    </form>
  </article>
<?php endif; ?>

<form id="loteInventario" method="post" action="inventario.php" data-ops-form>
  <?= csrf_field() ?>
  <input type="hidden" name="accion" value="reponer_lote" />
  <input type="hidden" name="q" value="<?= e((string) $vista['q']) ?>" />
  <input type="hidden" name="estado" value="<?= e((string) $vista['estado']) ?>" />
  <input type="hidden" name="marca" value="<?= e((string) $vista['marca']) ?>" />
  <input type="hidden" name="orden" value="<?= e((string) $vista['orden']) ?>" />
  <input type="hidden" name="pagina" value="<?= (int) $vista['pager']['page'] ?>" />
</form>

<article class="admin-card">
  <div class="card-head">
    <h2>Control de piso</h2>
    <button type="submit" class="ops-btn" form="loteInventario">Reponer selección</button>
  </div>
  <div class="table-wrap">
    <table class="admin-table" data-ops-table>
      <thead>
        <tr>
          <th><span class="sr-only">Seleccionar</span></th>
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
          <tr><td colspan="7"><p class="empty-note">Sin resultados para esos filtros.</p></td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $row): ?>
          <?php $texto = mb_strtolower((string) $row['nombre'] . ' ' . (string) $row['sku'] . ' ' . (string) $row['marca']); ?>
          <tr data-ops-row="<?= e($texto) ?>">
            <td><input form="loteInventario" type="checkbox" name="ids[]" value="<?= (int) $row['id'] ?>" aria-label="Seleccionar <?= e((string) $row['sku']) ?>" /></td>
            <td>
              <div class="prod-cell">
                <img src="<?= e((string) $row['img']) ?>" alt="" />
                <div>
                  <strong><?= e((string) $row['nombre']) ?></strong>
                  <small><?= e((string) $row['sku']) ?> · <?= e((string) $row['marca']) ?></small>
                </div>
              </div>
            </td>
            <td><?= (int) $row['gondola'] ?></td>
            <td><?= (int) $row['trastienda'] ?></td>
            <td><strong><?= (int) $row['gondola'] + (int) $row['trastienda'] ?></strong></td>
            <td><span class="status-pill <?= e((string) $row['estado']) ?>"><?= e(mass_ops_estado_stock_label((string) $row['estado'])) ?></span></td>
            <td>
              <div class="ops-actions">
                <?php if ($row['estado'] !== 'ok'): ?>
                  <form method="post" class="inline-form" action="inventario.php" data-ops-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="reponer" />
                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                    <input type="hidden" name="q" value="<?= e((string) $vista['q']) ?>" />
                    <input type="hidden" name="estado" value="<?= e((string) $vista['estado']) ?>" />
                    <input type="hidden" name="marca" value="<?= e((string) $vista['marca']) ?>" />
                    <input type="hidden" name="orden" value="<?= e((string) $vista['orden']) ?>" />
                    <button type="submit" class="btn-mini warn">Reponer</button>
                  </form>
                <?php endif; ?>
                <a class="ops-btn" href="inventario.php?<?= e(mass_ops_query($opsQuery + ['ajustar' => (int) $row['id']])) ?>"
                   data-ops-dialog="dlgAjuste"
                   data-id="<?= (int) $row['id'] ?>"
                   data-gondola="<?= (int) $row['gondola'] ?>"
                   data-trastienda="<?= (int) $row['trastienda'] ?>"
                   data-titulo="Ajustar <?= e((string) $row['sku']) ?>">Ajustar</a>
                <button type="button" class="ops-btn" data-ops-dialog="dlgTransfer"
                   data-id="<?= (int) $row['id'] ?>"
                   data-titulo="Transferir <?= e((string) $row['sku']) ?>">Transferir</button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php $opsPager = $vista['pager']; require MASS_ROOT . '/frontend/views/admin/partials/ops-pager.php'; ?>
</article>

<?php if ($movimientos !== []): ?>
  <article class="admin-card">
    <h2>Movimientos recientes</h2>
    <ul class="ops-log">
      <?php foreach ($movimientos as $mov): ?>
        <li><time><?= e((string) $mov['cuando']) ?></time> <strong><?= e((string) $mov['ref']) ?></strong> <?= e((string) $mov['detalle']) ?></li>
      <?php endforeach; ?>
    </ul>
  </article>
<?php endif; ?>

<dialog class="ops-modal" id="dlgAjuste" aria-labelledby="dlgAjusteTitle">
  <form method="post" action="inventario.php" data-ops-form>
    <?= csrf_field() ?>
    <input type="hidden" name="accion" value="ajustar" />
    <input type="hidden" name="id" data-ops-field="id" value="" />
    <input type="hidden" name="q" value="<?= e((string) $vista['q']) ?>" />
    <input type="hidden" name="estado" value="<?= e((string) $vista['estado']) ?>" />
    <input type="hidden" name="marca" value="<?= e((string) $vista['marca']) ?>" />
    <input type="hidden" name="orden" value="<?= e((string) $vista['orden']) ?>" />
    <h2 id="dlgAjusteTitle" data-ops-title>Ajustar stock</h2>
    <div class="ops-form">
      <label>Góndola <input type="number" name="gondola" min="0" max="9999" required data-ops-field="gondola" /></label>
      <label>Trastienda <input type="number" name="trastienda" min="0" max="9999" required data-ops-field="trastienda" /></label>
    </div>
    <div class="ops-actions">
      <button type="submit" class="btn-mini">Guardar ajuste</button>
      <button type="button" class="ops-btn" data-ops-close>Cerrar</button>
    </div>
  </form>
</dialog>

<dialog class="ops-modal" id="dlgTransfer" aria-labelledby="dlgTransferTitle">
  <form method="post" action="inventario.php" data-ops-form>
    <?= csrf_field() ?>
    <input type="hidden" name="accion" value="transferir" />
    <input type="hidden" name="id" data-ops-field="id" value="" />
    <input type="hidden" name="q" value="<?= e((string) $vista['q']) ?>" />
    <input type="hidden" name="estado" value="<?= e((string) $vista['estado']) ?>" />
    <input type="hidden" name="marca" value="<?= e((string) $vista['marca']) ?>" />
    <input type="hidden" name="orden" value="<?= e((string) $vista['orden']) ?>" />
    <h2 id="dlgTransferTitle" data-ops-title>Transferir</h2>
    <div class="ops-form">
      <label>Destino
        <select name="destino">
          <option value="gondola">Hacia góndola</option>
          <option value="trastienda">De regreso a trastienda</option>
        </select>
      </label>
      <label>Cantidad <input type="number" name="cantidad" min="1" max="9999" required value="<?= e($stickyTransfer) ?>" /></label>
    </div>
    <div class="ops-actions">
      <button type="submit" class="btn-mini">Transferir</button>
      <button type="button" class="ops-btn" data-ops-close>Cerrar</button>
    </div>
  </form>
</dialog>
</div>

<script src="<?= e(mass_asset('assets/js/admin-ops.js')) ?>" defer></script>
<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>
