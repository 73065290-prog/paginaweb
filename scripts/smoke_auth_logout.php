<?php
declare(strict_types=1);

/**
 * Smoke funcional PROMPT 03 (INT-005):
 * - Escenario A: Logout desde admin redirige a admin/login.php
 * - Escenario B: Logout desde cliente redirige a cliente_interfaces/index.php
 * - Escenario C: Cambio de rol cierra la sesión anterior y establece la nueva sesión
 * - Uso correcto de mass_url() (páginas) y mass_asset() (CSS/JS/IMG)
 */
$_SERVER['SCRIPT_NAME'] = '/paginaweb/cliente_interfaces/index.php';
require dirname(__DIR__) . '/includes/init.php';

$ok = true;
$log = [];

// 1. Verificar separación mass_url() y mass_asset()
$logoutUrl = mass_url('logout.php');
$adminLoginUrl = mass_url('admin/login.php');
$assetCss = mass_asset('assets/css/styles.css');
$assetJs = mass_asset('assets/js/admin-login.js');
$assetImg = mass_asset('assets/img/logo.svg');
$bad = mass_asset('logout.php');
if (
    $logoutUrl !== '../logout.php'
    || $adminLoginUrl !== '../admin/login.php'
    || !str_contains($assetCss, 'frontend/assets/css/styles.css')
    || !str_contains($assetJs, 'frontend/assets/js/admin-login.js')
    || !str_contains($assetImg, 'frontend/assets/img/logo.svg')
    || str_contains($bad, 'frontend/assets/logout')
) {
    $ok = false;
    $log[] = "FAIL urls logout='$logoutUrl' asset_logout='$bad'";
} else {
    $log[] = "OK urls logout='$logoutUrl' admin='$adminLoginUrl' css='$assetCss'";
}

// 2. Escenario A: Logout desde administrador -> admin/login.php
$r = auth_intentar('admin', '123456', 'admin');
if (!$r['ok']) {
    $ok = false;
    $log[] = 'FAIL Escenario A: login admin';
} else {
    auth_login($r['usuario']);
    $eraAdmin = auth_es_admin();
    auth_logout();
    $destAdmin = auth_logout_destino($eraAdmin);
    if (auth_usuario() !== null || $destAdmin !== 'admin/login.php') {
        $ok = false;
        $log[] = "FAIL Escenario A: dest='$destAdmin'";
    } else {
        $log[] = "OK Escenario A: logout admin limpia sesión y redirige a '$destAdmin'";
    }
}

// 3. Escenario B: Logout desde cliente -> cliente_interfaces/index.php
$c = auth_intentar('cliente', '123456', 'cliente');
if (!$c['ok']) {
    $ok = false;
    $log[] = 'FAIL Escenario B: login cliente';
} else {
    auth_login($c['usuario']);
    $eraAdmin = auth_es_admin();
    auth_logout();
    $destCliente = auth_logout_destino($eraAdmin);
    if (auth_usuario() !== null || $destCliente !== 'cliente_interfaces/index.php') {
        $ok = false;
        $log[] = "FAIL Escenario B: dest='$destCliente'";
    } else {
        $log[] = "OK Escenario B: logout cliente limpia sesión y redirige a '$destCliente'";
    }
}

// 4. Escenario C: Cambio de rol (admin -> cliente y cliente -> admin)
$r1 = auth_intentar('admin', '123456', 'admin');
auth_login($r1['usuario']);
$adminOk = auth_es_admin();
auth_logout();
$c1 = auth_intentar('cliente', '123456', 'cliente');
auth_login($c1['usuario']);
$cambioAClienteOk = $adminOk && auth_es_cliente() && !auth_es_admin() && (auth_usuario()['rol'] ?? '') === 'cliente';

auth_logout();
$r2 = auth_intentar('admin', '123456', 'admin');
auth_login($r2['usuario']);
$cambioAAdminOk = $cambioAClienteOk && auth_es_admin() && !auth_es_cliente() && (auth_usuario()['rol'] ?? '') === 'admin';

if (!$cambioAClienteOk || !$cambioAAdminOk) {
    $ok = false;
    $log[] = 'FAIL Escenario C: cambio de rol admin <-> cliente';
} else {
    $log[] = 'OK Escenario C: cambio de rol cierra sesión anterior y establece la nueva (admin -> cliente -> admin)';
}

foreach ($log as $line) {
    echo $line . PHP_EOL;
}
echo ($ok ? 'SMOKE_AUTH_OK' : 'SMOKE_AUTH_FAIL') . PHP_EOL;
exit($ok ? 0 : 1);
