<?php
declare(strict_types=1);

/* bootstrap vía shim */

$error = '';
$rol = auth_normalizar_rol((string) ($_POST['rol'] ?? $_GET['rol'] ?? 'admin'));
$defaultsUsuario = [
    'admin' => 'admin.operaciones@mass.pe',
    'cliente' => 'cliente@mass.pe',
    'proveedor' => 'proveedor@mass.pe',
];
$usuarioVal = (string) ($_POST['usuario'] ?? ($defaultsUsuario[$rol] ?? 'admin.operaciones@mass.pe'));
$recordar = isset($_POST['recordar']);

$flashMsg = $flash ?? '';
$hints = [
    'admin' => 'Modo Administrador: acceso a WMS, góndolas, compras, mermas y auditoría de tiendas.',
    'cliente' => 'Modo Cliente: tienda Mass Online, catálogo, carrito y seguimiento de pedidos personales.',
    'proveedor' => 'Modo Proveedor: portal B2B para OC, catálogo SKU, despachos y entregas a tienda.',
];

// Si ya hay sesión del mismo rol, ir al destino; si es otro rol, cerrar para permitir el cambio
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && auth_usuario()) {
    $rolActual = (string) (auth_usuario()['rol'] ?? '');
    if ($rol === $rolActual) {
        redirect(auth_destino_post_login($rolActual));
    }
    auth_logout();
    $flashMsg = 'Sesión anterior cerrada. Inicia sesión con el rol seleccionado.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validar(isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : null)) {
        $error = 'Sesión inválida. Recarga la página e intenta de nuevo.';
    } else {
        auth_logout();
        $rol = auth_normalizar_rol((string) ($_POST['rol'] ?? 'admin'));
        $resultado = auth_intentar(
            (string) ($_POST['usuario'] ?? ''),
            (string) ($_POST['password'] ?? ''),
            $rol
        );

        if ($resultado['ok'] && $resultado['usuario']) {
            auth_login($resultado['usuario'], $recordar);
            flash_set('Bienvenido, ' . $resultado['usuario']['nombre']);
            redirect(auth_destino_post_login((string) $resultado['usuario']['rol']));
        }

        $error = implode(' · ', $resultado['errores']);
        $usuarioVal = (string) ($_POST['usuario'] ?? '');
    }
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
        <h1>Tres accesos, un ecosistema Mass</h1>
        <p>Administra tienda (WMS), compra como cliente online o abastece como proveedor B2B — cada rol con su propio portal.</p>
        <div class="promo-metric">
          <strong>Roles: Admin · Cliente · Proveedor</strong>
          <span>Separados y funcionales</span>
        </div>
        <div class="promo-chip">SGC-Mass + Mass Online + Portal Proveedores</div>
      </div>
      <div class="promo-mid">Red Supermercados Peruanos · Mass Core</div>
      <figure class="promo-photo">
        <img src="https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=900&h=500&fit=crop" alt="Interior tienda Mass" />
        <figcaption>Operación, compra y abastecimiento en una sola plataforma demo</figcaption>
      </figure>
    </section>

    <section class="login-panel">
      <header class="login-brand">
        <div class="brand-row">
          <span class="brand-mark">MASS</span>
          <div>
            <strong>SGC-Mass v4.2 PRO</strong>
            <span>Acceso por rol</span>
          </div>
        </div>
        <span class="secure-pill">🔒 Acceso Seguro</span>
      </header>

      <h2>Iniciar Sesión</h2>
      <p class="login-sub">Elige tu rol y entra al portal correspondiente</p>

      <?php if ($flashMsg): ?>
        <div class="login-alert"><?= e($flashMsg) ?></div>
      <?php endif; ?>
      <?php if ($error !== ''): ?>
        <div class="login-alert is-error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" class="login-form" id="loginForm" novalidate>
        <?= csrf_field() ?>
        <div class="role-switch role-switch--3" role="tablist" aria-label="Tipo de acceso">
          <label class="role-tab <?= $rol === 'admin' ? 'is-active' : '' ?>">
            <input type="radio" name="rol" value="admin" <?= $rol === 'admin' ? 'checked' : '' ?> />
            <span class="role-ico" aria-hidden="true">🛡</span>
            <span>Admin WMS</span>
          </label>
          <label class="role-tab <?= $rol === 'cliente' ? 'is-active' : '' ?>">
            <input type="radio" name="rol" value="cliente" <?= $rol === 'cliente' ? 'checked' : '' ?> />
            <span class="role-ico" aria-hidden="true">🛒</span>
            <span>Cliente</span>
          </label>
          <label class="role-tab <?= $rol === 'proveedor' ? 'is-active' : '' ?>">
            <input type="radio" name="rol" value="proveedor" <?= $rol === 'proveedor' ? 'checked' : '' ?> />
            <span class="role-ico" aria-hidden="true">🏭</span>
            <span>Proveedor</span>
          </label>
        </div>
        <p class="role-hint" id="roleHint"><?= e($hints[$rol] ?? $hints['admin']) ?></p>

        <label class="field">
          <span>Correo electrónico o usuario corporativo</span>
          <div class="input-wrap">
            <span class="input-ico" aria-hidden="true">👤</span>
            <input
              type="text"
              name="usuario"
              id="usuario"
              value="<?= e($usuarioVal) ?>"
              placeholder="<?= e($defaultsUsuario[$rol] ?? 'usuario') ?>"
              autocomplete="username"
              required
            />
          </div>
        </label>

        <label class="field">
          <span class="field-row">
            Contraseña
            <a href="#" class="forgot" onclick="alert('Demo: contraseña 123456 para todos los roles'); return false;">¿Olvidaste tu contraseña?</a>
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

        <button type="submit" class="btn-login">Iniciar Sesión →</button>
      </form>

      <aside class="login-support">
        <h3>Credenciales demo</h3>
        <p class="demo-hint">
          Admin: <code>admin</code> / <code>123456</code><br />
          Cliente: <code>cliente</code> / <code>123456</code><br />
          Proveedor: <code>proveedor</code> / <code>123456</code>
        </p>
        <div class="support-foot">
          <span>Supermercados Peruanos S.A.</span>
          <span class="ssl">SSL 256-Bit</span>
        </div>
      </aside>
    </section>
  </main>
  <script src="<?= e(mass_asset('assets/js/admin-login.js')) ?>"></script>
</body>
</html>
