<?php
declare(strict_types=1);

/* bootstrap vía shim */
auth_requerir_admin();
require_once MASS_ROOT . '/includes/admin_operaciones.php';

$tituloAdmin = 'Configuración del sistema — Mass Admin';
$adminActivo = 'config';
$adminCrumb = 'Configuración del Sistema';

$errores = [];
$cfg = mass_ops_config();
$sticky = [
    'umbralBajo' => (string) $cfg['umbralBajo'],
    'umbralCriticoPiso' => (string) $cfg['umbralCriticoPiso'],
    'diasCaducidad' => (string) $cfg['diasCaducidad'],
    'slaPickingMin' => (string) $cfg['slaPickingMin'],
    'alertasActivas' => $cfg['alertasActivas'] ? '1' : '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sticky = [
        'umbralBajo' => trim((string) ($_POST['umbralBajo'] ?? '')),
        'umbralCriticoPiso' => trim((string) ($_POST['umbralCriticoPiso'] ?? '')),
        'diasCaducidad' => trim((string) ($_POST['diasCaducidad'] ?? '')),
        'slaPickingMin' => trim((string) ($_POST['slaPickingMin'] ?? '')),
        'alertasActivas' => (string) ($_POST['alertasActivas'] ?? ''),
    ];
    if (!csrf_validar(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
        $errores[] = 'Sesión inválida. Recarga la página e intenta de nuevo.';
    } else {
        $resultado = mass_ops_config_guardar($sticky);
        if ($resultado['ok']) {
            flash_set('Configuración del sistema guardada. Los umbrales ya aplican a góndola, caducidad y picking.');
            redirect('configuracion.php');
        }
        $errores = $resultado['errores'];
    }
}

$cfg = mass_ops_config();
$stock = ['ok' => 0, 'bajo' => 0, 'critico' => 0];
foreach (mass_ops_inventario() as $row) {
    $estado = (string) $row['estado'];
    if (isset($stock[$estado])) {
        $stock[$estado]++;
    }
}
$porVencer = 0;
foreach (mass_ops_mermas() as $merma) {
    if (mass_ops_merma_clase($merma) === 'por_vencer' && (string) $merma['estado'] === 'abierta') {
        $porVencer++;
    }
}

require MASS_ROOT . '/frontend/views/admin/partials/header.php';
?>

<div class="page-toolbar">
  <div>
    <h1 class="admin-title" style="margin:0">Configuración del sistema</h1>
    <p class="dash-sub">Umbrales de góndola, ventana de caducidad y SLA de recolección de esta sucursal. Se guardan en la sesión operativa.</p>
  </div>
</div>

<?php if (($flash ?? '') !== ''): ?>
  <div class="prod-toast prod-toast--page" role="status"><?= e((string) $flash) ?></div>
<?php endif; ?>
<?php if ($errores !== []): ?>
  <div class="ops-alert" role="alert">
    <strong>Revisa la configuración</strong>
    <ul>
      <?php foreach ($errores as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<section class="kpi-grid" style="grid-template-columns: repeat(3, 1fr)">
  <article class="kpi-card">
    <h3>Góndola crítica</h3>
    <p class="kpi-value"><?= (int) $stock['critico'] ?></p>
    <p class="kpi-meta"><?= (int) $stock['bajo'] ?> SKUs bajo el umbral</p>
  </article>
  <article class="kpi-card">
    <h3>Por vencer</h3>
    <p class="kpi-value"><?= $porVencer ?></p>
    <p class="kpi-meta">Dentro de <?= (int) $cfg['diasCaducidad'] ?> días</p>
  </article>
  <article class="kpi-card">
    <h3>SLA picking</h3>
    <p class="kpi-value"><?= (int) $cfg['slaPickingMin'] ?> <small>min</small></p>
    <p class="kpi-meta"><?= $cfg['alertasActivas'] ? 'Alertas activas' : 'Alertas apagadas' ?></p>
  </article>
</section>

<article class="admin-card">
  <h2>Parámetros operativos</h2>
  <form method="post" action="configuracion.php" class="ops-form">
    <?= csrf_field() ?>
    <label>Umbral de stock bajo (góndola)
      <input type="number" name="umbralBajo" min="1" max="200" required value="<?= e($sticky['umbralBajo']) ?>" />
    </label>
    <label>Umbral crítico de piso
      <input type="number" name="umbralCriticoPiso" min="0" max="50" required value="<?= e($sticky['umbralCriticoPiso']) ?>" />
    </label>
    <label>Alerta de caducidad (días)
      <input type="number" name="diasCaducidad" min="1" max="90" required value="<?= e($sticky['diasCaducidad']) ?>" />
    </label>
    <label>SLA de picking (minutos)
      <input type="number" name="slaPickingMin" min="1" max="180" required value="<?= e($sticky['slaPickingMin']) ?>" />
    </label>
    <label class="ops-check">
      <input type="checkbox" name="alertasActivas" value="1" <?= $sticky['alertasActivas'] === '1' ? 'checked' : '' ?> />
      Mostrar alertas de SLA en recolección
    </label>
    <div class="ops-actions">
      <button type="submit" class="btn-mini">Guardar configuración</button>
    </div>
  </form>
</article>

<?php require MASS_ROOT . '/frontend/views/admin/partials/footer.php'; ?>
