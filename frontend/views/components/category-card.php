<?php
declare(strict_types=1);

/** @var array $c */
$c = $c ?? [];
?>
<a class="cat" href="catalogo.php?cat=<?= urlencode((string) $c['cat']) ?>">
  <div class="cat-img">
    <?php if (($c['badge'] ?? '') !== ''): ?>
      <span class="cat-tag <?= e((string) ($c['badgeTone'] ?? '')) ?>"><?= e((string) $c['badge']) ?></span>
    <?php endif; ?>
    <img src="<?= e((string) $c['img']) ?>" alt="<?= e((string) $c['nombre']) ?>" loading="lazy" />
  </div>
  <div class="cat-body">
    <strong><?= e((string) $c['nombre']) ?></strong>
    <small><?= (int) ($c['count'] ?? 0) ?> productos</small>
  </div>
</a>
