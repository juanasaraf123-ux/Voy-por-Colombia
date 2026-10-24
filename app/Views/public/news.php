<?php
use App\Core\View;
$lead = $featuredNews[0] ?? null;
$rest = $lead ? array_values(array_filter($items, static fn ($n) => $n['id'] !== $lead['id'])) : $items;
?>
<section class="page-hero" style="--c: <?= e($info['def']['color']) ?>; padding-bottom: 90px">
  <div class="container">
    <span class="eyebrow"><?= icon('news') ?> <?= e($info['title']) ?></span>
    <h1>Lo que pasa en <em>los territorios</em></h1>
    <p class="lead"><?= e($info['description']) ?></p>
  </div>
  <?= View::partial('wave') ?>
</section>
<section class="section" style="--c: <?= e($info['def']['color']) ?>; padding-top: 0">
  <div class="container">
    <?= View::partial('filters', compact('filters', 'categories', 'departments', 'municipalities', 'regions') + ['placeholder' => 'Busca noticias, eventos o historias…']) ?>
    <?php if ($lead): ?>
      <div class="news-layout mt-0" style="margin-bottom:40px">
        <a class="news-lead reveal" href="<?= e($lead['url']) ?>">
          <?= img($lead['cover'], 'large', '', '(max-width: 900px) 100vw, 60vw', false, $lead['title']) ?>
          <div class="inner"><span class="news-date" style="color:var(--amarillo)">Destacada · <?= e(fecha($lead['published_at'])) ?></span><h3><?= e($lead['title']) ?></h3><p><?= e($lead['summary']) ?></p></div>
        </a>
        <div class="news-list">
          <?php foreach (array_slice($rest, 0, 4) as $n): ?>
            <article class="news-item reveal"><?= img($n['cover'], 'thumb', '', '130px', true, $n['title']) ?><div><span class="news-date"><?= e(fecha($n['published_at'])) ?></span><h3><a href="<?= e($n['url']) ?>"><?= e($n['title']) ?></a></h3></div></article>
          <?php endforeach; ?>
        </div>
      </div>
      <?php $rest = array_slice($rest, 4); ?>
    <?php endif; ?>
    <?php if (!$lead): ?><?= View::partial('results-info', compact('total', 'filters')) ?><?php endif; ?>
    <?php if ($rest): ?>
      <div class="grid grid-3"><?php foreach ($rest as $i => $e): ?><?= View::partial('card', ['e' => $e, 'reveal' => true, 'i' => $i % 3]) ?><?php endforeach; ?></div>
    <?php elseif (!$items): ?>
      <?= View::partial('empty', ['icon' => 'news', 'title' => 'No hay noticias con esos filtros']) ?>
    <?php endif; ?>
    <?= View::partial('pagination', compact('page', 'pages')) ?>
  </div>
</section>
