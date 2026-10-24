<?php
use App\Core\View;
use App\Models\Media;
use App\Sections;

View::share('transparentHeader', true);
$e = $entry;
$meta = $e['meta'];
$s = $e['section'];
$loc = trim(($e['municipality_name'] ? $e['municipality_name'] . ', ' : '') . ($e['department_name'] ?? ''), ', ');
$fieldDefs = array_column(Sections::fields($s), null, 'key');
$hasMap = $e['lat'] !== null || $e['department_slug'];
$text = static fn ($k) => trim((string) ($meta[$k] ?? ''));
$lines = static fn ($k) => array_values(array_filter(array_map('trim', (array) ($meta[$k] ?? []))));
?>
<?php if ($preview): ?>
  <div class="preview-bar" role="status"><?= icon('eye') ?> Vista previa · <?= e(status_label($e['effective_status'])) ?>
    <a class="btn btn-sm btn-light" href="<?= e(url('/admin/publicaciones/' . $e['id'] . '/editar')) ?>"><?= icon('edit') ?> Volver a editar</a></div>
<?php endif; ?>

<header class="entry-hero" style="--c: <?= e($def['color']) ?>">
  <?= img($e['cover'], 'large', '', '100vw', false, $e['title']) ?>
  <div class="container">
    <nav class="breadcrumbs" aria-label="Ruta de navegación">
      <a href="<?= e(url('/')) ?>">Inicio</a><?= icon('chevron-right') ?>
      <a href="<?= e(Sections::url($s)) ?>"><?= e($def['label']) ?></a>
      <?php if ($e['department_slug']): ?><?= icon('chevron-right') ?><a href="<?= e(url('/departamentos/' . $e['department_slug'])) ?>"><?= e($e['department_name']) ?></a><?php endif; ?>
      <?php if ($e['municipality_name']): ?><?= icon('chevron-right') ?><span><?= e($e['municipality_name']) ?></span><?php endif; ?>
    </nav>
    <div class="flex gap-2 wrap mt-2">
      <span class="badge badge-section"><?= icon($def['icon']) ?> <?= e($def['label']) ?></span>
      <?php foreach (array_slice($e['categories'], 0, 3) as $c): ?><span class="badge"><?= e($c['name']) ?></span><?php endforeach; ?>
      <?php if ($e['is_hidden_gem']): ?><span class="badge badge-gem"><?= icon('sparkle') ?> Joya escondida</span><?php endif; ?>
      <?php if ($e['is_demo'] && in_array($s, ['entrepreneurship', 'news'], true)): ?><span class="badge badge-demo">Contenido de demostración</span><?php endif; ?>
    </div>
    <h1><?= e($e['title']) ?></h1>
    <p class="lead"><?= e($e['summary']) ?></p>
    <div class="flex gap-2 wrap mt-4">
      <?php if ($s !== 'news'): ?>
        <button class="btn btn-light fav-btn" type="button" data-fav="<?= (int) $e['id'] ?>"><span class="off"><?= icon('heart') ?></span><span class="on"><?= icon('heart-fill') ?></span> Favorito</button>
        <?php if ($e['lat'] !== null): ?><button class="btn btn-glass" type="button" data-route-toggle="<?= (int) $e['id'] ?>"><?= icon('plus') ?> <span data-label>Agregar a mi ruta</span></button><?php endif; ?>
      <?php endif; ?>
      <button class="btn btn-glass" type="button" data-share="<?= e($e['title']) ?>"><?= icon('share') ?> Compartir</button>
    </div>
  </div>
  <?= View::partial('wave') ?>
</header>

<div class="container" style="--c: <?= e($def['color']) ?>">
  <div class="entry-layout">
    <article class="entry-main">
      <?php if ($s === 'news'): ?>
        <p class="card-meta" style="margin-bottom:20px"><?= icon('calendar') ?> <?= e(fecha($e['published_at'])) ?> · <?= icon('clock') ?> <?= reading_time($e['body']) ?> min de lectura<?= $e['author_name'] ? ' · ' . e($e['author_name']) : '' ?></p>
      <?php endif; ?>

      <?php if ($e['is_demo'] && in_array($s, ['entrepreneurship', 'news'], true) || ($e['is_demo'] && str_contains($e['title'], '(demo)'))): ?>
        <p class="demo-note"><?= icon('info') ?><span>Esta ficha es de <strong>demostración</strong>: los datos no corresponden a un negocio o hecho real y se reemplazarán desde el panel administrativo.</span></p>
      <?php endif; ?>

      <?php if ($e['body']): ?><div class="prose dropcap"><?= md($e['body']) ?></div><?php endif; ?>

      <?php if ($items = $lines('activities')): ?>
        <section class="block"><h2><?= icon('compass') ?> Qué hacer</h2><ul class="pill-list"><?php foreach ($items as $a): ?><li><?= e($a) ?></li><?php endforeach; ?></ul></section>
      <?php endif; ?>

      <?php if ($items = $lines('ingredients')): ?>
        <section class="block"><h2><?= icon('leaf') ?> Ingredientes</h2><ul class="ingredients-list"><?php foreach ($items as $a): ?><li><?= e($a) ?></li><?php endforeach; ?></ul></section>
      <?php endif; ?>

      <?php if ($items = $lines('preparation')): ?>
        <section class="block"><h2><?= icon('fire') ?> Preparación</h2><ol class="steps"><?php foreach ($items as $a): ?><li><span><?= e($a) ?></span></li><?php endforeach; ?></ol></section>
      <?php endif; ?>

      <?php if ($text('history')): ?>
        <section class="block"><h2><?= icon('book') ?> Historia</h2><div class="prose"><?= md($text('history')) ?></div></section>
      <?php endif; ?>

      <?php if ($items = $lines('products')): ?>
        <section class="block"><h2><?= icon('store') ?> Productos y servicios</h2><ul class="pill-list"><?php foreach ($items as $a): ?><li><?= e($a) ?></li><?php endforeach; ?></ul></section>
      <?php endif; ?>

      <?php if ($text('how_to_get')): ?>
        <section class="block"><h2><?= icon('route') ?> Cómo llegar</h2><div class="prose"><?= md($text('how_to_get')) ?></div></section>
      <?php endif; ?>

      <?php if ($items = $lines('recommendations')): ?>
        <section class="block"><h2><?= icon('info') ?> Recomendaciones</h2><ul class="pill-list"><?php foreach ($items as $a): ?><li><?= e($a) ?></li><?php endforeach; ?></ul></section>
      <?php endif; ?>

      <?php if ($text('respect_note')): ?>
        <section class="block"><p class="demo-note" style="background:color-mix(in srgb, var(--verde) 14%, white)"><?= icon('hand') ?><span><strong>Visita con respeto.</strong> <?= e($text('respect_note')) ?></span></p></section>
      <?php endif; ?>

      <?php if ($images): ?>
        <section class="block">
          <h2><?= icon('camera') ?> Galería</h2>
          <div class="entry-gallery count-<?= min(4, count($images)) ?>" data-gallery-group>
            <?php foreach ($images as $m): ?>
              <button type="button" data-lightbox-item='<?= e(json_encode(Media::toJson($m), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>' aria-label="Ampliar imagen: <?= e($m['title'] ?: $e['title']) ?>">
                <?= img($m, 'medium', '', '(max-width: 620px) 50vw, 300px', true, $m['alt_text'] ?: $e['title']) ?>
              </button>
            <?php endforeach; ?>
          </div>
          <?php $credits = array_unique(array_filter(array_column($images, 'credit'))); if ($credits): ?><p class="credit">Créditos: <?= e(implode(' · ', $credits)) ?></p><?php endif; ?>
        </section>
      <?php endif; ?>

      <?php if ($videos): ?>
        <section class="block">
          <h2><?= icon('video') ?> Videos</h2>
          <div class="grid" style="gap:18px">
            <?php foreach ($videos as $v): ?>
              <figure style="margin:0">
                <div class="video-frame">
                  <?php if ($v['kind'] === 'embed'): ?>
                    <iframe src="<?= e(Media::embedUrl($v)) ?>" title="<?= e($v['title'] ?: 'Video') ?>" loading="lazy" allow="encrypted-media; picture-in-picture" allowfullscreen></iframe>
                  <?php else: ?>
                    <video src="<?= e(media_url($v, 'original')) ?>" controls preload="metadata" playsinline></video>
                  <?php endif; ?>
                </div>
                <?php if ($v['title'] || $v['credit']): ?><figcaption class="credit"><?= e(trim($v['title'] . ($v['credit'] ? ' · ' . $v['credit'] : ''), ' ·')) ?></figcaption><?php endif; ?>
              </figure>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($audios): ?>
        <section class="block">
          <h2><?= icon('volume') ?> Escucha</h2>
          <div class="grid" style="gap:12px">
            <?php foreach ($audios as $a): ?>
              <div class="audio-card"><strong><?= e($a['title'] ?: 'Audio') ?></strong><audio src="<?= e(media_url($a, 'original')) ?>" controls preload="none"></audio><?php if ($a['credit']): ?><span class="credit"><?= e($a['credit']) ?></span><?php endif; ?></div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($documents): ?>
        <section class="block"><h2><?= icon('file') ?> Documentos</h2>
          <ul class="pill-list"><?php foreach ($documents as $d): ?><li><a href="<?= e(media_url($d, 'original')) ?>" target="_blank" rel="noopener"><?= e($d['title'] ?: $d['original_name']) ?> (PDF)</a></li><?php endforeach; ?></ul>
        </section>
      <?php endif; ?>

      <?php if ($e['cover'] && $e['cover']['credit']): ?><p class="credit mt-4">Imagen principal: <?= e($e['cover']['credit']) ?></p><?php endif; ?>
    </article>

    <aside class="entry-aside">
      <div class="facts">
        <?php if ($loc): ?><div class="fact"><span class="ico"><?= icon('pin') ?></span><div><small>Ubicación</small><span><?= e($loc) ?></span></div></div><?php endif; ?>
        <?php if ($e['region_name']): ?><div class="fact"><span class="ico"><?= icon('map') ?></span><div><small>Región</small><span><?= e($e['region_name']) ?></span></div></div><?php endif; ?>
        <?php if ($e['event_start']): ?><div class="fact"><span class="ico"><?= icon('calendar') ?></span><div><small>Fecha</small><span><?= e(fecha($e['event_start'])) ?><?= $e['event_end'] && substr($e['event_end'], 0, 10) !== substr($e['event_start'], 0, 10) ? ' al ' . e(fecha($e['event_end'])) : '' ?></span></div></div><?php endif; ?>
        <?php foreach (['best_time' => 'calendar', 'altitude' => 'mountain', 'dish_type' => 'tag', 'origin' => 'pin', 'varieties' => 'leaf', 'process' => 'cycle', 'tasting_notes' => 'star', 'venue' => 'columns', 'community' => 'users', 'schedule' => 'clock', 'contact_name' => 'user', 'address' => 'home', 'source' => 'news'] as $k => $ic): ?>
          <?php if ($text($k) && isset($fieldDefs[$k])): ?><div class="fact"><span class="ico"><?= icon($ic) ?></span><div><small><?= e($fieldDefs[$k]['label']) ?></small><span><?= e($text($k)) ?></span></div></div><?php endif; ?>
        <?php endforeach; ?>
        <?php if ($text('contact_phone')): ?><div class="fact"><span class="ico"><?= icon('phone') ?></span><div><small>Teléfono</small><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $text('contact_phone'))) ?>"><?= e($text('contact_phone')) ?></a></div></div><?php endif; ?>
        <?php if ($text('contact_email')): ?><div class="fact"><span class="ico"><?= icon('mail') ?></span><div><small>Correo</small><a href="mailto:<?= e($text('contact_email')) ?>"><?= e($text('contact_email')) ?></a></div></div><?php endif; ?>
        <?php if ($text('website')): ?><div class="fact"><span class="ico"><?= icon('globe') ?></span><div><small>Sitio web</small><a href="<?= e($text('website')) ?>" target="_blank" rel="noopener nofollow"><?= e(preg_replace('#^https?://#', '', $text('website'))) ?></a></div></div><?php endif; ?>
        <?php if ($text('instagram')): ?><div class="fact"><span class="ico"><?= icon('instagram') ?></span><div><small>Instagram</small><a href="https://instagram.com/<?= e(ltrim($text('instagram'), '@')) ?>" target="_blank" rel="noopener nofollow">@<?= e(ltrim($text('instagram'), '@')) ?></a></div></div><?php endif; ?>
        <?php if ($text('source_url')): ?><div class="fact"><span class="ico"><?= icon('link') ?></span><div><small>Fuente</small><a href="<?= e($text('source_url')) ?>" target="_blank" rel="noopener nofollow">Ver fuente original</a></div></div><?php endif; ?>
      </div>
      <?php if ($hasMap): ?>
        <div class="mini-map" data-mini-map data-lat="<?= e($e['lat'] ?? '') ?>" data-lng="<?= e($e['lng'] ?? '') ?>" data-dep="<?= e($e['department_slug'] ?? '') ?>" data-color="<?= e($def['color']) ?>" data-thumb="<?= e($e['cover'] ? media_url($e['cover'], 'thumb') : '') ?>" aria-label="Mapa de ubicación"></div>
        <?php if ($e['lat'] !== null): ?>
          <a class="btn btn-outline" href="<?= e(url('/turismo', ['lugar' => $s === 'tourism' ? $e['id'] : null, 'departamento' => $e['department_slug']])) ?>"><?= icon('map') ?> Ver en el mapa interactivo</a>
        <?php endif; ?>
      <?php endif; ?>
    </aside>
  </div>
</div>

<?php if ($nearby): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal"><div><span class="eyebrow" style="--c: var(--naranja)">Muy cerca</span><h2 class="title-lg">Lugares cercanos</h2></div></div>
    <div class="grid grid-3">
      <?php foreach (array_slice($nearby, 0, 3) as $i => $n): ?>
        <?= View::partial('card', ['e' => $n, 'showSection' => true, 'reveal' => true, 'i' => $i]) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php elseif ($related): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal"><div><span class="eyebrow">Sigue explorando</span><h2 class="title-lg">También te puede gustar</h2></div></div>
    <div class="grid grid-3"><?php foreach ($related as $i => $n): ?><?= View::partial('card', ['e' => $n, 'reveal' => true, 'i' => $i]) ?><?php endforeach; ?></div>
  </div>
</section>
<?php endif; ?>
