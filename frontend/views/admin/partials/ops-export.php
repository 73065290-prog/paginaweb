<?php
declare(strict_types=1);
/** @var array<string, scalar|null> $opsQuery */
/** @var string $opsExportTitulo */
?>
<div class="ops-exports" aria-label="Exportar <?= e($opsExportTitulo) ?>">
  <a href="?<?= e(mass_ops_query($opsQuery + ['export' => 'csv'])) ?>">CSV</a>
  <a href="?<?= e(mass_ops_query($opsQuery + ['export' => 'xls'])) ?>">Excel</a>
  <a href="?<?= e(mass_ops_query($opsQuery + ['export' => 'pdf'])) ?>">PDF</a>
</div>
