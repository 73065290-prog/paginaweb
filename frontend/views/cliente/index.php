<?php
declare(strict_types=1);

/* bootstrap vía shim admin|cliente_interfaces */

$tituloPagina = 'Mass Online — Tiendas Mass';
$navActivo = 'inicio';
$footerModo = 'inicio';

$categorias = mass_categorias();
$productos = mass_productos();
$ofertas = array_slice($productos, 0, 8);
$favoritos = array_values(array_filter($productos, static fn(array $p): bool => !empty($p['favorito'])));

require MASS_ROOT . '/frontend/views/layouts/header.php';
?>

<main>
  <section class="hero" id="inicio">
    <div class="container hero-grid">
      <div class="hero-copy">
        <div class="hero-tag">Frescura garantizada y precios de barrio.</div>
        <h1>Todo lo que necesitas, <em>más cerca de ti</em> y al mejor precio.</h1>
        <p class="hero-lead">
          Frescos del mercado, abarrotes esenciales y ofertas bomba con stock en tiempo real de tu sede Mass.
        </p>
        <div class="hero-cta">
          <a class="btn btn-naranja" href="#ofertas">🔥 Ver Ofertas Bomba -20%</a>
          <a class="btn btn-verde" href="catalogo.php">Explorar Catálogo →</a>
        </div>
        <div class="hero-trust">
          <div class="trust-card">
            <div class="ico">🚚</div>
            <div><strong>45 min</strong><span>Entrega Express</span></div>
          </div>
          <div class="trust-card">
            <div class="ico">🏷️</div>
            <div><strong>100% Real</strong><span>Precio de bodega</span></div>
          </div>
          <div class="trust-card">
            <div class="ico">🌿</div>
            <div><strong>Cero Merma</strong><span>Consumo consciente</span></div>
          </div>
        </div>
      </div>

      <div class="hero-visual">
        <div class="hero-photo">
          <img
            src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=900&h=900&fit=crop"
            alt="Canasta de productos frescos en Tiendas Mass"
          />
        </div>
        <div class="hero-badge b1">✅ Garantía 100% Frescura</div>
        <div class="hero-badge b2">⭐ Calidad Mass</div>
        <div class="hero-badge b3">💡 Ahorro Inteligente</div>
      </div>
    </div>
  </section>

  <section class="value-bar" aria-label="Beneficios">
    <div class="container">
      <div class="value-grid">
        <div class="value-item">
          <div class="ico g">⚡</div>
          <div><strong>Entrega Express 45 min</strong><span>A tu puerta desde tu sede</span></div>
        </div>
        <div class="value-item">
          <div class="ico o">🏷️</div>
          <div><strong>Precios de Bodega</strong><span>Ahorro real todos los días</span></div>
        </div>
        <div class="value-item">
          <div class="ico g">📋</div>
          <div><strong>Stock en Tiempo Real</strong><span>Lo que ves, está disponible</span></div>
        </div>
        <div class="value-item">
          <div class="ico g">♻️</div>
          <div><strong>Consumo Responsable</strong><span>Menos merma, más ahorro</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" id="categorias">
    <div class="container">
      <div class="section-head">
        <h2>Categorías Populares</h2>
        <a class="section-link" href="catalogo.php">Ver las 18 secciones completas ›</a>
      </div>
      <div class="cats">
        <?php foreach ($categorias as $c): ?>
          <?php render_category_card($c); ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section" id="ofertas" style="padding-top: 0.5rem">
    <div class="container">
      <div class="bomba">
        <div class="bomba-left">
          <span class="bomba-badge">📢 OFERTAS BOMBA</span>
          <p>Precios congelados hasta agotar existencias asignadas a tu sede.</p>
        </div>
        <div class="bomba-timer">
          <span>TERMINA EN:</span>
          <div class="timer-boxes" id="countdown">
            <div class="timer-box" data-unit="h">08</div>
            <span>:</span>
            <div class="timer-box" data-unit="m">34</div>
            <span>:</span>
            <div class="timer-box" data-unit="s">11</div>
          </div>
        </div>
      </div>

      <div class="section-head">
        <h2>Nuestras Ofertas</h2>
        <a class="section-link" href="#favoritos">Ver favoritos del barrio ›</a>
      </div>
      <div class="product-grid">
        <?php foreach ($ofertas as $p): ?>
          <?php render_product_card($p); ?>
        <?php endforeach; ?>
      </div>

      <div class="promo" id="promos">
        <div>
          <h2>Lleva Mass pagando menos</h2>
          <p>Productos de calidad cerca de tu hogar. Ahorra en tu compra diaria con precios del barrio.</p>
          <a class="btn btn-naranja" href="#ofertas">Comprar ahora</a>
        </div>
        <div class="promo-stats">
          <div class="promo-stat"><strong>+400</strong><span>tiendas</span></div>
          <div class="promo-stat"><strong>45 min</strong><span>express</span></div>
          <div class="promo-stat"><strong>S/</strong><span>precios Mass</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" id="favoritos">
    <div class="container">
      <div class="section-head">
        <h2>Favoritos del Barrio</h2>
        <a class="section-link" href="catalogo.php">Ver todas las ofertas ›</a>
      </div>
      <div class="product-grid">
        <?php foreach ($favoritos as $p): ?>
          <?php render_product_card($p); ?>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php require MASS_ROOT . '/frontend/views/layouts/footer.php'; ?>

