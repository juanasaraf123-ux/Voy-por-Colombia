<?php
use App\Core\View;
$imgs = array_values(array_filter(array_map(static fn ($e) => $e['cover'], $items)));
?>
<?= View::partial('page-hero', ['info' => $info, 'title' => 'Un país que se cuenta', 'titleAccent' => 'en la mesa', 'images' => [$info['cover'] ?? ($imgs[0] ?? null), $imgs[1] ?? null], 'sticker' => [(string) $total, 'sabores']]) ?>
<section class="section" style="--c: <?= e($info['def']['color']) ?>; padding-top: 0">
  <div class="container">
    <?= View::partial('filters', compact('filters', 'categories', 'departments', 'municipalities', 'regions') + ['placeholder' => 'Busca un plato, bebida o ingrediente…']) ?>
    <?= View::partial('results-info', compact('total', 'filters')) ?>
    <?php if ($items): ?>
      <div class="food-grid">
        <?php foreach ($items as $i => $e): $type = $e['meta']['dish_type'] ?? ($e['categories'][0]['name'] ?? 'Sabor'); ?>
          <article class="food-card reveal" style="--i: <?= $i % 3 ?>">
            <a href="<?= e($e['url']) ?>" aria-label="<?= e($e['title']) ?>"><?= img($e['cover'], 'medium', '', '(max-width: 700px) 100vw, 400px', true, $e['title']) ?></a>
            <div class="food-body">
              <div class="flex between items-center"><span class="type"><?= e($type) ?></span>
                <button class="fav-btn" style="background:var(--arena)" type="button" data-fav="<?= (int) $e['id'] ?>" aria-label="Guardar en favoritos"><span class="off"><?= icon('heart') ?></span><span class="on"><?= icon('heart-fill') ?></span></button></div>
              <h3><a href="<?= e($e['url']) ?>"><?= e($e['title']) ?></a></h3>
              <p><?= e(str_limit($e['summary'], 150)) ?></p>
              <?php if (!empty($e['meta']['ingredients'])): ?>
                <div class="ingredients"><?php foreach (array_slice((array) $e['meta']['ingredients'], 0, 4) as $ing): ?><span><?= e($ing) ?></span><?php endforeach; ?></div>
              <?php endif; ?>
              <?php if ($e['department_name']): ?><p class="card-meta" style="margin-top:12px"><?= icon('pin') ?> <?= e($e['department_name']) ?></p><?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?= View::partial('pagination', compact('page', 'pages')) ?>
    <?php else: ?>
      <?= View::partial('empty', ['icon' => 'fork', 'title' => 'No encontramos sabores con esos filtros']) ?>
    <?php endif; ?>
  </div>
</section>
