<?php
declare(strict_types=1);
$items = carrito_items();
?>
<div class="overlay" id="overlay"></div>
<aside class="cart-drawer" id="cartDrawer" aria-label="Carrito">
  <div class="cart-head">
    <h3>Tu carrito</h3>
    <button class="close-btn" id="closeCart" type="button" aria-label="Cerrar">×</button>
  </div>
  <div class="cart-items" id="cartItems">
    <?php if (!$items): ?>
      <div class="empty">Tu carrito está vacío.</div>
    <?php else: ?>
      <?php foreach ($items as $item): ?>
        <?php $p = producto_por_id((int) $item['id']); ?>
        <?php if (!$p) { continue; } ?>
        <div class="cart-item">
          <img src="<?= e($p['img']) ?>" alt="" />
          <div>
            <h4><?= e($p['nombre']) ?></h4>
            <p><?= e(dinero((float) $p['precio'])) ?> c/u · x<?= (int) $item['cantidad'] ?></p>
            <div class="qty" style="margin-top:0.3rem">
              <form method="post" action="carrito.php" style="display:inline">
                <input type="hidden" name="accion" value="cambiar" />
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                <input type="hidden" name="delta" value="-1" />
                <input type="hidden" name="volver" value="<?= e($_SERVER['REQUEST_URI'] ?? 'index.php') ?>" />
                <button type="submit">−</button>
              </form>
              <span><?= (int) $item['cantidad'] ?></span>
              <form method="post" action="carrito.php" style="display:inline">
                <input type="hidden" name="accion" value="cambiar" />
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                <input type="hidden" name="delta" value="1" />
                <input type="hidden" name="volver" value="<?= e($_SERVER['REQUEST_URI'] ?? 'index.php') ?>" />
                <button type="submit">+</button>
              </form>
            </div>
          </div>
          <strong><?= e(dinero((float) $p['precio'] * (int) $item['cantidad'])) ?></strong>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="cart-foot">
    <div class="cart-total">
      <span>Total</span>
      <span id="cartTotal"><?= e(dinero(carrito_total_monto())) ?></span>
    </div>
    <a class="btn btn-naranja" href="pedido.php" style="width: 100%; display: inline-block; text-align: center; text-decoration: none;">
      Ir al checkout
    </a>
  </div>
</aside>
