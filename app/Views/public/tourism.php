<?php
use App\Core\View;
use App\Sections;
$meta = App\Models\Geo::mapMeta();
?>
<section class="explorer" data-explorer
  data-initial="<?= e(json_encode($initial, JSON_UNESCAPED_UNICODE)) ?>"
  data-regions="<?= e(json_encode($meta['colors'])) ?>"
  data-region-list="<?= e(json_encode($meta['regions'], JSON_UNESCAPED_UNICODE)) ?>"
  style="--c: <?= e(Sections::ALL['tourism']['color']) ?>">
  <aside class="explorer-side" aria-label="Explorar destinos">
    <div class="explorer-head">
      <div>
        <span class="eyebrow"><?= icon('compass') ?> Colombia · Modo exploración</span>
        <h1>¿A dónde te lleva la curiosidad?</h1>
        <p class="muted" style="margin:4px 0 0">Explora el mapa. Encuentra tu próximo destino.</p>
      </div>
      <div class="field">
        <?= icon('search') ?>
        <label class="sr-only" for="explorer-q">Buscar destinos por nombre</label>
        <input class="input" style="width:100%" id="explorer-q" type="search" placeholder="Busca un destino o lugar…" data-explorer-q>
      </div>
      <div class="flex gap-2">
        <label class="sr-only" for="explorer-dep">Departamento</label>
        <select class="select" style="flex:1" id="explorer-dep" data-explorer-dep>
          <option value="">Todo el país</option>
          <?php foreach ($departments as $d): ?><option value="<?= e($d['slug']) ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-outline btn-icon" type="button" data-near aria-label="Ordenar por cercanía a mi ubicación" title="Cerca de mí"><?= icon('locate') ?></button>
      </div>
      <div class="chips chips-scroll" role="group" aria-label="Categorías" style="gap:6px">
        <?php foreach ($categories as $c): ?>
          <button class="chip" type="button" style="--chip: <?= e($c['color']) ?>; min-height:36px; padding:0 14px; font-size:.85rem" data-cat="<?= e($c['slug']) ?>" aria-pressed="false"><?= icon($c['icon'] ?: 'tag') ?> <?= e($c['name']) ?></button>
        <?php endforeach; ?>
      </div>
      <div class="chips chips-scroll" role="group" aria-label="Capas del mapa" style="gap:6px">
        <?php foreach (['tourism' => 'Destinos', 'coffee' => 'Café', 'gastronomy' => 'Sabores', 'culture' => 'Cultura', 'entrepreneurship' => 'Emprendimientos'] as $k => $label): ?>
          <button class="chip" type="button" style="--chip: <?= e(Sections::ALL[$k]['color']) ?>; min-height:32px; font-size:.8rem; padding:0 12px" data-sec="<?= e($k) ?>" aria-pressed="<?= $k === 'tourism' ? 'true' : 'false' ?>"><?= icon(Sections::ALL[$k]['icon']) ?> <?= e($label) ?></button>
        <?php endforeach; ?>
      </div>
      <div class="flex gap-2 wrap">
        <button class="chip" type="button" style="--chip: #ff3f8e; min-height:32px; font-size:.8rem; padding:0 12px" data-explorer-routes aria-pressed="false"><?= icon('route') ?> Rutas sugeridas</button>
      </div>
      <nav class="crumbs" aria-label="Nivel del mapa" data-crumbs></nav>
    </div>
    <div class="explorer-list" data-explorer-list aria-live="polite">
      <p class="muted" style="padding:10px">Cargando destinos…</p>
    </div>
  </aside>
  <div class="explorer-map">
    <div data-explorer-map style="position:absolute;inset:0" role="application" aria-label="Mapa interactivo de Colombia por departamentos y municipios"></div>
    <div class="map-tools">
      <button class="btn btn-sm" type="button" data-tiles-toggle><?= icon('layers') ?> Relieve y calles</button>
    </div>
    <aside class="drawer" data-drawer aria-hidden="true" aria-label="Detalle del lugar"></aside>
    <div class="route-bar" data-route-bar>
      <span class="stops" data-route-stops></span>
      <span><strong>Mi ruta</strong><small data-route-label></small></span>
      <a class="btn btn-sm btn-primary" href="<?= e(url('/mi-ruta')) ?>">Ver ruta <?= icon('arrow-right') ?></a>
    </div>
  </div>
</section>

<?php if ($featured): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div><span class="eyebrow" style="--c: var(--verde)">Destacados</span><h2 class="title-xl">Imperdibles de Colombia</h2></div>
      <div class="rail-controls">
        <button class="btn btn-icon" type="button" data-rail-prev="tfeat" aria-label="Anterior"><?= icon('arrow-left') ?></button>
        <button class="btn btn-icon" type="button" data-rail-next="tfeat" aria-label="Siguiente"><?= icon('arrow-right') ?></button>
      </div>
    </div>
    <div class="rail" data-rail="tfeat"><?php foreach ($featured as $e): ?><?= View::partial('dest-card', ['e' => $e]) ?><?php endforeach; ?></div>
  </div>
</section>
<?php endif; ?>

<?php if ($gems): ?>
<section class="section gems">
  <div class="container">
    <div class="section-head reveal"><div><span class="eyebrow">Lugares poco conocidos</span><h2 class="title-xl">Joyas escondidas</h2></div></div>
    <div class="polaroids">
      <?php foreach (array_slice($gems, 0, 5) as $i => $g): ?>
        <a class="polaroid reveal" style="--i: <?= $i ?>" href="<?= e($g['url']) ?>"><?= img($g['cover'], 'thumb', '', '260px', true, $g['title']) ?><strong><?= e($g['title']) ?></strong><small><?= e($g['department_name']) ?></small></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($routes): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div><span class="eyebrow" style="--c: var(--turquesa)">Rutas turísticas</span><h2 class="title-xl">Itinerarios para inspirarte</h2></div>
      <a class="btn btn-outline" href="<?= e(url('/rutas')) ?>">Todas las rutas <?= icon('arrow-right', 'icon icon-arrow') ?></a>
    </div>
    <div class="grid grid-3">
      <?php foreach (array_slice($routes, 0, 3) as $i => $r): ?>
        <?= View::partial('route-card', ['r' => $r, 'i' => $i]) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
