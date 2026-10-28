<?php
declare(strict_types=1);

$resumen = pedido_resumen();
?>
<aside class="checkout-summary is-sticky" aria-label="Resumen del pedido">
  <h2>Resumen de tu pedido</h2>
  <dl class="sum-lines">
    <div><dt>Subtotal (<?= (int) $resumen['items'] ?> un.)</dt><dd><?= e(dinero($resumen['subtotal'])) ?></dd></div>
    <?php if ($resumen['ahorroOfertas'] > 0): ?>
    <div class="is-save"><dt>Ahorro ofertas</dt><dd>−<?= e(dinero($resumen['ahorroOfertas'])) ?></dd></div>
    <?php endif; ?>
    <?php if ($resumen['descuentoCupon'] > 0): ?>
    <div class="is-save"><dt>Cupón <?= e($resumen['cupon']) ?></dt><dd>−<?= e(dinero($resumen['descuentoCupon'])) ?></dd></div>
    <?php endif; ?>
    <div>
      <dt>Entrega</dt>
      <dd><?= $resumen['envio'] > 0 ? e(dinero($resumen['envio'])) : 'GRATIS' ?></dd>
    </div>
  </dl>
  <?php if ($resumen['ahorroTotal'] > 0): ?>
  <div class="sum-ahorro">Ahorro total: <strong><?= e(dinero($resumen['ahorroTotal'])) ?></strong></div>
  <?php endif; ?>
  <div class="sum-total">
    <span>Total a pagar</span>
    <strong><?= e(dinero($resumen['total'])) ?></strong>
  </div>
</aside>
