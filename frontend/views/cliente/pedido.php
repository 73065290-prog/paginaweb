<?php
declare(strict_types=1);

/* bootstrap vía shim */

$pasoRaw = (string) ($_GET['paso'] ?? '1');
if ($pasoRaw === 'ok') {
    redirect('confirmacion.php');
}
$paso = max(1, min(3, (int) $pasoRaw));

// Carga demo Figma (solo si canasta vacía)
if (isset($_GET['demo']) && !carrito_items()) {
    $_SESSION['carrito'] = [
        ['id' => 1, 'cantidad' => 2],
        ['id' => 2, 'cantidad' => 1],
        ['id' => 3, 'cantidad' => 1],
        ['id' => 11, 'cantidad' => 1],
    ];
    pedido_set(['cupon' => 'CLUBMASS10', 'entrega' => 'retiro', 'tienda_id' => 'magdalena']);
    redirect('pedido.php');
}

$items = carrito_items();
$resumen = pedido_resumen();
$pedido = pedido_estado();
$countItems = count($items);
$tiendas = mass_tiendas();
$horarios = mass_horarios_entrega((string) $pedido['entrega']);

if (($paso === 2 || $paso === 3) && !$items) {
    flash_set('Agrega productos antes de continuar');
    redirect('pedido.php');
}

// Demo visual Figma: sugeridos para completar canasta
$sugeridos = array_slice(mass_productos(), 0, 4);

$tituloPagina = match ($paso) {
    2 => 'Entrega o Retiro — Mass Online',
    3 => 'Pago & Confirmación — Mass Online',
    default => 'Carrito & Validación — Mass Online',
};
$descripcion = 'Checkout Mass Online: carrito, entrega y pago.';
$paginaCssExtra = ['assets/css/pedido.css'];
$navActivo = 'catalogo';
$footerModo = 'catalogo';

require MASS_ROOT . '/frontend/views/layouts/header.php';
?>

<main class="checkout-page">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Migas de pan">
      <a href="index.php">Inicio</a>
      <span class="sep">›</span>
      <a href="catalogo.php">Catálogo</a>
      <span class="sep">›</span>
      <span>Checkout</span>
    </nav>

    <ol class="checkout-steps" aria-label="Progreso del pedido">
      <li class="<?= $paso === 1 ? 'is-active' : ($paso > 1 ? 'is-done' : '') ?>">
        <span class="step-num">1</span>
        <span class="step-label">Carrito &amp; Validación</span>
      </li>
      <li class="<?= $paso === 2 ? 'is-active' : ($paso > 2 ? 'is-done' : '') ?>">
        <span class="step-num">2</span>
        <span class="step-label">Entrega o Retiro</span>
      </li>
      <li class="<?= $paso === 3 ? 'is-active' : '' ?>">
        <span class="step-num">3</span>
        <span class="step-label">Pago &amp; Confirmación</span>
      </li>
    </ol>

    <?php if ($paso === 1): ?>
      <div class="reserve-banner">
        <div>
          <strong>Inventario en góndola asignado</strong>
          <span>Tienda Mass · Av. Sucre 420</span>
        </div>
        <div class="reserve-timer" id="reserveTimer" data-mins="14" data-secs="50">
          Reserva vence en: <strong><span data-unit="m">14</span>:<span data-unit="s">50</span></strong>
        </div>
      </div>

      <div class="checkout-layout">
        <section class="checkout-main" aria-labelledby="cartTitle">
          <div class="cart-toolbar">
            <label class="cart-check-all">
              <input type="checkbox" checked id="selectAll" />
              <span id="cartTitle">Seleccionar todos los productos (<?= $countItems ?>)</span>
            </label>
            <div class="cart-toolbar-links">
              <?php if ($items): ?>
              <form method="post" action="carrito.php" class="inline-form">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="vaciar" />
                <input type="hidden" name="volver" value="pedido.php" />
                <button type="submit">Vaciar canasta</button>
              </form>
              <?php endif; ?>
              <a href="catalogo.php">Seguir comprando</a>
            </div>
          </div>

          <?php if (!$items): ?>
            <div class="cart-empty-box">
              <p>Tu canasta está vacía.</p>
              <a class="btn btn-naranja" href="catalogo.php">Ir al catálogo</a>
            </div>
          <?php else: ?>
            <div class="cart-lines">
              <?php foreach ($items as $item): ?>
                <?php $p = producto_por_id((int) $item['id']); ?>
                <?php if (!$p) { continue; } ?>
                <?php require MASS_ROOT . '/frontend/views/components/cart-line.php'; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <aside class="cart-upsell">
            <h2>Completa tu canasta</h2>
            <div class="upsell-grid">
              <?php foreach ($sugeridos as $sug): ?>
                <article class="upsell-card">
                  <img src="<?= e($sug['img']) ?>" alt="<?= e($sug['nombre']) ?>" loading="lazy" />
                  <div>
                    <h3><?= e($sug['nombre']) ?></h3>
                    <strong><?= e(dinero((float) $sug['precio'])) ?></strong>
                  </div>
                  <form method="post" action="carrito.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="agregar" />
                    <input type="hidden" name="id" value="<?= (int) $sug['id'] ?>" />
                    <input type="hidden" name="cantidad" value="1" />
                    <input type="hidden" name="volver" value="pedido.php" />
                    <button type="submit" class="btn-add-mini">Agregar</button>
                  </form>
                </article>
              <?php endforeach; ?>
            </div>
          </aside>
        </section>

        <aside class="checkout-summary" aria-labelledby="sumTitle">
          <h2 id="sumTitle">Resumen de tu pedido</h2>

          <form class="coupon-form" method="post" action="carrito.php">
            <?= csrf_field() ?>
            <input type="hidden" name="accion" value="cupon" />
            <input type="hidden" name="volver" value="pedido.php" />
            <label for="cuponInput">Cupón de descuento</label>
            <div class="coupon-row">
              <input id="cuponInput" name="cupon" type="text" placeholder="Ej. CLUBMASS10 / CLUBMASS60" value="" autocomplete="off" />
              <button type="submit" class="btn-aplicar">Aplicar</button>
            </div>
          </form>

          <?php if ($resumen['descuentoCupon'] > 0 && $resumen['cupon'] !== ''): ?>
          <div class="coupon-applied">
            <span>Cupón <strong><?= e($resumen['cupon']) ?></strong> · −<?= e(dinero($resumen['descuentoCupon'])) ?></span>
            <form method="post" action="carrito.php">
              <?= csrf_field() ?>
              <input type="hidden" name="accion" value="cupon" />
              <input type="hidden" name="cupon" value="" />
              <input type="hidden" name="volver" value="pedido.php" />
              <button type="submit">Quitar</button>
            </form>
          </div>
          <?php endif; ?>

          <form method="post" action="carrito.php" id="entregaForm">
            <?= csrf_field() ?>
            <input type="hidden" name="accion" value="paso2" />
            <input type="hidden" name="volver" value="pedido.php?paso=2" />

            <dl class="sum-lines">
              <div><dt>Subtotal productos (<?= (int) $resumen['items'] ?>)</dt><dd><?= e(dinero($resumen['subtotal'])) ?></dd></div>
              <?php if ($resumen['ahorroOfertas'] > 0): ?>
              <div class="is-save"><dt>Ahorro ofertas bomba</dt><dd>−<?= e(dinero($resumen['ahorroOfertas'])) ?></dd></div>
              <?php endif; ?>
              <?php if ($resumen['ahorroAprovecha'] > 0): ?>
              <div class="is-save"><dt>Ahorro Aprovecha+</dt><dd>−<?= e(dinero($resumen['ahorroAprovecha'])) ?></dd></div>
              <?php endif; ?>
              <?php if ($resumen['descuentoCupon'] > 0): ?>
              <div class="is-save"><dt>Descuento Club Mass</dt><dd>−<?= e(dinero($resumen['descuentoCupon'])) ?></dd></div>
              <?php endif; ?>
              <div>
                <dt>Costo de entrega</dt>
                <dd>Se define en el siguiente paso</dd>
              </div>
            </dl>

            <?php if ($resumen['ahorroTotal'] > 0): ?>
            <div class="sum-ahorro">Tu ahorro total acumulado: <strong><?= e(dinero($resumen['ahorroTotal'])) ?></strong></div>
            <?php endif; ?>

            <?php
              // En validación de stock aún no se elige entrega: total sin costo de envío
              $totalSinEnvio = max(0, (float) $resumen['subtotal'] - (float) $resumen['ahorroAprovecha'] - (float) $resumen['descuentoCupon']);
            ?>
            <div class="sum-total">
              <span>Total a pagar</span>
              <strong><?= e(dinero($totalSinEnvio)) ?></strong>
            </div>

            <button type="submit" class="btn-continuar" <?= !$items ? 'disabled' : '' ?>>
              Continuar con la Entrega →
            </button>
          </form>

          <p class="sum-trust">🔒 Pago seguro SSL · Cambios y devoluciones en tienda</p>
          <div class="sum-pays">
            <span>VISA</span><span>MC</span><span>YAPE</span><span>PLIN</span><span>Efectivo</span>
          </div>
        </aside>
      </div>

    <?php elseif ($paso === 2): ?>
      <?php $esRetiro = ($pedido['entrega'] ?? 'retiro') === 'retiro'; ?>
      <div class="checkout-layout">
        <section class="checkout-main">
          <form
            method="post"
            action="carrito.php"
            class="entrega-form"
            id="entregaFormPaso2"
            data-modo="<?= e((string) $pedido['entrega']) ?>"
            data-horarios-retiro="<?= e(json_encode(mass_horarios_entrega('retiro'), JSON_UNESCAPED_UNICODE) ?: '{}') ?>"
            data-horarios-express="<?= e(json_encode(mass_horarios_entrega('express'), JSON_UNESCAPED_UNICODE) ?: '{}') ?>"
          >
            <?= csrf_field() ?>
            <input type="hidden" name="accion" value="paso3" />
            <input type="hidden" name="volver" value="pedido.php?paso=3" />

            <div class="panel">
              <h2>1. ¿Cómo quieres recibir tu pedido?</h2>
              <p class="panel-hint">Elige retiro en la misma tienda Mass o delivery a domicilio. El costo y los datos requeridos cambian según la opción.</p>
              <div class="entrega-opts" id="modoEntrega">
                <label class="entrega-opt <?= $esRetiro ? 'is-active' : '' ?>" data-panel="retiro">
                  <input type="radio" name="entrega" value="retiro" <?= $esRetiro ? 'checked' : '' ?> />
                  <span>
                    <strong>Retiro en tienda</strong>
                    <small>Misma tienda o otra Mass cercana · Stock en góndola</small>
                    <em>GRATIS</em>
                  </span>
                </label>
                <label class="entrega-opt <?= !$esRetiro ? 'is-active' : '' ?>" data-panel="express">
                  <input type="radio" name="entrega" value="express" <?= !$esRetiro ? 'checked' : '' ?> />
                  <span>
                    <strong>Delivery Express</strong>
                    <small>A tu dirección · Motorizado 45–60 min</small>
                    <em><?= e(dinero(4.9)) ?></em>
                  </span>
                </label>
              </div>

              <div class="modo-panel <?= $esRetiro ? 'is-visible' : '' ?>" id="panelRetiro" data-modo-panel="retiro">
                <h3>Tienda de retiro</h3>
                <p class="panel-hint">Puedes retirar en la <strong>misma tienda</strong> donde se reservó el inventario u otra con stock.</p>
                <div class="tienda-pick">
                  <?php foreach ($tiendas as $t): ?>
                    <?php
                      $agotada = ($t['estado'] ?? '') === 'agotado' || (int) ($t['stock'] ?? 0) <= 0;
                      $sel = ($pedido['tienda_id'] ?? '') === ($t['id'] ?? '');
                    ?>
                    <label class="tienda-pick-card <?= $sel ? 'is-active' : '' ?> <?= $agotada ? 'is-disabled' : '' ?>">
                      <input
                        type="radio"
                        name="tienda_id"
                        value="<?= e((string) $t['id']) ?>"
                        <?= $sel ? 'checked' : '' ?>
                        <?= $agotada ? 'disabled' : '' ?>
                      />
                      <div>
                        <strong><?= e($t['nombre']) ?></strong>
                        <?php if (!empty($t['misma'])): ?>
                          <span class="pill-misma">Misma tienda</span>
                        <?php endif; ?>
                        <p><?= e($t['direccion']) ?></p>
                        <small><?= e($t['distancia']) ?> · <?= $agotada ? 'Sin stock' : ((int) $t['stock'] . ' un. en góndola') ?></small>
                      </div>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="modo-panel <?= !$esRetiro ? 'is-visible' : '' ?>" id="panelExpress" data-modo-panel="express">
                <h3>Dirección de delivery</h3>
                <p class="panel-hint">El pedido sale desde <strong>Tienda Magdalena Sucre</strong> (inventario asignado).</p>
                <label class="field">
                  <span>Dirección completa</span>
                  <input name="direccion" id="direccionInput" type="text" value="<?= e((string) $pedido['direccion']) ?>" placeholder="Av. / Jr. / Calle, número, distrito" <?= $esRetiro ? '' : 'required' ?> />
                </label>
                <label class="field">
                  <span>Referencia (opcional)</span>
                  <input name="referencia" type="text" value="<?= e((string) ($pedido['referencia'] ?? '')) ?>" placeholder="Dpto, piso, color de fachada…" />
                </label>
                <input type="hidden" name="tienda_id" value="magdalena" id="tiendaExpressHidden" <?= $esRetiro ? 'disabled' : '' ?> />
              </div>

              <label class="field">
                <span id="horarioLabel"><?= $esRetiro ? 'Horario de retiro' : 'Ventana de entrega' ?></span>
                <select name="horario" id="horarioSelect">
                  <?php foreach ($horarios as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= ($pedido['horario'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
            </div>

            <div class="panel">
              <h2>2. Contacto y facturación</h2>
              <div class="fields-2">
                <label class="field"><span>Nombres</span><input name="nombre" required value="<?= e((string) $pedido['nombre']) ?>" /></label>
                <label class="field"><span>Apellidos</span><input name="apellido" required value="<?= e((string) $pedido['apellido']) ?>" /></label>
                <label class="field"><span>DNI / Documento</span><input name="documento" required minlength="8" value="<?= e((string) $pedido['documento']) ?>" /></label>
                <label class="field"><span>Teléfono</span><input name="telefono" required minlength="9" value="<?= e((string) $pedido['telefono']) ?>" /></label>
              </div>
              <div class="comprobante-opts">
                <label><input type="radio" name="comprobante" value="boleta" <?= ($pedido['comprobante'] ?? '') === 'boleta' ? 'checked' : '' ?> /> Boleta de venta</label>
                <label><input type="radio" name="comprobante" value="factura" <?= ($pedido['comprobante'] ?? '') === 'factura' ? 'checked' : '' ?> /> Factura</label>
              </div>
            </div>

            <div class="form-nav">
              <a href="pedido.php" class="btn-link">← Volver al carrito</a>
              <button type="submit" class="btn-continuar" id="btnPaso3">
                Continuar al Pago →
              </button>
            </div>
          </form>
        </section>

        <?php require MASS_ROOT . '/frontend/views/components/order-summary-side.php'; ?>
      </div>

    <?php else: /* paso 3 */ ?>
      <div class="checkout-layout">
        <section class="checkout-main">
          <form method="post" action="carrito.php" class="pago-form">
            <?= csrf_field() ?>
            <input type="hidden" name="accion" value="confirmar" />
            <input type="hidden" name="volver" value="pedido.php?paso=ok" />

            <div class="panel">
              <h2>3. Método de pago</h2>
              <div class="pago-opts">
                <label class="pago-opt <?= $pedido['pago'] === 'yape' ? 'is-active' : '' ?>">
                  <input type="radio" name="pago" value="yape" <?= $pedido['pago'] === 'yape' ? 'checked' : '' ?> />
                  <span><strong>Yape / Plin / QR</strong><small>Escanea y paga al instante</small></span>
                </label>
                <label class="pago-opt <?= $pedido['pago'] === 'tarjeta' ? 'is-active' : '' ?>">
                  <input type="radio" name="pago" value="tarjeta" <?= $pedido['pago'] === 'tarjeta' ? 'checked' : '' ?> />
                  <span><strong>Tarjeta débito / crédito</strong><small>Visa, Mastercard, Amex</small></span>
                </label>
                <label class="pago-opt <?= $pedido['pago'] === 'contra' ? 'is-active' : '' ?>">
                  <input type="radio" name="pago" value="contra" <?= $pedido['pago'] === 'contra' ? 'checked' : '' ?> />
                  <span><strong>Pago contra entrega</strong><small>Efectivo o tarjeta en tienda/domicilio</small></span>
                </label>
              </div>
              <div class="qr-box" aria-hidden="true">
                <div class="qr-fake">QR</div>
                <p>Demo · Total <?= e(dinero($resumen['total'])) ?></p>
              </div>
            </div>

            <div class="form-nav">
              <a href="pedido.php?paso=2" class="btn-link">← Volver a entrega</a>
              <button type="submit" class="btn-continuar btn-naranja-solid">Confirmar pedido demo</button>
            </div>
          </form>
        </section>

        <?php require MASS_ROOT . '/frontend/views/components/order-summary-side.php'; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require MASS_ROOT . '/frontend/views/layouts/footer.php'; ?>
