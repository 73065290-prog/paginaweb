<?php
declare(strict_types=1);
$navActivo = $navActivo ?? 'inicio';
$unidades = carrito_total_unidades();
$monto = carrito_total_monto();
$authUser = auth_usuario();
$authNombre = $authUser['nombre'] ?? 'Mi Cuenta';
$authCargo = $authUser['cargo'] ?? '';
$authRol = $authUser['rol'] ?? '';
$authAvatar = match ($authRol) {
    'admin' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=64&h=64&fit=crop',
    'proveedor' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=64&h=64&fit=crop',
    default => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=64&h=64&fit=crop',
};
?>
<header class="header">
  <div class="container header-main">
    <a class="brand" href="index.php" aria-label="mass online">
      <img src="<?= e(mass_asset('assets/img/logo.svg')) ?>" alt="mass online" width="168" height="40" />
    </a>
    <a class="btn-cats" href="catalogo.php">☰ Categorías</a>
    <form class="search" method="get" action="catalogo.php" role="search">
      <span class="search-icon" aria-hidden="true">🔍</span>
      <label class="sr-only" for="searchInput">Buscar</label>
      <input id="searchInput" name="q" type="search" placeholder="Busca productos, marcas o categorías…" value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off" />
      <button class="btn-buscar" type="submit">Buscar</button>
    </form>
    <div class="header-actions">
      <a class="action-link" href="index.php#favoritos"><span aria-hidden="true">♡</span> <span>Favoritos</span></a>
      <button class="pill-ahorro" type="button">💡 Ahorro Inteligente</button>

      <div class="cuenta-wrap">
        <button
          type="button"
          class="cuenta"
          id="cuentaBtn"
          aria-expanded="false"
          aria-haspopup="true"
          aria-controls="cuentaMenu"
        >
          <img src="<?= e($authAvatar) ?>" alt="" />
          <span><?= e($authUser ? (string) $authNombre : 'Mi Cuenta') ?></span>
          <span class="cuenta-caret" aria-hidden="true">▾</span>
        </button>
        <div class="cuenta-menu" id="cuentaMenu" hidden>
          <?php if ($authUser): ?>
            <div class="cuenta-head">
              <strong><?= e((string) $authNombre) ?></strong>
              <span><?= e((string) $authCargo) ?></span>
              <small><?= e(auth_etiqueta_rol((string) $authRol)) ?></small>
            </div>
            <?php if ($authRol === 'admin'): ?>
              <a href="<?= e(mass_url('admin/index.php')) ?>">Ir al panel admin</a>
            <?php elseif ($authRol === 'proveedor'): ?>
              <a href="<?= e(mass_url('proveedor/index.php')) ?>">Ir al portal proveedor</a>
            <?php else: ?>
              <a href="cuenta.php">Mi cuenta cliente</a>
              <a href="pedido.php">Checkout / pedidos</a>
              <a href="catalogo.php">Seguir comprando</a>
            <?php endif; ?>
            <a class="is-logout" href="<?= e(mass_url('logout.php')) ?>">Cerrar sesión</a>
          <?php else: ?>
            <div class="cuenta-head">
              <strong>Acceso Mass</strong>
              <span>Elige tu rol</span>
            </div>
            <a href="<?= e(mass_url('admin/login.php?rol=cliente')) ?>">Soy cliente (tienda)</a>
            <a href="<?= e(mass_url('admin/login.php?rol=proveedor')) ?>">Soy proveedor (B2B)</a>
            <a href="<?= e(mass_url('admin/login.php?rol=admin')) ?>">Soy administrador WMS</a>
          <?php endif; ?>
        </div>
      </div>

      <button class="btn-carrito" id="openCart" type="button">
        <span aria-hidden="true">🛒</span>
        <span>
          <span id="cartLabel"><?= (int) $unidades ?> producto<?= $unidades === 1 ? '' : 's' ?></span>
          <small id="cartTotalNav"><?= e(dinero($monto)) ?></small>
        </span>
      </button>
      <button class="menu-toggle" id="menuToggle" type="button" aria-label="Menú">☰</button>
    </div>
  </div>
  <nav class="nav-sec" aria-label="Secciones">
    <div class="container nav-sec-inner">
      <a href="index.php" class="<?= $navActivo === 'inicio' ? 'active' : '' ?>">Inicio</a>
      <a href="index.php#ofertas" class="oferta">Ofertas Bomba</a>
      <a href="catalogo.php" class="<?= $navActivo === 'catalogo' ? 'active' : '' ?>">Catálogo</a>
      <a href="catalogo.php?cat=Frutas">Frutas y Verduras</a>
      <a href="catalogo.php?cat=Lácteos">Lácteos y Huevos</a>
      <a href="catalogo.php?cat=Bebidas">Bebidas y Licores</a>
      <a href="catalogo.php?cat=Limpieza">Limpieza y Cuidado</a>
      <button class="stock-btn" type="button">📊 Panel de Stock</button>
    </div>
  </nav>
</header>
