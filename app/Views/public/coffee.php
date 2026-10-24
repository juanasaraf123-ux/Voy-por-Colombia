<?php
use App\Core\View;
$imgs = array_values(array_filter(array_map(static fn ($e) => $e['cover'], $items)));
?>
<?= View::partial('page-hero', ['info' => $info, 'title' => 'Del grano', 'titleAccent' => 'a la taza', 'images' => [$info['cover'] ?? null, $imgs[0] ?? null], 'sticker' => ['100%', 'arábico']]) ?>

<?php if ($process): ?>
<section class="section coffee-process" aria-labelledby="proc-title">
  <div class="container">
    <span class="eyebrow">El viaje del café</span>
    <h2 class="title-xl reveal" id="proc-title" style="color:#fff">Seis pasos, <span class="italic-accent" style="color:var(--amarillo)">mil manos</span></h2>
    <div class="process-track">
      <?php foreach (array_slice($process, 0, 6) as $i => $p): ?>
        <a class="process-step reveal" style="--i: <?= $i ?>" href="<?= e($p['url']) ?>">
          <div class="num"><?= img($p['cover'], 'thumb', '', '120px', true, $p['title']) ?><b><?= $i + 1 ?></b></div>
          <h3><?= e($p['title']) ?></h3>
          <p><?= e(str_limit($p['summary'], 110)) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($farms): ?>
<section class="section-tight" aria-labelledby="farms-title">
  <div class="container">
    <div class="section-head reveal">
      <div><span class="eyebrow" style="--c: var(--selva-2)">Mapa cafetero</span><h2 class="title-lg" id="farms-title">Fincas, rutas y experiencias en el mapa</h2></div>
      <a class="btn btn-outline" href="<?= e(url('/turismo')) ?>">Abrir el explorador <?= icon('arrow-right', 'icon icon-arrow') ?></a>
    </div>
    <div class="route-map reveal-scale" style="position:relative;top:0;height:440px" data-route-map data-mode="points" data-stops="<?= e(json_encode(array_map(static fn ($f) => ['title' => $f['title'], 'lat' => $f['lat'], 'lng' => $f['lng'], 'thumb' => $f['thumb'], 'color' => $f['color']], $farms), JSON_UNESCAPED_UNICODE)) ?>"></div>
  </div>
</section>
<?php endif; ?>

<section class="section" style="--c: <?= e($info['def']['color']) ?>">
  <div class="container">
    <div class="section-head reveal"><div><span class="eyebrow">Cultura cafetera</span><h2 class="title-xl">Fincas, productores y catas</h2></div></div>
    <?= View::partial('filters', compact('filters', 'categories', 'departments', 'municipalities', 'regions') + ['placeholder' => 'Busca una finca, variedad o método…']) ?>
    <?= View::partial('results-info', compact('total', 'filters')) ?>
    <?php if ($items): ?>
      <div class="grid grid-3"><?php foreach ($items as $i => $e): ?><?= View::partial('card', ['e' => $e, 'reveal' => true, 'i' => $i % 3]) ?><?php endforeach; ?></div>
      <?= View::partial('pagination', compact('page', 'pages')) ?>
    <?php else: ?>
      <?= View::partial('empty', ['icon' => 'coffee']) ?>
    <?php endif; ?>
  </div>
</section>
