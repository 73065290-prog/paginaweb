<?php
declare(strict_types=1);

/** @var array $tienda */
$tienda = $tienda ?? [];
$estado = (string) ($tienda['estado'] ?? 'ok');
$stock = (int) ($tienda['stock'] ?? 0);
$agotado = $estado === 'agotado' || $stock <= 0;
?>
<article class="store-card <?= $agotado ? 'is-agotado' : '' ?> <?= $estado === 'actual' ? 'is-actual' : '' ?>">
  <div class="store-card-top">
    <h3><?= e($tienda['nombre'] ?? '') ?></h3>
    <?php if (!empty($tienda['estadoLabel'])): ?>
      <span class="store-pill tone-<?= e($estado) ?>"><?= e($tienda['estadoLabel']) ?></span>
    <?php endif; ?>
  </div>
  <p class="store-addr"><?= e($tienda['direccion'] ?? '') ?></p>
  <div class="store-meta">
    <span class="store-dist"><?= e($tienda['distancia'] ?? '') ?></span>
    <span class="store-stock tone-<?= e($estado) ?>">
      <?= $agotado ? 'Sin stock' : ($stock . ' unidades') ?>
    </span>
  </div>
  <?php if ($agotado): ?>
    <span class="store-choose is-disabled">No disponible</span>
  <?php else: ?>
    <button type="button" class="store-choose">Elegir tienda</button>
  <?php endif; ?>
</article>
