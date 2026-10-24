<div class="results-info">
  <span><?= (int) $total ?> <?= $total === 1 ? 'resultado' : 'resultados' ?></span>
  <?php if ($filters['q'] || $filters['category'] || $filters['region'] || $filters['department_id']): ?>
    <a class="link-arrow" href="<?= e(strtok($_SERVER['REQUEST_URI'] ?? '', '?')) ?>">Limpiar filtros</a>
  <?php endif; ?>
</div>
