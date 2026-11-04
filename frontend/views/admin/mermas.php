<?php
declare(strict_types=1);

/* bootstrap vía shim */
auth_requerir_admin();
require_once MASS_ROOT . '/includes/admin_operaciones.php';

$tituloAdmin = 'Mermas y caducidades — Mass Admin';
$adminActivo = 'mermas';
$adminCrumb = 'Mermas y Caducidades';

$errores = [];
$filtro = (string) ($_GET['filtro'] ?? 'todas');
$sticky = [
    'producto_id' => '',
    'tipo' => 'merma',
    'motivo' => 'Daño en góndola',
    'cantidad' => '1',
    'vence' => '',
    'descontar' => '1',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filtro = (string) ($_POST['filtro'] ?? 'todas');
    if (!csrf_validar(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
        $errores[] = 'Sesión inválida. Recarga la página e intenta de nuevo.';
    } elseif ((string) ($_POST['accion'] ?? '') === 'registrar') {
        $sticky = [
            'producto_id' => trim((string) ($_POST['producto_id'] ?? '')),
            'tipo' => (string) ($_POST['tipo'] ?? 'merma'),
            'motivo' => (string) ($_POST['motivo'] ?? ''),
            'cantidad' => trim((string) ($_POST['cantidad'] ?? '')),
            'vence' => trim((string) ($_POST['vence'] ?? '')),
            'descontar' => (string) ($_POST['descontar'] ?? ''),
        ];
        $resultado = mass_ops_merma_registrar($sticky);
        if ($resultado['ok']) {
            flash_set('Registro ' . (string) $resultado['id'] . ' guardado en mermas y caducidades.');
            mass_ops_volver('mermas.php', ['filtro' => $filtro]);
        }
        $errores = $resultado['errores'];
    } elseif ((string) ($_POST['accion'] ?? '') === 'retirar') {
        $resultado = mass_ops_merma_retirar(trim((string) ($_POST['id'] ?? '')));
        if ($resultado['ok']) {
            flash_set('El lote se retiró de góndola y el stock de piso quedó descontado.');
            mass_ops_volver('mermas.php', ['filtro' => $filtro]);
        }
        $errores = $resultado['errores'];
    } elseif ((string) ($_POST['accion'] ?? '') === 'editar') {
        $resultado = mass_ops_merma_editar(trim((string) ($_POST['id'] ?? '')), $_POST);
        if ($resultado['ok']) {
            flash_set('El registro se actualizó.');
            mass_ops_volver('mermas.php', ['filtro' => $filtro, 'q' => trim((string) ($_POST['q'] ?? ''))]);
        }
        $errores = $resultado['errores'];
    } elseif ((string) ($_POST['accion'] ?? '') === 'anular') {
        $resultado = mass_ops_merma_anular(trim((string) ($_POST['id'] ?? '')));
        if ($resultado['ok']) {
            flash_set('Registro anulado. Si ya había descuento, el stock volvió a la góndola.');
            mass_ops_volver('mermas.php', ['filtro' => $filtro]);
        }
        $errores = $resultado['errores'];
    }
}

if (!in_array($filtro, ['todas', 'por_vencer', 'vencidas', 'merma', 'retiradas', 'anuladas'], true)) {
    $filtro = 'todas';
}
$qMerma = trim((string) ($_GET['q'] ?? ''));

$inventario = mass_ops_inventario();
$mermas = mass_ops_mermas();
$mermas = array_values(array_filter(
    $mermas,
    static function (array $merma) use ($filtro): bool {
        $clase = mass_ops_merma_clase($merma);
        return match ($filtro) {
            'por_vencer' => $clase === 'por_vencer' && (string) $merma['estado'] === 'abierta',
            'vencidas' => $clase === 'vencida',
            'merma' => (string) $merma['tipo'] === 'merma',
            'retiradas' => (string) $merma['estado'] === 'retirada',
            'anuladas' => (string) $merma['estado'] === 'anulada',
            default => true,
        };
    }
));
if ($qMerma !== '') {
    $needleMerma = mb_strtolower($qMerma);
    $mermas = array_values(array_filter(
        $mermas,
        static fn (array $merma): bool => str_contains(mb_strtolower((string) $merma['id'] . ' ' . (string) $merma['nombre'] . ' ' . (string) $merma['sku'] . ' ' . (string) $merma['motivo']), $needleMerma)
    ));
}
$mermasExport = $mermas;
$opsPager = mass_ops_paginar($mermas, (int) ($_GET['pagina'] ?? 1), 6);
$mermas = $opsPager['items'];
$opsQuery = ['filtro' => $filtro, 'q' => $qMerma];
$planasMerma = [];
foreach ($mermasExport as $mermaPlana) {
    $planasMerma[] = [
        (string) $mermaPlana['id'],
        (string) $mermaPlana['sku'],
        (string) $mermaPlana['nombre'],
        (string) $mermaPlana['tipo'],
        (string) $mermaPlana['motivo'],
        (int) $mermaPlana['cantidad'],
        (string) $mermaPlana['vence'],
        (string) $mermaPlana['estado'],
    ];
}
$exportMerma = (string) ($_GET['export'] ?? '');
$encabezadosMerma = ['Registro', 'SKU', 'Producto', 'Tipo', 'Motivo', 'Cantidad', 'Vence', 'Estado'];
if ($exportMerma === 'csv') {
    mass_ops_descargar('mermas.csv', 'text/csv; charset=UTF-8', mass_ops_csv($encabezadosMerma, $planasMerma));
}
if ($exportMerma === 'xls') {
    mass_ops_descargar('mermas.xls', 'application/vnd.ms-excel; charset=UTF-8', mass_ops_xls('Mermas y caducidades', $encabezadosMerma, $planasMerma));
}
if ($exportMerma === 'pdf') {
    echo mass_ops_documento_html('Mermas y caducidades', 'Corte ' . mass_ops_hoy(), $encabezadosMerma, $planasMerma);
    exit;
}
$mermaKpi = ['abierta' => 0, 'retirada' => 0, 'anulada' => 0];
foreach (mass_ops_mermas() as $mermaKpiFila) {
    $claveMerma = (string) $mermaKpiFila['estado'];
    if (isset($mermaKpi[$claveMerma])) {
        $mermaKpi[$claveMerma]++;
    }
}

$clasePills = [
    'vencida' => 'critico',
    'por_vencer' => 'vencer',
    'vigente' => 'ok',
    'merma' => 'bajo',
];

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<div class="ops-suite">
<div class="page-toolbar">
  <div>
    <h1 class="admin-title" style="margin:0">Mermas y caducidades</h1>
    <p class="dash-sub">Registra, corrige o anula desmedro y lotes. Anular devuelve a góndola lo que ya se había descontado.</p>
  </div>
  <?php $opsExportTitulo = 'mermas'; require MASS_ROOT . '/frontend/views/admin/partials/ops-export.php'; ?>
</div>
<section class="kpi-grid" style="grid-template-columns: repeat(3, 1fr)">
  <article class="kpi-card"><h3>Abiertas</h3><p class="kpi-value"><?= (int) $mermaKpi['abierta'] ?></p></article>
  <article class="kpi-card"><h3>Retiradas</h3><p class="kpi-value"><?= (int) $mermaKpi['retirada'] ?></p></article>
  <article class="kpi-card"><h3>Anuladas</h3><p class="kpi-value"><?= (int) $mermaKpi['anulada'] ?></p></article>
</section>
<form class="ops-filters" method="get" action="mermas.php">
  <label>Buscar <input type="search" name="q" value="<?= e($qMerma) ?>" placeholder="SKU, producto o motivo" /></label>
  <label>Vista
    <select name="filtro">
      <?php foreach (['todas' => 'Todas', 'por_vencer' => 'Por vencer', 'vencidas' => 'Vencidas', 'merma' => 'Mermas', 'retiradas' => 'Retiradas', 'anuladas' => 'Anuladas'] as $valor => $texto): ?>
        <option value="<?= e($valor) ?>" <?= $filtro === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit" class="btn-mini">Aplicar</button>
</form>

<?php if (($flash ?? '') !== ''): ?>
  <div class="prod-toast prod-toast--page" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>
<?php if ($errores !== []): ?>
  <div class="ops-alert" role="alert">
    <strong>No se pudo registrar la operación</strong>
    <ul>
      <?php foreach ($errores as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<article class="admin-card ops-detail">
  <h2>Nuevo registro</h2>
  <form method="post" action="mermas.php" class="ops-form">
    <?= csrf_field() ?>
    <input type="hidden" name="accion" value="registrar" />
    <input type="hidden" name="filtro" value="<?= e($filtro) ?>" />
    <label>Producto
      <select name="producto_id" required>
        <option value="">Selecciona</option>
        <?php foreach ($inventario as $row): ?>
          <option value="<?= (int) $row['id'] ?>" <?= (string) $row['id'] === $sticky['producto_id'] ? 'selected' : '' ?>>
            <?= e((string) $row['sku']) ?> · <?= e((string) $row['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Tipo
      <select name="tipo">
        <option value="merma" <?= $sticky['tipo'] === 'merma' ? 'selected' : '' ?>>Merma</option>
        <option value="caducidad" <?= $sticky['tipo'] === 'caducidad' ? 'selected' : '' ?>>Caducidad</option>
      </select>
    </label>
    <label>Motivo
      <select name="motivo">
        <?php foreach (['Daño en góndola', 'Desmedro', 'Rotura', 'Vencido', 'Próximo a vencer'] as $motivo): ?>
          <option value="<?= e($motivo) ?>" <?= $sticky['motivo'] === $motivo ? 'selected' : '' ?>><?= e($motivo) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Cantidad
      <input type="number" name="cantidad" min="1" max="999" required value="<?= e($sticky['cantidad']) ?>" />
    </label>
    <label>Vence
      <input type="date" name="vence" value="<?= e($sticky['vence']) ?>" />
    </label>
    <label class="ops-check">
      <input type="checkbox" name="descontar" value="1" <?= $sticky['descontar'] === '1' ? 'checked' : '' ?> />
      Descontar de góndola ahora
    </label>
    <div class="ops-actions">
      <button type="submit" class="btn-mini warn">Registrar</button>
    </div>
  </form>
</article>

<article class="admin-card">
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Registro</th>
          <th>Producto</th>
          <th>Tipo</th>
          <th>Motivo</th>
          <th>Cant.</th>
          <th>Vence</th>
          <th>Clase</th>
          <th>Estado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if ($mermas === []): ?>
          <tr><td colspan="9"><p class="empty-note">No hay registros para este filtro.</p></td></tr>
        <?php endif; ?>
        <?php foreach ($mermas as $merma): ?>
          <?php $clase = mass_ops_merma_clase($merma); ?>
          <tr>
            <td><strong><?= e((string) $merma['id']) ?></strong></td>
            <td>
              <strong><?= e((string) $merma['nombre']) ?></strong>
              <small class="ean"><?= e((string) $merma['sku']) ?></small>
            </td>
            <td><?= (string) $merma['tipo'] === 'caducidad' ? 'Caducidad' : 'Merma' ?></td>
            <td><?= e((string) $merma['motivo']) ?></td>
            <td><?= (int) $merma['cantidad'] ?></td>
            <td><?= (string) $merma['vence'] !== '' ? e((string) $merma['vence']) : '—' ?></td>
            <td><span class="status-pill <?= e($clasePills[$clase] ?? 'ok') ?>"><?= e(mass_ops_merma_clase_label($clase)) ?></span></td>
            <td><span class="status-pill <?= (string) $merma['estado'] === 'retirada' ? 'retirado' : 'bajo' ?>"><?= (string) $merma['estado'] === 'retirada' ? 'Retirada' : 'Abierta' ?></span></td>
            <td>
              <div class="ops-actions">
                <?php if ((string) $merma['estado'] === 'abierta'): ?>
                  <form method="post" class="inline-form" action="mermas.php" data-ops-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="retirar" />
                    <input type="hidden" name="id" value="<?= e((string) $merma['id']) ?>" />
                    <input type="hidden" name="filtro" value="<?= e($filtro) ?>" />
                    <button type="submit" class="btn-mini warn">Retirar</button>
                  </form>
                  <button type="button" class="ops-btn" data-ops-dialog="dlgMerma"
                    data-id="<?= e((string) $merma['id']) ?>"
                    data-cantidad="<?= (int) $merma['cantidad'] ?>"
                    data-motivo="<?= e((string) $merma['motivo']) ?>"
                    data-vence="<?= e((string) $merma['vence']) ?>"
                    data-titulo="Editar <?= e((string) $merma['id']) ?>">Editar</button>
                <?php endif; ?>
                <?php if ((string) $merma['estado'] !== 'anulada'): ?>
                  <form method="post" class="inline-form" action="mermas.php" data-ops-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="anular" />
                    <input type="hidden" name="id" value="<?= e((string) $merma['id']) ?>" />
                    <input type="hidden" name="filtro" value="<?= e($filtro) ?>" />
                    <button type="submit" class="ops-btn">Anular</button>
                  </form>
                <?php else: ?>
                  <span class="status-pill retirado">Anulada</span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php require MASS_ROOT . '/frontend/views/admin/partials/ops-pager.php'; ?>
</article>
<dialog class="ops-modal" id="dlgMerma" aria-labelledby="dlgMermaTitle">
  <form method="post" action="mermas.php" data-ops-form>
    <?= csrf_field() ?>
    <input type="hidden" name="accion" value="editar" />
    <input type="hidden" name="id" data-ops-field="id" />
    <input type="hidden" name="filtro" value="<?= e($filtro) ?>" />
    <input type="hidden" name="q" value="<?= e($qMerma) ?>" />
    <h2 id="dlgMermaTitle" data-ops-title>Editar registro</h2>
    <div class="ops-form">
      <label>Cantidad <input type="number" name="cantidad" min="1" max="999" required data-ops-field="cantidad" /></label>
      <label>Motivo
        <select name="motivo" data-ops-field="motivo">
          <?php foreach (['Daño en góndola', 'Desmedro', 'Rotura', 'Vencido', 'Próximo a vencer'] as $motivo): ?>
            <option value="<?= e($motivo) ?>"><?= e($motivo) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Vence <input type="date" name="vence" data-ops-field="vence" /></label>
    </div>
    <div class="ops-actions">
      <button type="submit" class="btn-mini">Guardar</button>
      <button type="button" class="ops-btn" data-ops-close>Cerrar</button>
    </div>
  </form>
</dialog>
</div>
<script src="<?= e(mass_asset('assets/js/admin-ops.js')) ?>" defer></script>

<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>
