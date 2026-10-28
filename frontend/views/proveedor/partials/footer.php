<?php
declare(strict_types=1);
$flashMsg = $flash ?? '';
?>
    </div>
  </div>
</div>
<?php if ($flashMsg !== ''): ?>
  <div class="toast show" id="toast" role="status" aria-live="polite" aria-atomic="true"><?= e($flashMsg) ?></div>
<?php endif; ?>
<script src="<?= e(mass_asset('assets/js/admin.js')) ?>"></script>
</body>
</html>
