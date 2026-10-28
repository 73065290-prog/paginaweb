<?php
declare(strict_types=1);

auth_requerir_proveedor();

$tituloProveedor = 'Mi catálogo SKU — Proveedor Mass';
$provActivo = 'catalogo';
$provCrumb = 'Catálogo de abastecimiento';
$skus = mass_proveedor_skus();

require __DIR__ . '/partials/header.php';
?>

<header class="prov-hero">
  <div>
    <h1>Mi catálogo SKU</h1>
    <p>Productos que tu empresa abastece a la red Mass: stock de planta, MOQ y lead time.</p>
  </div>
</header>

<div class="prov-table-wrap">
  <table class="prov-table">
    <thead>
      <tr><th>SKU / EAN</th><th>Producto</th><th>Precio lista</th><th>Stock planta</th><th>MOQ</th><th>Lead time</th><th>Estado</th></tr>
    </thead>
    <tbody>
      <?php foreach ($skus as $s): ?>
        <?php $est = (string) ($s['estado'] ?? 'activo'); ?>
        <tr>
          <td><strong><?= e((string) $s['sku']) ?></strong><div class="prov-detail"><?= e((string) $s['ean']) ?></div></td>
          <td><?= e((string) $s['nombre']) ?><div class="prov-detail"><?= e((string) $s['categoria']) ?></div></td>
          <td><?= e(dinero((float) $s['precio'])) ?></td>
          <td><?= number_format((int) $s['stock']) ?></td>
          <td><?= (int) $s['moq'] ?></td>
          <td><?= e((string) $s['lead']) ?></td>
          <td><span class="prov-badge is-<?= e($est) ?>"><?= e($est) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
