<?php
declare(strict_types=1);

/** @var array $p */
/** @var array $item */
$p = $p ?? [];
$item = $item ?? [];
$qty = max(1, (int) ($item['cantidad'] ?? 1));
$precio = (float) ($p['precio'] ?? 0);
$antes = !empty($p['precioAntes']) ? (float) $p['precioAntes'] : null;
$subtotal = $precio * $qty;
$ahorroUn = ($antes !== null && $antes > $precio) ? ($antes - $precio) : 0.0;
$tope = !empty($p['tope']) || $qty >= 3 && (int) ($p['id'] ?? 0) === 2;
$badge = $p['badge'] ?? null;
$volver = 'pedido.php';
$cartCheckId = 'cart-sel-' . (int) ($p['id'] ?? 0);
$cartCheckLabel = 'Seleccionar ' . (string) ($p['nombre'] ?? 'producto');
?>
<article class="cart-line">
  <label class="cart-check" for="<?= e($cartCheckId) ?>">
    <span class="sr-only"><?= e($cartCheckLabel) ?></span>
    <input id="<?= e($cartCheckId) ?>" type="checkbox" checked name="sel[]" value="<?= (int) $p['id'] ?>" />
  </label>
  <div class="cart-line-media">
    <?php if ($badge): ?>
      <span class="badge <?= e($badge['tone']) ?>"><?= e($badge['text']) ?></span>
    <?php endif; ?>
    <a href="producto.php?id=<?= (int) $p['id'] ?>">
      <img src="<?= e($p['img']) ?>" alt="<?= e($p['nombre']) ?>" />
    </a>
  </div>
  <div class="cart-line-info">
    <div class="cart-brand"><?= e($p['marca']) ?></div>
    <h3><a href="producto.php?id=<?= (int) $p['id'] ?>"><?= e($p['nombre']) ?></a></h3>
    <p class="cart-unit"><?= e($p['unidad']) ?> · SKU-<?= str_pad((string) $p['id'], 4, '0', STR_PAD_LEFT) ?></p>
    <div class="cart-stock"><i class="<?= e($p['stockTone']) ?>"></i> Stock asegurado en Tienda Magdalena</div>
    <div class="cart-price-row">
      <?php if ($antes !== null && $antes > $precio): ?>
        <span class="price-old"><?= e(dinero($antes)) ?></span>
        <span class="save-pill">Ahorras <?= e(dinero($ahorroUn)) ?></span>
      <?php endif; ?>
      <strong class="price-now"><?= e(dinero($precio)) ?> <small>/ un.</small></strong>
    </div>
    <?php if ($tope): ?>
      <div class="cart-alert">⚠ Tope de abastecimiento barrial — máx. 3 unidades</div>
    <?php endif; ?>
    <div class="cart-line-actions">
      <form method="post" action="carrito.php">
        <input type="hidden" name="accion" value="guardar" />
        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
        <input type="hidden" name="volver" value="<?= e($volver) ?>" />
        <button type="submit">♡ Guardar para después</button>
      </form>
      <form method="post" action="carrito.php">
        <input type="hidden" name="accion" value="eliminar" />
        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
        <input type="hidden" name="volver" value="<?= e($volver) ?>" />
        <button type="submit" class="danger">🗑 Eliminar</button>
      </form>
    </div>
  </div>
  <div class="cart-line-side">
    <form class="qty cart-qty" method="post" action="carrito.php">
      <input type="hidden" name="accion" value="set_cantidad" />
      <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
      <input type="hidden" name="volver" value="<?= e($volver) ?>" />
      <button type="submit" name="cantidad" value="<?= max(1, $qty - 1) ?>" aria-label="Menos">−</button>
      <span><?= $qty ?></span>
      <button type="submit" name="cantidad" value="<?= min(99, $qty + 1) ?>" aria-label="Más" <?= $tope && $qty >= 3 ? 'disabled' : '' ?>>+</button>
    </form>
    <div class="cart-subtotal">
      <span>Subtotal</span>
      <strong><?= e(dinero($subtotal)) ?></strong>
    </div>
  </div>
</article>
