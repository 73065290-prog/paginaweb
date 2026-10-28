<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

$tituloPagina = 'Catálogo General — Mass Online';
$descripcion = 'Catálogo general de productos Tiendas Mass. Filtra por categoría, precio y marca.';
$paginaCssExtra = ['assets/css/catalogo.css'];
$navActivo = 'catalogo';
$footerModo = 'catalogo';

$filtrosMeta = mass_filtros_catalogo();
$perPage = 6;

$cat = (string) ($_GET['cat'] ?? 'todos');
$q = (string) ($_GET['q'] ?? '');
$min = (float) ($_GET['min'] ?? 0);
$max = (float) ($_GET['max'] ?? 40);
$orden = (string) ($_GET['orden'] ?? 'relevancia');
$chip = (string) ($_GET['chip'] ?? 'todos');
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$marcasSel = isset($_GET['marcas']) && is_array($_GET['marcas']) ? array_map('strval', $_GET['marcas']) : [];
$stockOk = !isset($_GET['filtrar']) || isset($_GET['stock_ok']);
$stockBajo = !isset($_GET['filtrar']) || isset($_GET['stock_bajo']);

$filtros = [
    'cat' => $cat,
    'q' => $q,
    'min' => $min,
    'max' => $max,
    'orden' => $orden,
    'chip' => $chip,
    'marcas' => $marcasSel,
    'stock_ok' => $stockOk,
    'stock_bajo' => $stockBajo,
];

$lista = filtrar_productos($filtros);
$total = count($lista);
$totalPages = max(1, (int) ceil($total / $perPage));
if ($pagina > $totalPages) {
    $pagina = $totalPages;
}
$offset = ($pagina - 1) * $perPage;
$pageItems = array_slice($lista, $offset, $perPage);

$crumbNombre = 'Todas las categorías';
foreach ($filtrosMeta['categorias'] as $fc) {
    if ($fc['id'] === $cat) {
        $crumbNombre = $fc['nombre'];
        break;
    }
}

function catalogo_query(array $extra = []): string
{
    $base = $_GET;
    foreach ($extra as $k => $v) {
        if ($v === null) {
            unset($base[$k]);
        } else {
            $base[$k] = $v;
        }
    }
    return 'catalogo.php?' . http_build_query($base);
}

require __DIR__ . '/../includes/layouts/header.php';
?>

<main class="catalog-page">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Migas de pan">
      <a href="index.php">Inicio</a>
      <span class="sep">›</span>
      <a href="catalogo.php">Catálogo de Productos</a>
      <span class="sep">›</span>
      <span><?= e($crumbNombre) ?></span>
    </nav>

    <h1 class="catalog-title">Catálogo General de Productos</h1>

    <button class="filters-toggle" id="filtersToggle" type="button">☰ Mostrar filtros</button>

    <div class="catalog-layout">
      <aside class="filters" id="filtersPanel" aria-label="Filtros">
        <h3>Filtros</h3>
        <form method="get" action="catalogo.php" id="filtersForm">
          <input type="hidden" name="filtrar" value="1" />
          <?php if ($q !== ''): ?>
            <input type="hidden" name="q" value="<?= e($q) ?>" />
          <?php endif; ?>
          <input type="hidden" name="chip" value="<?= e($chip) ?>" />
          <input type="hidden" name="orden" value="<?= e($orden) ?>" />

          <div class="filter-block">
            <h4>Categorías</h4>
            <ul class="filter-list">
              <?php foreach ($filtrosMeta['categorias'] as $fc): ?>
                <li>
                  <a class="<?= $cat === $fc['id'] ? 'active' : '' ?>" href="<?= e(catalogo_query(['cat' => $fc['id'], 'pagina' => 1])) ?>" style="display:flex;justify-content:space-between;padding:0.55rem 0.7rem;border-radius:10px;font-weight:600;<?= $cat === $fc['id'] ? 'background:var(--verde);color:#fff' : '' ?>">
                    <?= e($fc['nombre']) ?><span><?= (int) $fc['count'] ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
            <input type="hidden" name="cat" value="<?= e($cat) ?>" />
          </div>

          <div class="filter-block">
            <h4>Rango de precio</h4>
            <div class="price-range">
              <input class="range-slider" id="priceMaxRange" type="range" min="0" max="40" step="0.5" value="<?= e((string) $max) ?>" />
              <div class="price-inputs">
                <label>Min <input name="min" id="priceMin" type="number" min="0" value="<?= e((string) $min) ?>" step="0.1" /></label>
                <label>Max <input name="max" id="priceMax" type="number" min="0" value="<?= e((string) $max) ?>" step="0.1" /></label>
              </div>
            </div>
          </div>

          <div class="filter-block">
            <h4>Marcas populares</h4>
            <div class="check-list">
              <?php foreach ($filtrosMeta['marcas'] as $m): ?>
                <label>
                  <input type="checkbox" name="marcas[]" value="<?= e($m) ?>" <?= in_array($m, $marcasSel, true) ? 'checked' : '' ?> />
                  <?= e($m) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="filter-block">
            <h4>Disponibilidad en tienda</h4>
            <div class="check-list">
              <label><input type="checkbox" name="stock_ok" value="1" <?= $stockOk ? 'checked' : '' ?> /> Con stock disponible</label>
              <label><input type="checkbox" name="stock_bajo" value="1" <?= $stockBajo ? 'checked' : '' ?> /> Bajo stock</label>
            </div>
          </div>

          <div class="filter-block">
            <h4>Consumo responsable</h4>
            <div class="check-list">
              <label><input type="checkbox" name="corta" value="1" /> Lotes de corta rotación</label>
            </div>
          </div>

          <button class="btn btn-verde" type="submit" style="width:100%;margin-top:0.75rem">Aplicar filtros</button>
        </form>

        <div class="filters-info">
          <strong>💡 Tip Mass</strong>
          Los precios y el stock se actualizan según tu sede seleccionada.
        </div>
      </aside>

      <div class="catalog-main">
        <form class="catalog-toolbar" method="get" action="catalogo.php">
          <input type="hidden" name="cat" value="<?= e($cat) ?>" />
          <input type="hidden" name="min" value="<?= e((string) $min) ?>" />
          <input type="hidden" name="max" value="<?= e((string) $max) ?>" />
          <input type="hidden" name="chip" value="<?= e($chip) ?>" />
          <input type="hidden" name="filtrar" value="1" />
          <?php if ($stockOk): ?><input type="hidden" name="stock_ok" value="1" /><?php endif; ?>
          <?php if ($stockBajo): ?><input type="hidden" name="stock_bajo" value="1" /><?php endif; ?>
          <?php foreach ($marcasSel as $m): ?>
            <input type="hidden" name="marcas[]" value="<?= e($m) ?>" />
          <?php endforeach; ?>

          <div class="catalog-search">
            <span aria-hidden="true">🔍</span>
            <input name="q" type="search" placeholder="Buscar en este catálogo…" value="<?= e($q) ?>" />
          </div>
          <div class="toolbar-right">
            <label for="sortSelect">Ordenar por</label>
            <select id="sortSelect" name="orden" onchange="this.form.submit()">
              <option value="relevancia" <?= $orden === 'relevancia' ? 'selected' : '' ?>>Relevancia</option>
              <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
              <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
              <option value="nombre" <?= $orden === 'nombre' ? 'selected' : '' ?>>Nombre A–Z</option>
            </select>
          </div>
        </form>

        <div class="quick-chips">
          <?php
          $chips = [
              'todos' => 'Todos',
              'precio_bajo' => 'Precio bajo',
              'ofertas' => 'Ofertas',
              'vendidos' => 'Más vendidos',
          ];
          foreach ($chips as $key => $label):
          ?>
            <a class="chip <?= $chip === $key ? 'active' : '' ?>" href="<?= e(catalogo_query(['chip' => $key, 'pagina' => 1])) ?>"><?= e($label) ?></a>
          <?php endforeach; ?>
        </div>

        <p class="results-meta">
          Mostrando <strong><?= count($pageItems) ?></strong> de <strong><?= $total ?></strong> productos
        </p>

        <div class="catalog-grid">
          <?php if (!$pageItems): ?>
            <div class="empty catalog-empty" style="grid-column:1/-1" role="status">
              <strong>No encontramos productos con estos filtros</strong>
              <p>Prueba ampliar el rango de precio, marcar stock disponible o quitar marcas.</p>
              <a class="btn btn-verde" href="catalogo.php">Limpiar filtros</a>
            </div>
          <?php else: ?>
            <?php foreach ($pageItems as $i => $p): ?>
              <?php if ($i === 3): ?>
                <div class="catalog-banner">
                  <div>
                    <h3>Aprovecha más con Lotes de Corta Vencimiento</h3>
                    <p>Calidad 100% y hasta 50% de dcto. Productos frescos con consumo responsable.</p>
                  </div>
                  <a class="btn btn-verde" href="<?= e(catalogo_query(['chip' => 'ofertas', 'pagina' => 1])) ?>">Ver Productos Aprovecha →</a>
                </div>
              <?php endif; ?>
              <?php render_product_card($p); ?>
            <?php endforeach; ?>
            <?php if (count($pageItems) < 4): ?>
              <div class="catalog-banner">
                <div>
                  <h3>Aprovecha más con Lotes de Corta Vencimiento</h3>
                  <p>Calidad 100% y hasta 50% de dcto. Productos frescos con consumo responsable.</p>
                </div>
                <a class="btn btn-verde" href="<?= e(catalogo_query(['chip' => 'ofertas', 'pagina' => 1])) ?>">Ver Productos Aprovecha →</a>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <nav class="pagination" aria-label="Paginación">
          <a class="chip" style="min-width:40px;justify-content:center;<?= $pagina <= 1 ? 'opacity:.4;pointer-events:none' : '' ?>" href="<?= e(catalogo_query(['pagina' => max(1, $pagina - 1)])) ?>">‹</a>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="chip <?= $pagina === $i ? 'active' : '' ?>" style="min-width:40px;justify-content:center" href="<?= e(catalogo_query(['pagina' => $i])) ?>"><?= $i ?></a>
          <?php endfor; ?>
          <a class="chip" style="min-width:40px;justify-content:center;<?= $pagina >= $totalPages ? 'opacity:.4;pointer-events:none' : '' ?>" href="<?= e(catalogo_query(['pagina' => min($totalPages, $pagina + 1)])) ?>">›</a>
        </nav>
      </div>
    </div>
  </div>
</main>

<?php require __DIR__ . '/../includes/layouts/footer.php'; ?>
