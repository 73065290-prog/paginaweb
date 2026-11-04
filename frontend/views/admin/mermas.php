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
    }
}

if (!in_array($filtro, ['todas', 'por_vencer', 'vencidas', 'merma', 'retiradas'], true)) {
    $filtro = 'todas';
}

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
            default => true,
        };
    }
));

$clasePills = [
    'vencida' => 'critico',
    'por_vencer' => 'vencer',
    'vigente' => 'ok',
    'merma' => 'bajo',
];

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<div class="page-toolbar">
  <div>
    <h1 class="admin-title" style="margin:0">Mermas y caducidades</h1>
    <p class="dash-sub">Registra desmedro o lotes por vencer. Retirar descuenta las unidades de la góndola si todavía no se habían bajado del stock.</p>
  </div>
  <div class="filters-inline">
    <a href="mermas.php" class="<?= $filtro === 'todas' ? 'is-active' : '' ?>">Todas</a>
    <a href="mermas.php?filtro=por_vencer" class="<?= $filtro === 'por_vencer' ? 'is-active' : '' ?>">Por vencer</a>
    <a href="mermas.php?filtro=vencidas" class="<?= $filtro === 'vencidas' ? 'is-active' : '' ?>">Vencidas</a>
    <a href="mermas.php?filtro=merma" class="<?= $filtro === 'merma' ? 'is-active' : '' ?>">Mermas</a>
    <a href="mermas.php?filtro=retiradas" class="<?= $filtro === 'retiradas' ? 'is-active' : '' ?>">Retiradas</a>
  </div>
</div>

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
              <?php if ((string) $merma['estado'] === 'abierta'): ?>
                <form method="post" class="inline-form" action="mermas.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="accion" value="retirar" />
                  <input type="hidden" name="id" value="<?= e((string) $merma['id']) ?>" />
                  <input type="hidden" name="filtro" value="<?= e($filtro) ?>" />
                  <button type="submit" class="btn-mini warn">Retirar de góndola</button>
                </form>
              <?php else: ?>
                <span class="status-pill ok">Stock descontado</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</article>

<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>
