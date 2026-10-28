<?php
declare(strict_types=1);

/* bootstrap vía shim */

$error = '';
$rol = (string) ($_POST['rol'] ?? $_GET['rol'] ?? 'admin');
$rol = $rol === 'cliente' ? 'cliente' : 'admin';
$usuarioVal = (string) ($_POST['usuario'] ?? ($rol === 'cliente' ? 'cliente' : 'admin.operaciones@mass.pe'));
$recordar = isset($_POST['recordar']);

$flashMsg = $flash ?? '';

// Si ya hay sesión del mismo rol, ir al destino; si es otro rol, cerrar para permitir el cambio
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && auth_usuario()) {
    if ($rol === 'admin' && auth_es_admin()) {
        redirect('index.php');
    }
    if ($rol === 'cliente' && auth_es_cliente()) {
        redirect('../cliente_interfaces/index.php');
    }
    // Cambio admin ↔ cliente: cerrar sesión actual antes de mostrar el login
    auth_logout();
    $flashMsg = 'Sesión anterior cerrada. Inicia sesión con el rol seleccionado.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CsrfMiddleware::requireValid();
    // Al enviar login siempre reemplaza la sesión previa (cambio de administrador/cliente)
    auth_logout();
    $resultado = auth_intentar(
        (string) ($_POST['usuario'] ?? ''),
        (string) ($_POST['password'] ?? ''),
        $rol
    );

    if ($resultado['ok'] && $resultado['usuario']) {
        auth_login($resultado['usuario'], $recordar);
        flash_set('Bienvenido, ' . $resultado['usuario']['nombre']);
        if (($resultado['usuario']['rol'] ?? '') === 'cliente') {
            redirect('../cliente_interfaces/pedido.php?demo=1');
        }
        redirect('index.php');
    }

    $error = implode(' · ', $resultado['errores']);
    $usuarioVal = (string) ($_POST['usuario'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar Sesión — SGC-Mass v4.2 PRO</title>
  <link rel="icon" href="<?= e(mass_asset('assets/img/logo.svg')) ?>" type="image/svg+xml" />
  <link rel="stylesheet" href="<?= e(mass_asset('assets/css/tokens.css')) ?>" />
  <link rel="stylesheet" href="<?= e(mass_asset('assets/css/admin-login.css')) ?>" />
</head>
<body class="login-body">
  <main class="login-shell">
    <section class="login-promo" aria-label="Información SGC Mass">
      <div class="promo-top">
        <span class="promo-badge">Consumo consciente</span>
        <h1>Control Inteligente de Inventarios &amp; Góndolas</h1>
        <p>Plataforma centralizada de Tiendas Mass para la optimización de reposición, picking y auditoría de stock en tiempo real.</p>
        <div class="promo-metric">
          <strong>Índice Merma Crítica: 0.94%</strong>
          <span>Meta &lt; 1.2%</span>
        </div>
        <div class="promo-chip">Monitoreo PEPS en tiempo real</div>
      </div>
      <div class="promo-mid">Red Supermercados Peruanos · Mass Core</div>
      <figure class="promo-photo">
        <img src="https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=900&h=500&fit=crop" alt="Interior tienda Mass" />
        <figcaption>Auditoría de stock con precisión de escaneo 99.8%</figcaption>
      </figure>
    </section>

    <section class="login-panel">
      <header class="login-brand">
        <div class="brand-row">
          <span class="brand-mark">MASS</span>
          <div>
            <strong>SGC-Mass v4.2 PRO</strong>
            <span>Plataforma de Optimización Comercial</span>
          </div>
        </div>
        <span class="secure-pill">🔒 Acceso Seguro</span>
      </header>

      <h2>Iniciar Sesión</h2>
      <p class="login-sub">Accede a la plataforma de gestión comercial e inventarios inteligentes</p>

      <?php if ($flashMsg): ?>
        <div class="login-alert"><?= e($flashMsg) ?></div>
      <?php endif; ?>
      <?php if ($error !== ''): ?>
        <div class="login-alert is-error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" class="login-form" id="loginForm" novalidate>
        <?= csrf_field() ?>
        <div class="role-switch" role="tablist" aria-label="Tipo de acceso">
          <label class="role-tab <?= $rol === 'admin' ? 'is-active' : '' ?>">
            <input type="radio" name="rol" value="admin" <?= $rol === 'admin' ? 'checked' : '' ?> />
            <span class="role-ico" aria-hidden="true">🛡</span>
            <span>Administrador WMS</span>
          </label>
          <label class="role-tab <?= $rol === 'cliente' ? 'is-active' : '' ?>">
            <input type="radio" name="rol" value="cliente" <?= $rol === 'cliente' ? 'checked' : '' ?> />
            <span class="role-ico" aria-hidden="true">📦</span>
            <span>Cliente / Proveedor Pedidos</span>
          </label>
        </div>
        <p class="role-hint" id="roleHint">
          <?= $rol === 'admin'
            ? 'Modo Administrador: Acceso a WMS, góndolas, compras, mermas y auditoría de tiendas.'
            : 'Modo Cliente/Proveedor: Acceso al portal de pedidos y seguimiento de entregas.' ?>
        </p>

        <label class="field">
          <span>Correo electrónico o usuario corporativo</span>
          <div class="input-wrap">
            <span class="input-ico" aria-hidden="true">👤</span>
            <input
              type="text"
              name="usuario"
              id="usuario"
              value="<?= e($usuarioVal) ?>"
              placeholder="admin.operaciones@mass.pe"
              autocomplete="username"
              required
            />
          </div>
        </label>

        <label class="field">
          <span class="field-row">
            Contraseña
            <a href="#" class="forgot" onclick="alert('Demo: usa MassAdmin24 o Proveedor24'); return false;">¿Olvidaste tu contraseña?</a>
          </span>
          <div class="input-wrap">
            <span class="input-ico" aria-hidden="true">🔒</span>
            <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="current-password" required />
            <button type="button" class="toggle-pass" id="togglePass" aria-label="Mostrar contraseña">👁</button>
          </div>
        </label>

        <div class="form-extras">
          <label class="check">
            <input type="checkbox" name="recordar" value="1" <?= $recordar ? 'checked' : '' ?> />
            Recordar mi sesión en este equipo
          </label>
          <span class="mfa-pill">🛡 MFA Activo</span>
        </div>

        <button type="submit" class="btn-login">Iniciar Sesión en Mass SGC →</button>
      </form>

      <aside class="login-support">
        <h3>Soporte &amp; Auditoría</h3>
        <p>¿Problemas de ingreso? Mesa de Ayuda TI: Anexo 4402</p>
        <div class="support-foot">
          <span>Supermercados Peruanos S.A.</span>
          <span class="ssl">SSL 256-Bit</span>
        </div>
        <p class="demo-hint">
          Demo admin: <code>admin</code> / <code>123456</code><br />
          Demo cliente: <code>cliente</code> / <code>123456</code>
        </p>
      </aside>
    </section>
  </main>
  <script src="<?= e(mass_asset('assets/js/admin-login.js')) ?>"></script>
</body>
</html>

