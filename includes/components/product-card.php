<?php
declare(strict_types=1);

/** @var array $p */
$p = $p ?? [];
$badge = $p['badge'] ?? null;
$qtyMax = max(1, carrito_tope_linea((int) ($p['id'] ?? 0)));
?>
<article class="product">
  <a class="product-link" href="producto.php?id=<?= (int) $p['id'] ?>">
    <div class="product-media">
      <?php if ($badge): ?>
        <span class="badge <?= e($badge['tone']) ?>"><?= e($badge['text']) ?></span>
      <?php endif; ?>
      <span class="wish <?= !empty($p['favorito']) ? 'active' : '' ?>" aria-hidden="true"><?= !empty($p['favorito']) ? '♥' : '♡' ?></span>
      <img src="<?= e($p['img']) ?>" alt="<?= e($p['nombre']) ?>" loading="lazy" />
    </div>
  </a>
  <div class="product-body">
    <div class="stock"><i class="<?= e($p['stockTone']) ?>"></i><span><?= e($p['stockLabel']) ?></span></div>
    <div class="meta"><?= e($p['marca']) ?> / <?= e($p['categoria']) ?></div>
    <h3><a class="product-link" href="producto.php?id=<?= (int) $p['id'] ?>"><?= e($p['nombre']) ?></a></h3>
    <p class="unit"><?= e($p['unidad']) ?></p>
    <div class="prices">
      <span class="price-now"><?= e(dinero((float) $p['precio'])) ?></span>
      <?php if (!empty($p['precioAntes'])): ?>
        <span class="price-old"><?= e(dinero((float) $p['precioAntes'])) ?></span>
      <?php endif; ?>
    </div>
    <form class="product-actions" method="post" action="carrito.php">
      <?= csrf_field() ?>
      <input type="hidden" name="accion" value="agregar" />
      <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
      <input type="hidden" name="volver" value="<?= e($_SERVER['REQUEST_URI'] ?? 'index.php') ?>" />
      <div class="qty">
        <button type="button" class="qty-minus" aria-label="Menos">−</button>
        <input type="number" name="cantidad" value="1" min="1" max="<?= (int) $qtyMax ?>" class="qty-input" aria-label="Cantidad" />
        <button type="button" class="qty-plus" aria-label="Más">+</button>
      </div>
      <button class="btn-add" type="submit">🛒 Agregar</button>
    </form>
  </div>
</article>
