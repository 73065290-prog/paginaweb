<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/init.php';
auth_requerir_admin();

$tituloAdmin = 'Administración de Productos — Mass Admin';
$adminActivo = 'productos';
$adminCrumb = 'Administración de Productos';

$formErrores = [];
$postSticky = [];

// Alta / edición (demo en sesión) — al guardar se cierra el modal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) ($_POST['accion'] ?? '');

    if ($accion === 'guardar_producto') {
        $idSave = (int) ($_POST['id'] ?? 0);
        $resultado = mass_admin_producto_guardar($idSave, $_POST);
        if ($resultado['ok']) {
            $sku = 'SKU-' . str_pad((string) $resultado['id'], 5, '0', STR_PAD_LEFT);
            flash_set('Sincronización exitosa: producto ' . $sku . ' actualizado correctamente en el WMS central.');
            redirect('productos.php');
        }
        $formErrores = $resultado['errores'];
        $_GET['editar'] = (string) $idSave;
        $postSticky = $_POST;
    }

    if ($accion === 'crear_producto') {
        $resultado = mass_admin_producto_crear($_POST);
        if ($resultado['ok']) {
            $sku = 'SKU-' . str_pad((string) $resultado['id'], 5, '0', STR_PAD_LEFT);
            flash_set('Producto ' . $sku . ' creado y sincronizado con el WMS central.');
            redirect('productos.php');
        }
        $formErrores = $resultado['errores'];
        $_GET['nuevo'] = '1';
        $postSticky = $_POST;
    }
}

$q = mb_strtolower(trim((string) ($_GET['q'] ?? '')));
$filtroAbcRaw = strtoupper(trim((string) ($_GET['abc'] ?? '')));
$filtroAbc = in_array($filtroAbcRaw, ['A', 'B', 'C'], true) ? $filtroAbcRaw : 'todos';
$filtroStock = (string) ($_GET['stock'] ?? 'todos');
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$porPagina = 8;

$productos = mass_admin_productos();

if ($q !== '') {
    $productos = array_values(array_filter(
        $productos,
        static function (array $p) use ($q): bool {
            $hay = mb_strtolower($p['nombre'] . ' ' . $p['sku'] . ' ' . $p['ean'] . ' ' . $p['marca'] . ' ' . $p['categoria']);
            return str_contains($hay, $q);
        }
    ));
}

if ($filtroAbc !== 'todos') {
    $productos = array_values(array_filter(
        $productos,
        static fn (array $p): bool => ($p['abc'] ?? '') === $filtroAbc
    ));
}

if (in_array($filtroStock, ['ok', 'bajo', 'critico'], true)) {
    $productos = array_values(array_filter(
        $productos,
        static fn (array $p): bool => ($p['stockTone'] ?? '') === $filtroStock
    ));
}

$total = count($productos);
$totalPaginas = max(1, (int) ceil($total / $porPagina));
$pagina = min($pagina, $totalPaginas);
$slice = array_slice($productos, ($pagina - 1) * $porPagina, $porPagina);

$modoNuevo = isset($_GET['nuevo']);
$editarId = (int) ($_GET['editar'] ?? 0);
$editando = null;
$esAlta = false;

if ($modoNuevo) {
    $esAlta = true;
    $editando = mass_admin_producto_plantilla_nueva();
    if ($postSticky !== []) {
        $editando = mass_admin_producto_aplicar_campos($editando, [
            'nombre' => (string) ($postSticky['nombre'] ?? ''),
            'ean' => (string) ($postSticky['ean'] ?? ''),
            'unidad' => (string) ($postSticky['unidad'] ?? ''),
            'categoria' => (string) ($postSticky['categoria'] ?? ''),
            'subcategoria' => (string) ($postSticky['subcategoria'] ?? ''),
            'descripcion' => (string) ($postSticky['descripcion'] ?? ''),
            'marca' => (string) ($postSticky['marca'] ?? 'MASS'),
            'precio' => (float) ($postSticky['precio'] ?? 0),
            'costo' => (float) ($postSticky['costo'] ?? 0),
            'puntoReorden' => (int) ($postSticky['puntoReorden'] ?? 0),
            'capacidadGondola' => (int) ($postSticky['capacidadGondola'] ?? 0),
            'abc' => (string) ($postSticky['abc'] ?? ''),
            'activo' => !empty($postSticky['activo']) || !empty($postSticky['activo_ui']),
        ]);
        $editando['id'] = 0;
        $editando['esNuevo'] = true;
    }
} elseif ($editarId > 0) {
    $editando = mass_admin_producto_por_id($editarId);
    if ($editando && $postSticky !== [] && (int) ($_POST['id'] ?? 0) === $editarId) {
        $editando = mass_admin_producto_aplicar_campos($editando, [
            'nombre' => (string) ($postSticky['nombre'] ?? $editando['nombre']),
            'ean' => (string) ($postSticky['ean'] ?? $editando['ean']),
            'unidad' => (string) ($postSticky['unidad'] ?? $editando['unidad']),
            'categoria' => (string) ($postSticky['categoria'] ?? $editando['categoria']),
            'subcategoria' => (string) ($postSticky['subcategoria'] ?? $editando['subcategoria']),
            'descripcion' => (string) ($postSticky['descripcion'] ?? $editando['descripcion']),
            'precio' => (float) ($postSticky['precio'] ?? $editando['precio']),
            'costo' => (float) ($postSticky['costo'] ?? $editando['costo']),
            'puntoReorden' => (int) ($postSticky['puntoReorden'] ?? $editando['puntoReorden']),
            'capacidadGondola' => (int) ($postSticky['capacidadGondola'] ?? $editando['capacidadGondola']),
            'abc' => (string) ($postSticky['abc'] ?? $editando['abc']),
            'activo' => !empty($postSticky['activo']) || !empty($postSticky['activo_ui']),
        ]);
    }
}

$flashLista = (string) ($flash ?? '');

require dirname(__DIR__) . '/includes/admin/header.php';

$queryBase = static function (array $extra = []) use ($q, $filtroAbc, $filtroStock): string {
    $params = [
        'q' => $q !== '' ? $q : null,
        'abc' => in_array($filtroAbc, ['A', 'B', 'C'], true) ? $filtroAbc : null,
        'stock' => in_array($filtroStock, ['ok', 'bajo', 'critico'], true) ? $filtroStock : null,
    ];
    foreach ($extra as $k => $v) {
        if ($v === null) {
            unset($params[$k]);
        } else {
            $params[$k] = $v;
        }
    }
    $params = array_filter($params, static fn ($v) => $v !== null && $v !== '');
    return 'productos.php' . ($params ? '?' . http_build_query($params) : '');
};
?>

<div class="page-toolbar">
  <div>
    <h1 class="admin-title" style="margin:0">Administración de Productos</h1>
    <p class="dash-sub">Catálogo maestro · precios, surtido WMS y clasificación ABC</p>
  </div>
  <div class="toolbar-actions">
    <div class="filters-inline">
      <a href="<?= e($queryBase(['abc' => null, 'stock' => null, 'pagina' => 1])) ?>" class="<?= $filtroAbc === 'todos' && $filtroStock === 'todos' ? 'is-active' : '' ?>">Todos</a>
      <a href="<?= e($queryBase(['abc' => 'A', 'pagina' => 1])) ?>" class="<?= $filtroAbc === 'A' ? 'is-active' : '' ?>">Clase A</a>
      <a href="<?= e($queryBase(['abc' => 'B', 'pagina' => 1])) ?>" class="<?= $filtroAbc === 'B' ? 'is-active' : '' ?>">Clase B</a>
      <a href="<?= e($queryBase(['abc' => 'C', 'pagina' => 1])) ?>" class="<?= $filtroAbc === 'C' ? 'is-active' : '' ?>">Clase C</a>
      <a href="<?= e($queryBase(['stock' => 'bajo', 'pagina' => 1])) ?>" class="<?= $filtroStock === 'bajo' ? 'is-active' : '' ?>">Stock bajo</a>
    </div>
    <a class="btn-mini btn-add" href="<?= e($queryBase(['nuevo' => 1, 'editar' => null, 'pagina' => $pagina])) ?>">+ Agregar producto</a>
  </div>
</div>

<?php if ($flashLista !== ''): ?>
  <div class="prod-toast prod-toast--page" role="status"><?= e($flashLista) ?></div>
<?php endif; ?>

<article class="admin-card productos-card">
  <div class="prod-admin-meta">
    <strong><?= (int) $total ?> SKUs</strong>
    <span>TIENDAS MASS SGC Retail v4.2</span>
  </div>
  <div class="table-wrap">
    <table class="admin-table productos-table">
      <thead>
        <tr>
          <th style="width:2rem"><input type="checkbox" aria-label="Seleccionar todos" disabled /></th>
          <th>Producto / SKU</th>
          <th>Categoría · Unidad</th>
          <th>PVP / Costo</th>
          <th>Stock</th>
          <th>ABC</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($slice === []): ?>
          <tr><td colspan="7"><p class="empty-note">Sin productos para este filtro.</p></td></tr>
        <?php endif; ?>
        <?php foreach ($slice as $p): ?>
          <?php
            $stockLabel = match ($p['stockTone']) {
                'bajo' => 'Stock bajo en góndola',
                'critico' => 'Quiebre / crítico',
                default => 'Nivel estable',
            };
          ?>
          <tr>
            <td><input type="checkbox" aria-label="Seleccionar <?= e($p['sku']) ?>" /></td>
            <td>
              <div class="prod-cell">
                <img src="<?= e($p['img']) ?>" alt="" />
                <div>
                  <strong><?= e($p['nombre']) ?><?= !empty($p['esNuevo']) ? ' <em class="nuevo-tag">NUEVO</em>' : '' ?></strong>
                  <small><?= e($p['sku']) ?> · EAN <?= e($p['ean']) ?></small>
                </div>
              </div>
            </td>
            <td>
              <strong><?= e($p['categoria']) ?></strong>
              <small class="ean"><?= e($p['unidad']) ?></small>
            </td>
            <td>
              <strong><?= e(dinero((float) $p['precio'])) ?></strong>
              <small class="ean">Costo <?= e(dinero((float) $p['costo'])) ?> · +<?= e(number_format((float) $p['margenPct'], 2)) ?>%</small>
            </td>
            <td>
              <span class="status-pill <?= e($p['stockTone']) ?>"><?= e($stockLabel) ?></span>
              <small class="ean"><?= (int) $p['stockPiso'] ?> piso / <?= (int) $p['stockAlmacen'] ?> alm.</small>
            </td>
            <td><span class="abc-badge abc-<?= e(strtolower((string) $p['abc'])) ?>"><?= e($p['abc']) ?></span></td>
            <td>
              <a class="btn-mini" href="<?= e($queryBase(['editar' => (int) $p['id'], 'nuevo' => null, 'pagina' => $pagina])) ?>">Editar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <nav class="pager" aria-label="Paginación">
    <?php for ($i = 1; $i <= min($totalPaginas, 5); $i++): ?>
      <a class="<?= $pagina === $i ? 'is-active' : '' ?>" href="<?= e($queryBase(['pagina' => $i])) ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($totalPaginas > 5): ?>
      <span>…</span>
      <a href="<?= e($queryBase(['pagina' => $totalPaginas])) ?>"><?= $totalPaginas ?></a>
    <?php endif; ?>
  </nav>
</article>

<?php if ($editando): ?>
  <?php
    $p = $editando;
    $catPath = trim((string) $p['categoria'] . ' > ' . (string) $p['subcategoria'], ' >');
    $cerrarUrl = $queryBase(['editar' => null, 'nuevo' => null, 'pagina' => $pagina]);
    $tituloModal = $esAlta ? 'Agregar producto' : 'Editar producto';
    $inv = static function (string $campo) use ($formErrores, $p): string {
        if ($formErrores === []) {
            return '';
        }
        $v = $p[$campo] ?? '';
        if ($campo === 'ean' && strlen(preg_replace('/\D+/', '', (string) $v) ?? '') !== 13) {
            return ' is-invalid';
        }
        if (in_array($campo, ['precio', 'costo'], true) && (float) $v <= 0) {
            return ' is-invalid';
        }
        if (in_array($campo, ['puntoReorden', 'capacidadGondola'], true) && (int) $v < 1) {
            return ' is-invalid';
        }
        if ($campo === 'descripcion' && mb_strlen(trim((string) $v)) < 10) {
            return ' is-invalid';
        }
        if ($campo === 'nombre' && mb_strlen(trim((string) $v)) < 3) {
            return ' is-invalid';
        }
        if (in_array($campo, ['unidad', 'categoria', 'subcategoria'], true) && trim((string) $v) === '') {
            return ' is-invalid';
        }
        if ($campo === 'precio' && (float) ($p['precio'] ?? 0) > 0 && (float) ($p['costo'] ?? 0) > 0
            && (float) $p['precio'] <= (float) $p['costo']) {
            return ' is-invalid';
        }
        return '';
    };
    $abcInvalido = $formErrores !== [] && !in_array((string) ($p['abc'] ?? ''), ['A', 'B', 'C'], true);
  ?>
  <div class="prod-modal-backdrop" id="prodModal" role="dialog" aria-modal="true" aria-labelledby="prodModalTitle">
    <div class="prod-modal">
      <header class="prod-modal-head">
        <div>
          <p class="prod-modal-crumb">
            <?= $esAlta ? 'Nuevo SKU → asignación automática WMS' : ('Gestionar producto → ' . e($p['sku']) . ' (' . e((string) $p['marca']) . ')') ?>
          </p>
          <h2 id="prodModalTitle"><?= e($tituloModal) ?> <span class="ok-dot" aria-hidden="true">✓</span></h2>
          <p class="prod-modal-sub">
            <?= $esAlta
              ? 'Completa ficha, precios y parámetros de surtido'
              : (e((string) $p['nombre']) . ' · EAN-13 ' . e((string) $p['ean'])) ?>
          </p>
        </div>
        <div class="prod-modal-head-right">
          <label class="activo-switch">
            <input type="checkbox" name="activo_ui" form="formProducto" value="1" <?= !empty($p['activo']) ? 'checked' : '' ?> />
            <span>ACTIVO</span>
          </label>
          <a class="prod-modal-close" href="<?= e($cerrarUrl) ?>" aria-label="Cerrar">×</a>
        </div>
      </header>

      <form
        method="post"
        action="productos.php"
        class="prod-modal-body"
        id="formProducto"
        data-modo="<?= $esAlta ? 'crear' : 'editar' ?>"
        novalidate
      >
        <input type="hidden" name="accion" value="<?= $esAlta ? 'crear_producto' : 'guardar_producto' ?>" />
        <?php if (!$esAlta): ?>
          <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
        <?php endif; ?>
        <input type="hidden" name="activo" id="activoHidden" value="<?= !empty($p['activo']) ? '1' : '0' ?>" />
        <input type="hidden" name="marca" value="<?= e((string) ($p['marca'] ?: 'MASS')) ?>" />

        <?php if ($esAlta): ?>
          <aside class="prod-rules" aria-label="Reglas de negocio">
            <strong>Reglas de negocio (alta WMS)</strong>
            <ul>
              <li>Nombre comercial ≥ 3 caracteres y único en catálogo</li>
              <li>EAN-13 con exactamente 13 dígitos y no repetido</li>
              <li>Categoría, subcategoría, unidad y descripción (≥ 10) obligatorios</li>
              <li>PVP y costo &gt; 0 · PVP debe ser mayor al costo</li>
              <li>Punto de reorden ≥ 1 y menor a la capacidad de góndola</li>
              <li>Clasificación ABC obligatoria (A, B o C)</li>
            </ul>
          </aside>
        <?php endif; ?>

        <div
          id="prodClientErrors"
          class="prod-toast prod-toast--error"
          role="alert"
          <?= $formErrores === [] ? 'hidden' : '' ?>
        >
          <?php if ($formErrores !== []): ?>
            <strong>No se puede guardar: completa o selecciona los campos requeridos</strong>
            <ul class="prod-error-list">
              <?php foreach ($formErrores as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <section class="prod-section">
          <h3>1. Información básica del artículo</h3>
          <div class="prod-grid-3">
            <label class="field">
              <span>SKU interno</span>
              <input type="text" value="<?= e($p['skuInterno']) ?>" readonly class="is-locked" />
            </label>
            <label class="field">
              <span>Código de barras (EAN-13)</span>
              <input type="text" name="ean" value="<?= e((string) $p['ean']) ?>" required maxlength="13" inputmode="numeric" pattern="\d{13}" placeholder="7756123489123" class="<?= e(trim($inv('ean'))) ?>" />
            </label>
            <label class="field">
              <span>Unidad de medida</span>
              <input type="text" name="unidad" value="<?= e((string) $p['unidad']) ?>" required placeholder="Unidad (Botella 900ml)" class="<?= e(trim($inv('unidad'))) ?>" />
            </label>
          </div>
          <label class="field">
            <span>Nombre comercial</span>
            <input type="text" name="nombre" value="<?= e((string) $p['nombre']) ?>" required minlength="3" placeholder="Ej. Aceite Primor Clásico 900ml" class="<?= e(trim($inv('nombre'))) ?>" />
          </label>
          <div class="prod-grid-2">
            <label class="field">
              <span>Categoría</span>
              <input type="text" name="categoria" value="<?= e((string) $p['categoria']) ?>" required class="<?= e(trim($inv('categoria'))) ?>" />
            </label>
            <label class="field">
              <span>Subcategoría</span>
              <input type="text" name="subcategoria" value="<?= e((string) $p['subcategoria']) ?>" required class="<?= e(trim($inv('subcategoria'))) ?>" />
            </label>
          </div>
          <p class="field-hint">Ruta: <?= e($catPath !== '' ? $catPath : '—') ?></p>
          <label class="field">
            <span>Descripción / ficha de góndola</span>
            <textarea name="descripcion" rows="3" required minlength="10" placeholder="Detalle comercial para góndola y WMS…" class="<?= e(trim($inv('descripcion'))) ?>"><?= e((string) $p['descripcion']) ?></textarea>
          </label>
        </section>

        <section class="prod-section">
          <h3>2. Estructura de precios, costos &amp; margen</h3>
          <div class="prod-grid-3">
            <label class="field">
              <span>Costo unitario proveedor</span>
              <input type="number" step="0.01" min="0.01" name="costo" id="costoInput" value="<?= $p['costo'] > 0 ? e(number_format((float) $p['costo'], 2, '.', '')) : '' ?>" required class="<?= e(trim($inv('costo'))) ?>" />
            </label>
            <label class="field">
              <span>Precio de venta al público (PVP)</span>
              <input type="number" step="0.01" min="0.1" name="precio" id="precioInput" value="<?= $p['precio'] > 0 ? e(number_format((float) $p['precio'], 2, '.', '')) : '' ?>" required class="is-pvp<?= e($inv('precio')) ?>" />
            </label>
            <div class="margen-box" id="margenBox">
              <span>Margen comercial calculado</span>
              <strong id="margenPctLabel"><?= e(number_format((float) $p['margenPct'], 2)) ?>%</strong>
              <small id="margenBrutoLabel">(<?= e(dinero((float) $p['margenBruto'])) ?> bruto)</small>
              <em>Impuestos: IGV 18% incluido · PVP &gt; costo</em>
            </div>
          </div>
        </section>

        <section class="prod-section">
          <h3>3. Control de surtido &amp; parámetros WMS</h3>
          <div class="prod-grid-3">
            <div class="wms-stat">
              <span>Stock piso / almacén</span>
              <strong><?= (int) $p['stockPiso'] ?> / <?= (int) $p['stockAlmacen'] ?></strong>
            </div>
            <label class="field">
              <span>Punto de reorden (disparador)</span>
              <input type="number" min="1" name="puntoReorden" value="<?= (int) $p['puntoReorden'] > 0 ? (int) $p['puntoReorden'] : '' ?>" required placeholder="Ej. 25" class="<?= e(trim($inv('puntoReorden'))) ?>" />
            </label>
            <label class="field">
              <span>Capacidad máxima góndola</span>
              <input type="number" min="1" name="capacidadGondola" value="<?= (int) $p['capacidadGondola'] > 0 ? (int) $p['capacidadGondola'] : '' ?>" required placeholder="Ej. 200" class="<?= e(trim($inv('capacidadGondola'))) ?>" />
            </label>
          </div>
        </section>

        <section class="prod-section">
          <h3>4. Clasificación ABC estratégica</h3>
          <div class="abc-pick" role="radiogroup" aria-label="Clase ABC">
            <?php foreach (['A' => 'Alta rotación', 'B' => 'Rotación media', 'C' => 'Baja rotación'] as $clase => $label): ?>
              <label class="abc-option <?= ($p['abc'] ?? '') === $clase ? 'is-active' : '' ?><?= $abcInvalido ? ' is-invalid-abc' : '' ?>">
                <input type="radio" name="abc" value="<?= $clase ?>" <?= ($p['abc'] ?? '') === $clase ? 'checked' : '' ?> required />
                <strong>Clase <?= $clase ?></strong>
                <span><?= e($label) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="prod-media-row">
            <figure class="prod-photo">
              <img src="<?= e($p['img']) ?>" alt="" />
              <figcaption><?= e($p['imgNombre']) ?></figcaption>
            </figure>
            <p class="prod-status-note">
              <?= !empty($p['activo']) ? 'Activo para venta y reposición' : 'Inactivo en catálogo' ?>
              · Al guardar se cierra esta ventana y se sincroniza con Oracle Retail / WMS Mass
            </p>
          </div>
        </section>

        <footer class="prod-modal-foot">
          <a class="btn-mini ghost" href="<?= e($cerrarUrl) ?>">Cancelar</a>
          <button type="submit" class="btn-mini btn-save">
            <?= $esAlta ? 'Crear producto' : 'Guardar producto' ?>
          </button>
        </footer>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/includes/admin/footer.php'; ?>
