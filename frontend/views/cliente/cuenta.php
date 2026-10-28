<?php
declare(strict_types=1);

auth_requerir_cliente();

$tituloPagina = 'Mi cuenta — Mass Online';
$descripcion = 'Perfil y pedidos del cliente Mass Online.';
$navActivo = 'cuenta';
$footerModo = 'catalogo';

$u = auth_usuario();
$ultimo = pedido_ultimo();

require dirname(__DIR__) . '/layouts/header.php';
?>

<main class="catalog-page">
  <div class="container" style="padding-block:1.5rem 2.5rem;max-width:820px">
    <nav class="breadcrumbs" aria-label="Migas de pan">
      <a href="index.php">Inicio</a>
      <span class="sep">›</span>
      <span>Mi cuenta</span>
    </nav>

    <header style="margin:1rem 0 1.25rem">
      <h1 style="margin:0 0 0.35rem;font-size:1.6rem;letter-spacing:-0.02em">Hola, <?= e((string) ($u['nombre'] ?? 'Cliente')) ?></h1>
      <p style="margin:0;color:var(--muted)">Portal exclusivo de <strong>Cliente Mass Online</strong> — compras, canasta y pedidos personales.</p>
    </header>

    <section class="prov-table-wrap" style="margin-bottom:1.25rem;padding:1.1rem 1.25rem;background:#fff;border:1px solid var(--gris-2);border-radius:14px">
      <h2 style="margin:0 0 0.75rem;font-size:1.05rem">Tu perfil</h2>
      <p style="margin:0.25rem 0"><strong>Usuario:</strong> <?= e((string) ($u['usuario'] ?? '')) ?></p>
      <p style="margin:0.25rem 0"><strong>Rol:</strong> <?= e(auth_etiqueta_rol((string) ($u['rol'] ?? 'cliente'))) ?></p>
      <p style="margin:0.25rem 0"><strong>Cargo:</strong> <?= e((string) ($u['cargo'] ?? 'Cliente Mass Online')) ?></p>
      <p style="margin:0.85rem 0 0;display:flex;flex-wrap:wrap;gap:0.5rem">
        <a href="catalogo.php" style="display:inline-flex;padding:0.55rem 0.9rem;border-radius:10px;background:var(--verde);color:#fff;font-weight:700">Seguir comprando</a>
        <a href="pedido.php?demo=1" style="display:inline-flex;padding:0.55rem 0.9rem;border-radius:10px;background:#eef4f0;color:var(--verde);font-weight:700">Checkout demo</a>
        <a href="<?= e(mass_url('logout.php')) ?>" style="display:inline-flex;padding:0.55rem 0.9rem;border-radius:10px;border:1px solid var(--gris-2);font-weight:700">Cerrar sesión</a>
      </p>
    </section>

    <section style="padding:1.1rem 1.25rem;background:#fff;border:1px solid var(--gris-2);border-radius:14px">
      <h2 style="margin:0 0 0.75rem;font-size:1.05rem">Último pedido</h2>
      <?php if (!$ultimo): ?>
        <p style="margin:0;color:var(--muted)">Aún no tienes pedidos en esta sesión.</p>
        <p style="margin:0.85rem 0 0"><a href="pedido.php?demo=1">Iniciar pedido demo →</a></p>
      <?php else: ?>
        <p style="margin:0.25rem 0"><strong>Código:</strong> <?= e((string) ($ultimo['pedido']['codigo'] ?? $ultimo['codigo'] ?? '—')) ?></p>
        <p style="margin:0.25rem 0"><strong>Modalidad:</strong> <?= e((string) ($ultimo['pedido']['entrega'] ?? $ultimo['entrega'] ?? '—')) ?></p>
        <p style="margin:0.25rem 0"><strong>Total:</strong> <?= e(dinero((float) ($ultimo['resumen']['total'] ?? 0))) ?></p>
        <p style="margin:0.85rem 0 0"><a href="confirmacion.php">Ver confirmación / tracking →</a></p>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
