<?php
declare(strict_types=1);

auth_requerir_proveedor();

$tituloProveedor = 'Entregas a tienda — Proveedor Mass';
$provActivo = 'entregas';
$provCrumb = 'Calendario de entregas';
$entregas = mass_proveedor_entregas();

require __DIR__ . '/partials/header.php';
?>

<header class="prov-hero">
  <div>
    <h1>Entregas a tiendas Mass</h1>
    <p>Ventanas de recepción, chofer asignado y estado de cada guía.</p>
  </div>
</header>

<div class="prov-table-wrap">
  <table class="prov-table">
    <thead>
      <tr><th>Guía</th><th>OC</th><th>Tienda</th><th>Fecha</th><th>Ventana</th><th>Transporte</th><th>Estado</th></tr>
    </thead>
    <tbody>
      <?php foreach ($entregas as $e): ?>
        <?php $est = (string) ($e['estado'] ?? ''); ?>
        <tr>
          <td><strong><?= e((string) $e['id']) ?></strong></td>
          <td><?= e((string) $e['oc']) ?></td>
          <td><?= e((string) $e['tienda']) ?></td>
          <td><?= e((string) $e['fecha']) ?></td>
          <td><?= e((string) $e['ventana']) ?></td>
          <td><?= e((string) $e['chofer']) ?><div class="prov-detail">Placa <?= e((string) $e['placa']) ?></div></td>
          <td><span class="prov-badge is-<?= e($est) ?>"><?= e($est) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
