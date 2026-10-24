<?php
use App\Core\View;
use App\Models\Media;
use App\Sections;
View::share('transparentHeader', true);
?>
<header class="entry-hero dep-hero" style="--c: <?= e($dep['region_color'] ?? '#2bb673') ?>">
  <?= img($cover, 'large', '', '100vw', false, $dep['name']) ?>
  <div class="container">
    <nav class="breadcrumbs"><a href="<?= e(url('/')) ?>">Inicio</a><?= icon('chevron-right') ?><a href="<?= e(url('/turismo')) ?>">Turismo</a><?= icon('chevron-right') ?><span><?= e($dep['name']) ?></span></nav>
    <div class="flex gap-2 wrap mt-2"><?php if ($dep['region_name']): ?><span class="badge" style="background:<?= e($dep['region_color']) ?>;color:#fff">Región <?= e($dep['region_name']) ?></span><?php endif; ?><?php if ($dep['capital']): ?><span class="badge">Capital: <?= e($dep['capital']) ?></span><?php endif; ?></div>
    <h1><?= e($dep['name']) ?></h1>
    <p class="lead"><?= e($dep['summary']) ?></p>
    <a class="btn btn-primary mt-2" href="<?= e(url('/turismo', ['departamento' => $dep['slug']])) ?>"><?= icon('map') ?> Explorar sus municipios en el mapa</a>
  </div>
  <?= View::partial('wave') ?>
</header>
<section class="section" style="padding-top: 30px">
  <div class="container">
    <?php if (!$total): ?>
      <?= View::partial('empty', ['icon' => 'compass', 'title' => 'Aún no hay publicaciones en ' . $dep['name'], 'text' => 'Muy pronto agregaremos destinos, sabores e historias de este departamento.']) ?>
    <?php endif; ?>
    <?php foreach (Sections::ALL as $key => $def): if (empty($bySection[$key])) { continue; } ?>
      <div class="section-block" style="--c: <?= e($def['color']) ?>">
        <div class="section-block-head"><span class="ico"><?= icon($def['icon']) ?></span><h2 class="title-lg" style="margin:0"><?= e($def['label']) ?></h2></div>
        <div class="grid grid-3"><?php foreach ($bySection[$key] as $i => $e): ?><?= View::partial('card', ['e' => $e, 'reveal' => true, 'i' => $i % 3]) ?><?php endforeach; ?></div>
      </div>
    <?php endforeach; ?>
    <?php if ($photos): ?>
      <div class="section-block">
        <div class="section-block-head" style="--c: var(--amarillo)"><span class="ico"><?= icon('camera') ?></span><h2 class="title-lg" style="margin:0">Galería</h2></div>
        <div class="photo-strip" data-gallery-group>
          <?php foreach ($photos as $p): ?><a href="<?= e(url('/galeria', ['departamento' => $dep['id']])) ?>" data-lightbox-item='<?= e(json_encode(Media::toJson($p), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'><?= img($p, 'medium', '', '30vw', true) ?><span><?= e($p['title']) ?></span></a><?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
