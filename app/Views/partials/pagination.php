<?php if ($pages > 1): $q = $_GET; ?>
<nav class="pagination" aria-label="Paginación">
  <?php if ($page > 1): $q['pagina'] = $page - 1; ?><a href="?<?= e(http_build_query($q)) ?>" aria-label="Página anterior"><?= icon('chevron-left') ?></a><?php endif; ?>
  <?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): $q['pagina'] = $p; ?>
    <?php if ($p === $page): ?><span class="is-current" aria-current="page"><?= $p ?></span><?php else: ?><a href="?<?= e(http_build_query($q)) ?>"><?= $p ?></a><?php endif; ?>
  <?php endfor; ?>
  <?php if ($page < $pages): $q['pagina'] = $page + 1; ?><a href="?<?= e(http_build_query($q)) ?>" aria-label="Página siguiente"><?= icon('chevron-right') ?></a><?php endif; ?>
</nav>
<?php endif; ?>
