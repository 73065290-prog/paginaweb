<?php
declare(strict_types=1);

/* bootstrap vía shim admin|cliente_interfaces */

$orden = pedido_ultimo();

// Demo Figma: regenera orden según ?modo=retiro|express
if (isset($_GET['demo'])) {
    $_SESSION['carrito'] = [
        ['id' => 1, 'cantidad' => 2],
        ['id' => 2, 'cantidad' => 1],
        ['id' => 3, 'cantidad' => 1],
        ['id' => 11, 'cantidad' => 1],
    ];
    $modo = ($_GET['modo'] ?? 'express') === 'retiro' ? 'retiro' : 'express';
    $tiendaDemo = mass_tienda_por_id('magdalena');
    pedido_set([
        'cupon' => 'CLUBMASS10',
        'entrega' => $modo,
        'tienda_id' => 'magdalena',
        'nombre' => 'Mariana',
        'apellido' => 'Quispe',
        'documento' => '45678912',
        'telefono' => '999111222',
        'direccion' => $modo === 'express'
            ? 'Av. Brasil 2340, Dpto 802, Magdalena del Mar'
            : (string) ($tiendaDemo['direccion'] ?? 'Av. Sucre 420, Magdalena del Mar'),
        'horario' => $modo === 'express' ? 'hoy_express' : 'hoy_tarde',
        'comprobante' => 'boleta',
    ]);
    $orden = pedido_confirmar('tarjeta');
}

if (!$orden) {
    flash_set('Aún no tienes un pedido confirmado');
    redirect('pedido.php');
}

$pedido = $orden['pedido'] ?? [];
$resumen = $orden['resumen'] ?? pedido_resumen([], $pedido);
$entrega = pedido_normalizar_entrega((string) ($pedido['entrega'] ?? 'retiro'));
$esRetiro = $entrega === 'retiro';
$tienda = $orden['tienda'] ?? mass_tienda_por_id((string) ($pedido['tienda_id'] ?? 'magdalena'));
$pasos = pedido_tracking_pasos($entrega, (string) ($orden['estado'] ?? 'gondola'));
$nombre = trim((string) ($pedido['nombre'] ?? 'cliente'));
$horarios = mass_horarios_entrega($entrega);
$horarioLabel = $horarios[(string) ($pedido['horario'] ?? '')] ?? (string) ($pedido['horario'] ?? '');
$pagoLabel = match ((string) ($orden['pago'] ?? 'yape')) {
    'tarjeta' => 'Tarjeta · VISA **** 4521',
    'contra' => $esRetiro ? 'Pago en tienda' : 'Pago contra entrega',
    default => 'Yape / Plin / QR',
};

$tituloPagina = 'Pedido ' . ($orden['codigo'] ?? '') . ' — Mass Online';
$descripcion = 'Seguimiento de tu pedido Mass Online.';
$paginaCssExtra = ['assets/css/pedido.css', 'assets/css/confirmacion.css'];
$navActivo = 'catalogo';
$footerModo = 'catalogo';

require MASS_ROOT . '/frontend/views/layouts/header.php';
?>

<main class="confirm-page">
  <div class="container">
    <section class="confirm-hero">
      <div class="confirm-hero-text">
        <div class="confirm-check" aria-hidden="true">✓</div>
        <div>
          <h1>¡Gracias por tu compra, <?= e($nombre) ?>!</h1>
          <p>Tu pedido <strong><?= e((string) $orden['codigo']) ?></strong> fue verificado y se está preparando.</p>
        </div>
      </div>
      <div class="confirm-hero-actions">
        <button type="button" class="btn-ghost">Boleta PDF (<?= e((string) $orden['boleta']) ?>)</button>
        <a class="btn-ghost" href="https://wa.me/?text=<?= rawurlencode('Mi pedido Mass ' . $orden['codigo']) ?>" target="_blank" rel="noopener">Compartir por WhatsApp</a>
      </div>
    </section>

    <div class="confirm-meta">
      <div>
        <span><?= $esRetiro ? 'Tienda de retiro' : 'Dirección de entrega' ?></span>
        <strong><?= e((string) ($pedido['direccion'] ?? '')) ?></strong>
      </div>
      <div>
        <span><?= $esRetiro ? 'Ventana de retiro' : 'Ventana de entrega' ?></span>
        <strong><?= e($horarioLabel) ?></strong>
      </div>
      <div>
        <span>Método de pago</span>
        <strong><?= e($pagoLabel) ?></strong>
      </div>
      <div>
        <span>Modalidad</span>
        <strong><?= $esRetiro ? 'Retiro en tienda · GRATIS' : 'Delivery Express' ?></strong>
      </div>
    </div>

    <div class="confirm-layout">
      <section class="confirm-main">
        <div class="panel">
          <h2>Estado del pedido en vivo</h2>
          <ol class="track-steps">
            <?php foreach ($pasos as $st): ?>
              <li class="track-step is-<?= e($st['estado']) ?>">
                <span class="track-dot" aria-hidden="true"></span>
                <div>
                  <strong><?= e($st['titulo']) ?></strong>
                  <p><?= e($st['detalle']) ?></p>
                  <?php if ($st['key'] === 'gondola' && $st['estado'] === 'current' && $tienda): ?>
                    <small>Personal shopper · <?= e((string) $tienda['nombre']) ?></small>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>

        <?php if ($esRetiro): ?>
          <div class="panel confirm-retiro">
            <h2>Retiro en tienda</h2>
            <div class="retiro-grid">
              <div>
                <p><strong><?= e((string) ($tienda['nombre'] ?? 'Tienda Mass')) ?></strong></p>
                <p><?= e((string) ($tienda['direccion'] ?? $pedido['direccion'])) ?></p>
                <p class="muted">Presenta este QR y tu DNI en el módulo de retiro.</p>
                <ul class="retiro-rules">
                  <li>Retiro solo por el titular o con DNI autorizado</li>
                  <li>Conserva el código <?= e((string) $orden['codigo']) ?></li>
                  <li>Tu reserva de góndola está activa</li>
                </ul>
              </div>
              <div class="qr-pickup" aria-hidden="true">
                <div class="qr-fake">QR</div>
                <span><?= e((string) $orden['codigo']) ?></span>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="panel confirm-delivery">
            <h2>Seguimiento en mapa</h2>
            <div class="map-fake" role="img" aria-label="Mapa de ruta Mass Magdalena a tu dirección">
              <div class="map-pin store">Mass</div>
              <div class="map-route"></div>
              <div class="map-pin home">Tú</div>
            </div>
            <?php $rider = $orden['rider'] ?? []; ?>
            <div class="rider-card">
              <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop" alt="" />
              <div>
                <strong><?= e((string) ($rider['nombre'] ?? 'Motorizado Mass')) ?></strong>
                <p>★ <?= e((string) ($rider['rating'] ?? '4.8')) ?> · <?= e((string) ($rider['vehiculo'] ?? 'Motorizado')) ?></p>
              </div>
              <div class="rider-actions">
                <a href="tel:<?= e((string) ($rider['telefono'] ?? '')) ?>">Llamar</a>
                <a href="https://wa.me/51<?= e((string) ($rider['telefono'] ?? '')) ?>" target="_blank" rel="noopener">WhatsApp</a>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </section>

      <aside class="confirm-side">
        <div class="panel">
          <h2>Detalle de la orden</h2>
          <ul class="order-lines">
            <?php foreach (($resumen['lineas'] ?? []) as $linea): ?>
              <li>
                <img src="<?= e((string) $linea['img']) ?>" alt="" />
                <div>
                  <strong><?= e((string) $linea['nombre']) ?></strong>
                  <span>x<?= (int) $linea['cantidad'] ?></span>
                </div>
                <em><?= e(dinero((float) $linea['subtotal'])) ?></em>
              </li>
            <?php endforeach; ?>
          </ul>
          <dl class="sum-lines">
            <div><dt>Subtotal productos</dt><dd><?= e(dinero((float) $resumen['subtotal'])) ?></dd></div>
            <?php if (($resumen['ahorroOfertas'] ?? 0) > 0): ?>
            <div class="is-save"><dt>Descuento Ofertas Bomba</dt><dd>−<?= e(dinero((float) $resumen['ahorroOfertas'])) ?></dd></div>
            <?php endif; ?>
            <?php if (($resumen['ahorroAprovecha'] ?? 0) > 0): ?>
            <div class="is-save"><dt>Ahorro Aprovecha+</dt><dd>−<?= e(dinero((float) $resumen['ahorroAprovecha'])) ?></dd></div>
            <?php endif; ?>
            <?php if (($resumen['descuentoCupon'] ?? 0) > 0): ?>
            <div class="is-save"><dt>Cupón <?= e((string) $resumen['cupon']) ?></dt><dd>−<?= e(dinero((float) $resumen['descuentoCupon'])) ?></dd></div>
            <?php endif; ?>
            <div>
              <dt><?= $esRetiro ? 'Retiro en tienda' : 'Delivery Express' ?></dt>
              <dd><?= ((float) $resumen['envio']) > 0 ? e(dinero((float) $resumen['envio'])) : 'GRATIS' ?></dd>
            </div>
          </dl>
          <div class="sum-total">
            <span>Total pagado</span>
            <strong><?= e(dinero((float) $resumen['total'])) ?></strong>
          </div>
        </div>

        <?php $impacto = $orden['impacto'] ?? ['co2' => 1, 'plastico' => 1.4, 'puntos' => 100]; ?>
        <div class="panel impact-panel">
          <h2>Impacto positivo generado</h2>
          <div class="impact-grid">
            <div><strong><?= e(number_format((float) $impacto['co2'], 1)) ?> kg</strong><span>CO₂ evitado</span></div>
            <div><strong><?= e(number_format((float) $impacto['plastico'], 1)) ?> kg</strong><span>Plástico no usado</span></div>
            <div><strong>+<?= (int) $impacto['puntos'] ?></strong><span>Puntos Club Mass</span></div>
          </div>
        </div>

        <div class="panel help-panel">
          <h2>¿Necesitas ayuda con tu <?= $esRetiro ? 'retiro' : 'entrega' ?>?</h2>
          <p>(01) 615-8000 · Lun–Dom 7:00–22:00</p>
          <a href="index.php">Preguntas frecuentes</a>
        </div>
      </aside>
    </div>

    <div class="confirm-footer-actions">
      <a class="btn btn-ghost-lg" href="pedido.php">Ir a mis pedidos</a>
      <a class="btn btn-naranja" href="catalogo.php">Seguir comprando</a>
    </div>
  </div>
</main>

<?php require MASS_ROOT . '/frontend/views/layouts/footer.php'; ?>

