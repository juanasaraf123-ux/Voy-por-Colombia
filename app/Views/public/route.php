<?php
use App\Core\View;
View::share('transparentHeader', true);
$stopsJson = array_map(static fn ($s) => ['id' => (int) $s['id'], 'title' => $s['title'], 'lat' => $s['lat'] !== null ? (float) $s['lat'] : null, 'lng' => $s['lng'] !== null ? (float) $s['lng'] : null], $stops);
?>
<header class="entry-hero" style="--c: var(--turquesa); min-height: 70svh">
  <?= img($route['cover'], 'large', '', '100vw', false, $route['title']) ?>
  <div class="container">
    <nav class="breadcrumbs"><a href="<?= e(url('/')) ?>">Inicio</a><?= icon('chevron-right') ?><a href="<?= e(url('/rutas')) ?>">Rutas</a></nav>
    <h1><?= e($route['title']) ?></h1>
    <p class="lead"><?= e($route['summary']) ?></p>
    <div class="flex gap-2 wrap mt-2">
      <?php if ($route['duration']): ?><span class="badge"><?= icon('clock') ?> <?= e($route['duration']) ?></span><?php endif; ?>
      <span class="badge"><?= icon('pin') ?> <?= count($stops) ?> paradas</span>
    </div>
    <div class="flex gap-2 wrap mt-4">
      <button class="btn btn-primary" type="button" data-route-copy="<?= e(implode(',', array_column($stops, 'id'))) ?>"><?= icon('route') ?> Usar como mi ruta</button>
      <button class="btn btn-glass" type="button" data-share="<?= e($route['title']) ?>"><?= icon('share') ?> Compartir</button>
    </div>
  </div>
  <?= View::partial('wave') ?>
</header>
<section class="section" style="padding-top: 20px">
  <div class="container">
    <div class="my-route">
      <div>
        <?php if ($route['body']): ?><div class="prose" style="margin-bottom:24px"><?= md($route['body']) ?></div><?php endif; ?>
        <div class="stop-list">
          <?php foreach ($stops as $s): ?>
            <a class="stop" href="<?= e($s['url']) ?>">
              <?= img($s['cover'], 'thumb', '', '70px', true, $s['title']) ?>
              <span><strong><?= e($s['title']) ?></strong><small><?= e(trim(($s['municipality_name'] ?? '') . ', ' . ($s['department_name'] ?? ''), ', ')) ?></small><?php if ($s['stop_note']): ?><small style="display:block"><?= e($s['stop_note']) ?></small><?php endif; ?></span>
              <span class="stop-actions"><span class="btn btn-sm btn-outline btn-icon"><?= icon('arrow-right') ?></span></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="route-map" data-route-map data-stops="<?= e(json_encode($stopsJson, JSON_UNESCAPED_UNICODE)) ?>"></div>
    </div>
  </div>
</section>
