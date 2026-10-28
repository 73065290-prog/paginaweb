<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$id = max(0, (int) ($_GET['id'] ?? 0));
$producto = mass_producto_detalle($id);

if ($producto === null) {
    flash_set('Producto no encontrado');
    redirect('catalogo.php');
}

$presentacionActiva = $producto['presentaciones'][0] ?? null;
foreach ($producto['presentaciones'] as $pres) {
    if (!empty($pres['activa'])) {
        $presentacionActiva = $pres;
        break;
    }
}

$precioActual = (float) ($presentacionActiva['precio'] ?? $producto['precio']);
$precioAntes = isset($presentacionActiva['precioAntes'])
    ? (float) $presentacionActiva['precioAntes']
    : (isset($producto['precioAntes']) ? (float) $producto['precioAntes'] : null);

$stockCant = (int) ($producto['stockCantidad'] ?? 20);
$stockMax = max(1, (int) ($producto['stockMax'] ?? 50));
$stockPct = min(100, (int) round(($stockCant / $stockMax) * 100));

$tiendas = mass_tiendas();
$relacionados = productos_por_ids($producto['relacionados'] ?? []);
$combo = $producto['combo'] ?? null;
$promoPlus = $producto['promoPlus'] ?? null;
$confianza = $producto['confianza'] ?? [];
$galeria = $producto['galeria'] ?? [$producto['img']];

$tituloPagina = $producto['nombre'] . ' — Mass Online';
$descripcion = (string) ($producto['descripcion'] ?? $producto['nombre']);
$paginaCssExtra = ['assets/css/producto.css'];
$navActivo = 'catalogo';
$footerModo = 'catalogo';

require __DIR__ . '/../includes/layouts/header.php';
?>

<main class="pdp-page">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Migas de pan">
      <a href="index.php">Inicio</a>
      <span class="sep">›</span>
      <a href="catalogo.php">Catálogo</a>
      <span class="sep">›</span>
      <a href="catalogo.php?cat=<?= urlencode((string) $producto['categoria']) ?>"><?= e($producto['categoria']) ?></a>
      <span class="sep">›</span>
      <span><?= e($producto['nombre']) ?></span>
    </nav>

    <section class="pdp-hero">
      <div class="pdp-gallery">
        <div class="pdp-thumbs" role="tablist" aria-label="Miniaturas">
          <?php foreach ($galeria as $i => $src): ?>
            <button
              type="button"
              class="pdp-thumb <?= $i === 0 ? 'is-active' : '' ?>"
              data-src="<?= e($src) ?>"
              aria-label="Vista <?= $i + 1 ?>"
            >
              <img src="<?= e($src) ?>" alt="" loading="lazy" />
            </button>
          <?php endforeach; ?>
        </div>
        <div class="pdp-main-media">
          <?php if (!empty($producto['badge'])): ?>
            <span class="badge <?= e($producto['badge']['tone']) ?>"><?= e($producto['badge']['text']) ?></span>
          <?php endif; ?>
          <button type="button" class="wish <?= !empty($producto['favorito']) ? 'active' : '' ?>" aria-label="Favorito">
            <?= !empty($producto['favorito']) ? '♥' : '♡' ?>
          </button>
          <img id="pdpMainImg" src="<?= e($galeria[0]) ?>" alt="<?= e($producto['nombre']) ?>" />
        </div>
        <?php if ($confianza): ?>
        <ul class="pdp-trust">
          <?php foreach ($confianza as $c): ?>
            <li>
              <span class="trust-ico" aria-hidden="true"><?= e($c['icono']) ?></span>
              <div>
                <strong><?= e($c['titulo']) ?></strong>
                <span><?= e($c['texto']) ?></span>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>

      <div class="pdp-buy">
        <div class="pdp-brand"><?= e($producto['marca']) ?> · SKU <?= e($producto['sku'] ?? '') ?></div>
        <h1><?= e($producto['nombre']) ?></h1>
        <div class="pdp-rating">
          <span class="stars" aria-hidden="true">★★★★★</span>
          <strong><?= e(number_format((float) ($producto['rating'] ?? 0), 1)) ?></strong>
          <span>(<?= (int) ($producto['reviews'] ?? 0) ?> opiniones)</span>
        </div>

        <div class="pdp-price-block">
          <span class="pdp-price" id="pdpPrice"><?= e(dinero($precioActual)) ?></span>
          <?php if ($precioAntes !== null && $precioAntes > $precioActual): ?>
            <span class="pdp-price-old" id="pdpPriceOld"><?= e(dinero($precioAntes)) ?></span>
          <?php else: ?>
            <span class="pdp-price-old" id="pdpPriceOld" hidden></span>
          <?php endif; ?>
        </div>

        <div class="pdp-block">
          <h2 class="pdp-label">Presentación</h2>
          <div class="pdp-variants" role="group" aria-label="Presentaciones">
            <?php foreach ($producto['presentaciones'] as $pres): ?>
              <button
                type="button"
                class="pdp-variant <?= !empty($pres['activa']) ? 'is-active' : '' ?>"
                data-precio="<?= e((string) $pres['precio']) ?>"
                data-antes="<?= e(isset($pres['precioAntes']) ? (string) $pres['precioAntes'] : '') ?>"
                data-label="<?= e($pres['label']) ?>"
              >
                <span class="var-label"><?= e($pres['label']) ?></span>
                <span class="var-price"><?= e(dinero((float) $pres['precio'])) ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="pdp-stock-card">
          <div class="pdp-stock-head">
            <span>Stock en Tienda <?= e($producto['tiendaStock'] ?? 'Magdalena') ?></span>
            <strong><?= $stockCant ?> disponibles</strong>
          </div>
          <div class="pdp-stock-bar" aria-hidden="true">
            <i style="width: <?= $stockPct ?>%"></i>
          </div>
        </div>

        <form class="pdp-actions" method="post" action="carrito.php">
          <?= csrf_field() ?>
          <input type="hidden" name="accion" value="agregar" />
          <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>" />
          <input type="hidden" name="volver" value="<?= e($_SERVER['REQUEST_URI'] ?? 'producto.php?id=' . (int) $producto['id']) ?>" />
          <div class="qty">
            <button type="button" class="qty-minus" aria-label="Menos">−</button>
            <input type="number" name="cantidad" value="1" min="1" max="99" class="qty-input" aria-label="Cantidad" />
            <button type="button" class="qty-plus" aria-label="Más">+</button>
          </div>
          <button class="btn-add-pdp" type="submit">
            🛒 Agregar al Carrito · <span id="pdpBtnPrice"><?= e(dinero($precioActual)) ?></span>
          </button>
        </form>

        <?php if ($promoPlus): ?>
        <aside class="pdp-promo-plus">
          <span class="promo-badge"><?= e($promoPlus['badge']) ?></span>
          <div>
            <strong><?= e($promoPlus['titulo']) ?></strong>
            <p><?= e($promoPlus['texto']) ?></p>
          </div>
          <a href="#combo">Ver oferta</a>
        </aside>
        <?php endif; ?>

        <div class="pdp-ship">
          <div>
            <strong>Envío Express</strong>
            <span>45–60 min en tu zona</span>
          </div>
          <div>
            <strong>Retiro Gratis</strong>
            <span>En tienda Magdalena</span>
          </div>
        </div>
      </div>
    </section>

    <section class="pdp-stores" aria-labelledby="storesTitle">
      <div class="pdp-section-head">
        <h2 id="storesTitle">Disponibilidad en Tiendas Mass Lima</h2>
        <span class="pdp-updated">Actualizado hace 2 min</span>
      </div>
      <div class="store-grid">
        <?php foreach ($tiendas as $tienda): ?>
          <?php render_store_card($tienda); ?>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if ($combo): ?>
    <section class="pdp-combo" id="combo" aria-labelledby="comboTitle">
      <div class="pdp-section-head">
        <h2 id="comboTitle"><?= e($combo['titulo']) ?></h2>
        <?php if (!empty($combo['badge'])): ?>
          <span class="combo-badge"><?= e($combo['badge']) ?></span>
        <?php endif; ?>
      </div>
      <div class="combo-layout">
        <div class="combo-items">
          <?php foreach ($combo['items'] as $i => $item): ?>
            <?php if ($i > 0): ?><span class="combo-plus" aria-hidden="true">+</span><?php endif; ?>
            <article class="combo-item <?= !empty($item['checked']) ? 'is-checked' : '' ?>">
              <img src="<?= e($item['img']) ?>" alt="<?= e($item['nombre']) ?>" loading="lazy" />
              <h3><?= e($item['nombre']) ?></h3>
              <p><?= e(dinero((float) $item['precio'])) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
        <div class="combo-summary">
          <span class="combo-label">Precio total combo</span>
          <div class="combo-prices">
            <strong><?= e(dinero((float) $combo['precioCombo'])) ?></strong>
            <?php if (!empty($combo['precioAntes'])): ?>
              <s><?= e(dinero((float) $combo['precioAntes'])) ?></s>
            <?php endif; ?>
          </div>
          <form method="post" action="carrito.php">
            <?= csrf_field() ?>
            <input type="hidden" name="accion" value="agregar" />
            <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>" />
            <input type="hidden" name="cantidad" value="1" />
            <input type="hidden" name="volver" value="<?= e($_SERVER['REQUEST_URI'] ?? 'producto.php?id=' . (int) $producto['id']) ?>" />
            <button type="submit" class="btn-combo">Agregar Combo Completo</button>
          </form>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <section class="pdp-desc" aria-labelledby="descTitle">
      <div class="pdp-tabs">
        <button type="button" class="is-active" id="descTitle">Descripción</button>
        <button type="button" disabled>Especificaciones</button>
        <button type="button" disabled>Opiniones</button>
      </div>
      <div class="pdp-desc-body">
        <p><?= e($producto['descripcion']) ?></p>
        <?php if (!empty($producto['beneficios'])): ?>
        <ul class="pdp-benefits">
          <?php foreach ($producto['beneficios'] as $b): ?>
            <li><span aria-hidden="true">✓</span><?= e($b) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </section>

    <?php if ($relacionados): ?>
    <section class="pdp-related" aria-labelledby="relTitle">
      <div class="pdp-section-head">
        <h2 id="relTitle">Completa tu compra</h2>
      </div>
      <div class="product-grid related-grid">
        <?php foreach ($relacionados as $p): ?>
          <?php render_product_card($p); ?>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/../includes/layouts/footer.php'; ?>
