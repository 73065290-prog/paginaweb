<?php
declare(strict_types=1);

auth_requerir_proveedor();

$tituloProveedor = 'Dashboard Proveedor — Mass B2B';
$provActivo = 'dashboard';
$provCrumb = 'Dashboard de abastecimiento';

$perfil = mass_proveedor_perfil();
$kpis = mass_proveedor_kpis();
$pendientes = mass_proveedor_ordenes('pendiente');
$entregas = array_values(array_filter(
    mass_proveedor_entregas(),
    static fn (array $e): bool => in_array($e['estado'] ?? '', ['en_ruta', 'programada'], true)
));

require __DIR__ . '/partials/header.php';
?>

<header class="prov-hero">
  <div>
    <h1>Hola, <?= e($perfil['nombre']) ?></h1>
    <p>Gestiona órdenes de compra de Tiendas Mass, confirma despachos y monitorea entregas a tienda.</p>
    <div class="prov-chip-row">
      <span class="prov-chip"><?= e($perfil['categoria']) ?></span>
      <span class="prov-chip is-orange">Fill rate <?= e(number_format((float) $kpis['fillRate'], 1)) ?>%</span>
      <span class="prov-chip">SLA cumplido <?= e(number_format((float) $kpis['slaCumplido'], 1)) ?>%</span>
    </div>
  </div>
</header>

<section class="kpi-grid kpi-grid--8" aria-label="Indicadores del proveedor">
  <article class="kpi-card is-warn">
    <h3>OC pendientes</h3>
    <p class="kpi-value"><?= (int) $kpis['ocPendientes'] ?></p>
    <p class="kpi-meta">Requieren confirmación</p>
  </article>
  <article class="kpi-card">
    <h3>OC confirmadas</h3>
    <p class="kpi-value"><?= (int) $kpis['ocConfirmadas'] ?></p>
    <p class="kpi-meta">Listas para despacho</p>
  </article>
  <article class="kpi-card">
    <h3>Entregas en ruta</h3>
    <p class="kpi-value"><?= (int) $kpis['entregasRuta'] ?></p>
    <p class="kpi-meta">Hacia tiendas Mass</p>
  </article>
  <article class="kpi-card">
    <h3>SKUs activos</h3>
    <p class="kpi-value"><?= (int) $kpis['skusActivos'] ?></p>
    <p class="kpi-meta">Catálogo abastecido</p>
  </article>
  <article class="kpi-card">
    <h3>Monto del mes</h3>
    <p class="kpi-value"><?= e(dinero((float) $kpis['montoMes'])) ?></p>
    <p class="kpi-meta">OC confirmadas / en curso</p>
  </article>
</section>

<section class="admin-panel" style="margin-top:1.25rem">
  <header class="dash-head" style="margin-bottom:0.75rem">
    <div>
      <h2 class="admin-title" style="font-size:1.1rem">Órdenes pendientes</h2>
      <p class="dash-sub">Confirma o rechaza para liberar el flujo en tienda.</p>
    </div>
    <a class="prov-btn is-ghost" href="ordenes.php?estado=pendiente">Ver todas</a>
  </header>
  <div class="prov-table-wrap">
    <?php if (!$pendientes): ?>
      <div class="prov-empty">No hay OC pendientes.</div>
    <?php else: ?>
      <table class="prov-table">
        <thead>
          <tr><th>OC</th><th>Tienda</th><th>Entrega</th><th>Unidades</th><th>Total</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($pendientes, 0, 4) as $o): ?>
            <tr>
              <td><strong><?= e((string) $o['id']) ?></strong><div class="prov-detail"><?= (int) $o['items'] ?> ítems</div></td>
              <td><?= e((string) $o['tienda']) ?></td>
              <td><?= e((string) $o['entrega']) ?></td>
              <td><?= (int) $o['unidades'] ?></td>
              <td><?= e(dinero((float) $o['total'])) ?></td>
              <td><a class="prov-btn is-ghost" href="ordenes.php?estado=pendiente#<?= e((string) $o['id']) ?>">Gestionar</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<section class="admin-panel" style="margin-top:1.25rem">
  <header class="dash-head" style="margin-bottom:0.75rem">
    <div>
      <h2 class="admin-title" style="font-size:1.1rem">Próximas entregas</h2>
      <p class="dash-sub">Ventanas de recepción en sedes Mass.</p>
    </div>
    <a class="prov-btn is-ghost" href="entregas.php">Calendario</a>
  </header>
  <div class="prov-table-wrap">
    <?php if (!$entregas): ?>
      <div class="prov-empty">Sin entregas programadas o en ruta.</div>
    <?php else: ?>
      <table class="prov-table">
        <thead>
          <tr><th>Guía</th><th>OC</th><th>Tienda</th><th>Fecha / ventana</th><th>Estado</th></tr>
        </thead>
        <tbody>
          <?php foreach ($entregas as $e): ?>
            <tr>
              <td><strong><?= e((string) $e['id']) ?></strong></td>
              <td><?= e((string) $e['oc']) ?></td>
              <td><?= e((string) $e['tienda']) ?></td>
              <td><?= e((string) $e['fecha']) ?><div class="prov-detail"><?= e((string) $e['ventana']) ?></div></td>
              <td><span class="prov-badge is-<?= e((string) $e['estado']) ?>"><?= e((string) $e['estado']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
