<?php
declare(strict_types=1);

/* bootstrap vía shim */
auth_requerir_admin();
require_once MASS_ROOT . '/includes/admin_operaciones.php';

$tituloAdmin = 'Conteos cíclicos — Mass Admin';
$adminActivo = 'conteos';
$adminCrumb = 'Conteos cíclicos & Auditorías';

$errores = [];
$estado = (string) ($_GET['estado'] ?? 'todos');
$conteoId = trim((string) ($_GET['id'] ?? ''));
$sticky = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = (string) ($_POST['estado'] ?? 'todos');
    $conteoId = trim((string) ($_POST['id'] ?? ''));
    $accion = (string) ($_POST['accion'] ?? '');
    if (!csrf_validar(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
        $errores[] = 'Sesión inválida. Recarga la página e intenta de nuevo.';
    } elseif ($accion === 'iniciar') {
        $resultado = mass_ops_conteo_iniciar($conteoId);
        if ($resultado['ok']) {
            flash_set('Conteo ' . $conteoId . ' iniciado. Registra las unidades físicas.');
            mass_ops_volver('conteos.php', ['estado' => $estado, 'id' => $conteoId]);
        }
        $errores = $resultado['errores'];
    } elseif ($accion === 'registrar') {
        $contados = $_POST['contado'] ?? [];
        $sticky = is_array($contados) ? $contados : [];
        $resultado = mass_ops_conteo_registrar($conteoId, $sticky);
        if ($resultado['ok']) {
            flash_set('Conteo físico guardado en ' . $conteoId . '.');
            mass_ops_volver('conteos.php', ['estado' => $estado, 'id' => $conteoId]);
        }
        $errores = $resultado['errores'];
    } elseif ($accion === 'cerrar') {
        $contados = $_POST['contado'] ?? [];
        $sticky = is_array($contados) ? $contados : [];
        $previo = mass_ops_conteo_registrar($conteoId, $sticky);
        if (!$previo['ok']) {
            $errores = $previo['errores'];
        } else {
            $resultado = mass_ops_conteo_cerrar($conteoId, (string) ($_POST['aplicar'] ?? '') === '1');
            if ($resultado['ok']) {
                $texto = ($resultado['resultado'] ?? '') === 'cuadrado'
                    ? 'Auditoría cerrada sin diferencias.'
                    : 'Auditoría cerrada con diferencias de stock.';
                flash_set($texto);
                mass_ops_volver('conteos.php', ['estado' => $estado, 'id' => $conteoId]);
            }
            $errores = $resultado['errores'];
        }
    }
}

if (!in_array($estado, ['todos', 'pendiente', 'en_curso', 'cerrado'], true)) {
    $estado = 'todos';
}

$conteos = mass_ops_conteos();
if ($estado !== 'todos') {
    $conteos = array_values(array_filter(
        $conteos,
        static fn (array $c): bool => (string) ($c['estado'] ?? '') === $estado
    ));
}
$abierto = $conteoId !== '' ? mass_ops_conteo_por_id($conteoId) : null;
$clases = [
    'pendiente' => 'bajo',
    'en_curso' => 'preparacion',
    'cerrado' => 'ok',
];
$etiquetas = [
    'pendiente' => 'Pendiente',
    'en_curso' => 'En curso',
    'cerrado' => 'Cerrado',
];

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<div class="page-toolbar">
  <div>
    <h1 class="admin-title" style="margin:0">Conteos cíclicos &amp; auditorías</h1>
    <p class="dash-sub">Inicia el conteo del pasillo, registra lo físico y cierra la auditoría. Si hay diferencia, puedes aplicar el conteo a la góndola.</p>
  </div>
  <div class="filters-inline">
    <a href="conteos.php" class="<?= $estado === 'todos' ? 'is-active' : '' ?>">Todos</a>
    <a href="conteos.php?estado=pendiente" class="<?= $estado === 'pendiente' ? 'is-active' : '' ?>">Pendientes</a>
    <a href="conteos.php?estado=en_curso" class="<?= $estado === 'en_curso' ? 'is-active' : '' ?>">En curso</a>
    <a href="conteos.php?estado=cerrado" class="<?= $estado === 'cerrado' ? 'is-active' : '' ?>">Cerrados</a>
  </div>
</div>

<?php if (($flash ?? '') !== ''): ?>
  <div class="prod-toast prod-toast--page" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>
<?php if ($errores !== []): ?>
  <div class="ops-alert" role="alert">
    <strong>No se pudo actualizar el conteo</strong>
    <ul>
      <?php foreach ($errores as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($abierto !== null): ?>
  <?php $resumen = mass_ops_conteo_resumen($abierto); ?>
  <article class="admin-card ops-detail">
    <div class="card-head">
      <h2><?= e((string) $abierto['id']) ?> · <?= e((string) $abierto['pasillo']) ?></h2>
      <a class="btn-mini ghost" href="conteos.php<?= $estado === 'todos' ? '' : '?estado=' . e($estado) ?>">Volver al listado</a>
    </div>
    <div class="ops-kv">
      <div><span>Tipo</span> <strong><?= e((string) $abierto['tipo']) ?></strong></div>
      <div><span>Estado</span> <strong><?= e($etiquetas[(string) $abierto['estado']] ?? (string) $abierto['estado']) ?></strong></div>
      <div><span>Responsable</span> <strong><?= e((string) $abierto['responsable']) ?></strong></div>
      <div><span>Programado</span> <strong><?= e((string) $abierto['programado']) ?></strong></div>
      <div><span>Líneas pendientes</span> <strong><?= (int) $resumen['pendientes'] ?></strong></div>
      <?php if ((string) $abierto['estado'] === 'cerrado'): ?>
        <div><span>Resultado</span> <strong><?= ($abierto['resultado'] ?? '') === 'cuadrado' ? 'Cuadrado' : 'Con diferencia' ?></strong></div>
      <?php endif; ?>
    </div>

    <?php if ((string) $abierto['estado'] === 'pendiente'): ?>
      <form method="post" action="conteos.php">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="iniciar" />
        <input type="hidden" name="id" value="<?= e((string) $abierto['id']) ?>" />
        <input type="hidden" name="estado" value="<?= e($estado) ?>" />
        <button type="submit" class="btn-mini">Iniciar conteo</button>
      </form>
    <?php elseif ((string) $abierto['estado'] === 'en_curso'): ?>
      <form method="post" action="conteos.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e((string) $abierto['id']) ?>" />
        <input type="hidden" name="estado" value="<?= e($estado) ?>" />
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Producto</th>
                <th>Sistema (góndola)</th>
                <th>Contado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($abierto['lineas'] as $linea): ?>
                <?php
                  $sku = (string) $linea['sku'];
                  $valor = array_key_exists($sku, $sticky)
                      ? (string) $sticky[$sku]
                      : ($linea['contado'] === null ? '' : (string) $linea['contado']);
                ?>
                <tr>
                  <td><strong><?= e($sku) ?></strong></td>
                  <td><?= e((string) $linea['nombre']) ?></td>
                  <td><?= (int) $linea['sistema'] ?></td>
                  <td>
                    <input class="ops-input" type="number" min="0" max="9999" name="contado[<?= e($sku) ?>]" value="<?= e($valor) ?>" aria-label="Contado de <?= e($sku) ?>" />
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <label class="ops-check">
          <input type="checkbox" name="aplicar" value="1" checked />
          Al cerrar, aplicar el conteo físico a la góndola
        </label>
        <div class="ops-actions">
          <button type="submit" class="btn-mini ghost" name="accion" value="registrar">Guardar conteo</button>
          <button type="submit" class="btn-mini" name="accion" value="cerrar">Cerrar auditoría</button>
        </div>
      </form>
    <?php else: ?>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Producto</th>
              <th>Sistema</th>
              <th>Contado</th>
              <th>Diferencia</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($abierto['lineas'] as $linea): ?>
              <?php $dif = (int) $linea['contado'] - (int) $linea['sistema']; ?>
              <tr>
                <td><strong><?= e((string) $linea['sku']) ?></strong></td>
                <td><?= e((string) $linea['nombre']) ?></td>
                <td><?= (int) $linea['sistema'] ?></td>
                <td><?= (int) $linea['contado'] ?></td>
                <td><?= $dif > 0 ? '+' . $dif : (string) $dif ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </article>
<?php endif; ?>

<article class="admin-card">
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Conteo</th>
          <th>Pasillo</th>
          <th>Tipo</th>
          <th>Programado</th>
          <th>Avance</th>
          <th>Estado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if ($conteos === []): ?>
          <tr><td colspan="7"><p class="empty-note">No hay conteos con ese filtro.</p></td></tr>
        <?php endif; ?>
        <?php foreach ($conteos as $conteo): ?>
          <?php $resumen = mass_ops_conteo_resumen($conteo); ?>
          <tr>
            <td><strong><?= e((string) $conteo['id']) ?></strong></td>
            <td><?= e((string) $conteo['pasillo']) ?></td>
            <td><?= e((string) $conteo['tipo']) ?></td>
            <td><?= e((string) $conteo['programado']) ?></td>
            <td><?= count($conteo['lineas']) - (int) $resumen['pendientes'] ?> / <?= count($conteo['lineas']) ?></td>
            <td>
              <span class="status-pill <?= e($clases[(string) $conteo['estado']] ?? 'ok') ?>">
                <?= e($etiquetas[(string) $conteo['estado']] ?? (string) $conteo['estado']) ?>
              </span>
            </td>
            <td>
              <a class="btn-mini" href="conteos.php?<?= e(http_build_query(array_filter([
                  'estado' => $estado === 'todos' ? null : $estado,
                  'id' => (string) $conteo['id'],
              ], static fn ($v) => $v !== null && $v !== ''))) ?>">Abrir</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</article>

<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>
