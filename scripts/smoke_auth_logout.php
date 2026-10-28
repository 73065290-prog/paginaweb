<?php
declare(strict_types=1);

/**
 * Smoke funcional: login admin → logout → login cliente + URLs de navbar.
 */
$_SERVER['SCRIPT_NAME'] = '/paginaweb/cliente_interfaces/index.php';
require dirname(__DIR__) . '/includes/init.php';

$ok = true;
$log = [];

$r = auth_intentar('admin', '123456', 'admin');
if (!$r['ok']) {
    $ok = false;
    $log[] = 'FAIL login admin';
} else {
    auth_login($r['usuario']);
    $log[] = 'OK login admin';
}

$logoutUrl = mass_url('logout.php');
$assetCss = mass_asset('assets/css/styles.css');
$bad = mass_asset('logout.php');
if ($logoutUrl !== '../logout.php' || str_contains($bad, 'frontend/assets/logout')) {
    $ok = false;
    $log[] = "FAIL urls logout='$logoutUrl' asset_logout='$bad'";
} else {
    $log[] = "OK urls logout='$logoutUrl' css='$assetCss'";
}

auth_logout();
if (auth_usuario() !== null) {
    $ok = false;
    $log[] = 'FAIL logout no limpia';
} else {
    $log[] = 'OK logout limpia sesión';
}

$c = auth_intentar('cliente', '123456', 'cliente');
if (!$c['ok']) {
    $ok = false;
    $log[] = 'FAIL login cliente tras logout';
} else {
    auth_login($c['usuario']);
    $log[] = auth_es_cliente() && !auth_es_admin() ? 'OK cambio a cliente' : 'FAIL rol tras cambio';
    if (!auth_es_cliente() || auth_es_admin()) {
        $ok = false;
    }
}

foreach ($log as $line) {
    echo $line . PHP_EOL;
}
echo ($ok ? 'SMOKE_AUTH_OK' : 'SMOKE_AUTH_FAIL') . PHP_EOL;
exit($ok ? 0 : 1);
