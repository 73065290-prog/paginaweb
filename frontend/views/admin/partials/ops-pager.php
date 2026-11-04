<?php
declare(strict_types=1);
/** @var array{page:int,pages:int,total:int,from:int,to:int} $opsPager */
/** @var array<string, scalar|null> $opsQuery */
?>
<nav class="ops-pager" aria-label="Paginación">
  <span><?= (int) $opsPager['from'] ?>–<?= (int) $opsPager['to'] ?> de <?= (int) $opsPager['total'] ?></span>
  <?php if ((int) $opsPager['pages'] > 1): ?>
    <?php for ($i = 1; $i <= (int) $opsPager['pages']; $i++): ?>
      <a class="<?= $i === (int) $opsPager['page'] ? 'is-active' : '' ?>" href="?<?= e(mass_ops_query($opsQuery + ['pagina' => $i])) ?>"><?= $i ?></a>
    <?php endfor; ?>
  <?php endif; ?>
</nav>
