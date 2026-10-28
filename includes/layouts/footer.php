<?php
declare(strict_types=1);
$footerModo = $footerModo ?? 'inicio';
?>
  <footer class="footer" id="tiendas">
    <?php if ($footerModo === 'catalogo'): ?>
    <div class="container">
      <div class="footer-trust">
        <div class="footer-trust-item">
          <div class="ico">🔒</div>
          <div><strong>Compra 100% segura</strong><span>Tus datos protegidos</span></div>
        </div>
        <div class="footer-trust-item">
          <div class="ico">🚚</div>
          <div><strong>Envíos a domicilio</strong><span>Express 45 min en tu zona</span></div>
        </div>
        <div class="footer-trust-item">
          <div class="ico">📍</div>
          <div><strong>Encuentra tu Tienda</strong><span>+400 locales en Lima</span></div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <div class="container footer-grid">
      <div>
        <a class="brand" href="index.php"><img src="<?= e(mass_asset('assets/img/logo.svg')) ?>" alt="mass online" /></a>
        <p>Soy la tienda Mass: precios bajos cerca de ti, surtido esencial y ahorro real para tu familia.</p>
        <?php if ($footerModo === 'catalogo'): ?>
        <div class="payments">
          <span class="pay-badge">VISA</span>
          <span class="pay-badge">MC</span>
          <span class="pay-badge">YAPE</span>
          <span class="pay-badge">PLIN</span>
        </div>
        <?php endif; ?>
      </div>
      <div>
        <h4>Nosotros</h4>
        <p>
          <a href="index.php">Conóceme</a><br />
          <a href="index.php#tiendas">Nuestras tiendas</a><br />
          <a href="index.php#promos">Promociones</a>
        </p>
      </div>
      <div>
        <h4><?= $footerModo === 'catalogo' ? 'Ayuda' : 'Atención al cliente' ?></h4>
        <p>
          Lun–Dom · 7:00–22:00<br />
          (01) 615-8000<br />
          Libro de reclamaciones
        </p>
      </div>
      <div>
        <h4><?= $footerModo === 'catalogo' ? 'Legal' : 'Políticas' ?></h4>
        <p>
          Términos y condiciones<br />
          Privacidad<br />
          Cambios y devoluciones
        </p>
      </div>
    </div>
    <div class="container footer-bottom">
      <span>© <?= date('Y') ?> Tiendas Mass</span>
      <span>Frontend PHP · Verde · Blanco · Naranja</span>
    </div>
  </footer>

  <?php require __DIR__ . '/../components/cart-drawer.php'; ?>

  <?php if (!empty($flash)): ?>
    <div class="toast show" id="toast" role="status" aria-live="polite" aria-atomic="true"><?= e($flash) ?></div>
  <?php else: ?>
    <div class="toast" id="toast" role="status" aria-live="polite" aria-atomic="true"></div>
  <?php endif; ?>

  <script src="<?= e(mass_asset('assets/js/ui.js')) ?>"></script>
</body>
</html>
