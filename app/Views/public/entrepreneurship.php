<?php
use App\Core\View;
$imgs = array_values(array_filter(array_map(static fn ($e) => $e['cover'], $items)));
?>
<?= View::partial('page-hero', ['info' => $info, 'title' => 'Hecho en los', 'titleAccent' => 'territorios', 'images' => [$info['cover'] ?? null, $imgs[1] ?? null], 'sticker' => [(string) $total, 'proyectos'],
    'heroExtra' => '<p class="demo-note mt-2">' . icon('info') . '<span>Los emprendimientos publicados actualmente son <strong>fichas de demostración</strong> creadas para mostrar el diseño. Se reemplazarán por proyectos reales registrados desde el panel.</span></p>']) ?>
<section class="section" style="--c: <?= e($info['def']['color']) ?>; padding-top: 0">
  <div class="container">
    <?= View::partial('filters', compact('filters', 'categories', 'departments', 'municipalities', 'regions') + ['placeholder' => 'Busca por nombre, producto o servicio…']) ?>
    <?= View::partial('results-info', compact('total', 'filters')) ?>
    <?php if ($items): ?>
      <div class="grid grid-2">
        <?php foreach ($items as $i => $e): ?>
          <article class="biz-card reveal" style="--i: <?= $i % 2 ?>; --c: <?= e($info['def']['color']) ?>">
            <?= img($e['cover'], 'thumb', '', '180px', true, $e['title']) ?>
            <div class="card-body">
              <div class="flex gap-2 wrap">
                <?php foreach (array_slice($e['categories'], 0, 2) as $c): ?><span class="mini-tag" style="--t: <?= e($c['color']) ?>"><?= e($c['name']) ?></span><?php endforeach; ?>
                <?php if ($e['is_demo']): ?><span class="badge badge-demo">Demostración</span><?php endif; ?>
              </div>
              <h3><a href="<?= e($e['url']) ?>" style="position:static"><?= e($e['title']) ?></a></h3>
              <p><?= e(str_limit($e['summary'], 120)) ?></p>
              <div class="card-meta"><?php if ($e['municipality_name']): ?><span><?= icon('pin') ?> <?= e($e['municipality_name'] . ', ' . $e['department_name']) ?></span><?php endif; ?></div>
              <?php if (!empty($e['meta']['products'])): ?><div class="card-cats"><?php foreach (array_slice((array) $e['meta']['products'], 0, 3) as $p): ?><span class="mini-tag"><?= e($p) ?></span><?php endforeach; ?></div><?php endif; ?>
              <a class="link-arrow" style="margin-top:8px;align-self:flex-start" href="<?= e($e['url']) ?>">Ver ficha <?= icon('arrow-right') ?></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?= View::partial('pagination', compact('page', 'pages')) ?>
    <?php else: ?>
      <?= View::partial('empty', ['icon' => 'store', 'title' => 'Aún no hay emprendimientos con esos filtros']) ?>
    <?php endif; ?>
  </div>
</section>
