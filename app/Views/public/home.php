<?php
use App\Core\View;
use App\Sections;

View::share('transparentHeader', true);
$words = array_filter(array_map('trim', explode(',', (string) setting('hero_words'))));
$titleWords = preg_split('/\s+/', trim((string) setting('hero_title'))) ?: [];
$lastWord = array_pop($titleWords);
?>
<!-- A. Portada cinematográfica -->
<section class="hero" aria-label="Portada" data-hero>
  <div class="hero-slides">
    <?php if ($heroVideo): ?>
      <div class="hero-slide is-active">
        <video src="<?= e(media_url($heroVideo, 'original')) ?>" autoplay muted loop playsinline preload="metadata" <?= $heroMedia ? 'poster="' . e(media_url($heroMedia[0], 'large')) . '"' : '' ?>></video>
      </div>
    <?php else: ?>
      <?php foreach ($heroMedia as $i => $m): ?>
        <div class="hero-slide <?= $i === 0 ? 'is-active' : '' ?>" data-credit="<?= e(trim(($m['title'] ?? '') . ($m['credit'] ? ' · ' . $m['credit'] : ''), ' ·')) ?>">
          <?= img($m, 'large', '', '100vw', $i > 0, $m['alt_text'] ?? '') ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="hero-inner">
    <div>
      <span class="hero-kicker"><span class="pulse"><?= icon('infinity') ?></span><?= e(setting('hero_kicker')) ?></span>
      <h1>
        <?php foreach ($titleWords as $i => $w): ?><span class="word" style="animation-delay: <?= 120 + $i * 90 ?>ms"><?= e($w) ?></span> <?php endforeach; ?>
        <span class="word" style="animation-delay: <?= 120 + count($titleWords) * 90 ?>ms"><em><?= e($lastWord) ?></em></span>
      </h1>
      <p class="hero-sub"><?= e(setting('hero_subtitle')) ?></p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="#mapa"><?= icon('compass') ?> <?= e(setting('hero_cta')) ?></a>
        <a class="btn btn-glass" href="<?= e(url('/turismo')) ?>"><?= icon('map') ?> Abrir el mapa completo</a>
      </div>
      <?php if ($words): ?>
        <div class="hero-rotator" aria-hidden="true">Hoy me provoca
          <span class="rot" data-rotator><?php foreach ($words as $i => $w): ?><span class="<?= $i === 0 ? 'is-in' : '' ?>"><?= e($w) ?></span><?php endforeach; ?></span>
        </div>
      <?php endif; ?>
    </div>
    <div class="hero-cards" aria-label="Destinos destacados">
      <?php foreach ($heroCards as $c): ?>
        <a class="postcard" href="<?= e($c['url']) ?>">
          <?= img($c['cover'], 'thumb', '', '230px', false, $c['title']) ?>
          <strong><?= e($c['title']) ?></strong>
          <small><?= icon('pin') ?> <?= e($c['department_name']) ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (count($heroMedia) > 1 && !$heroVideo): ?>
    <div class="hero-dots" role="tablist" aria-label="Imágenes de portada">
      <?php foreach ($heroMedia as $i => $m): ?><button type="button" class="<?= $i === 0 ? 'is-active' : '' ?>" data-hero-dot="<?= $i ?>" aria-label="Imagen <?= $i + 1 ?>"></button><?php endforeach; ?>
    </div>
    <p class="hero-credit" data-hero-credit></p>
  <?php endif; ?>

  <div class="hero-stats">
    <div class="hero-stat"><b data-count="<?= (int) $stats['departamentos'] ?>"><?= (int) $stats['departamentos'] ?></b><span>departamentos</span></div>
    <div class="hero-stat"><b data-count="<?= (int) $stats['municipios'] ?>"><?= (int) $stats['municipios'] ?></b><span>municipios</span></div>
    <div class="hero-stat"><b data-count="<?= (int) $stats['destinos'] ?>"><?= (int) $stats['destinos'] ?></b><span>destinos</span></div>
    <div class="hero-stat"><b data-count="<?= (int) $stats['experiencias'] ?>"><?= (int) $stats['experiencias'] ?></b><span>experiencias</span></div>
  </div>
  <div class="hero-bottom"><?= View::partial('wave', ['class' => 'hero-wave']) ?></div>
</section>

<!-- Cinta de regiones -->
<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php for ($k = 0; $k < 2; $k++): foreach ($regions as $r): ?>
      <span class="marquee-item" style="--c: <?= e($r['color']) ?>"><i></i><?= e($r['name']) ?> · <span class="muted" style="font-size:.55em;font-style:normal;font-family:var(--font-body)"><?= e($r['summary']) ?></span></span>
    <?php endforeach; endfor; ?>
  </div>
</div>

<!-- B. Mapa interactivo -->
<section class="section" id="mapa" aria-labelledby="map-title">
  <div class="container container-wide">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow" style="--c: var(--naranja)">Mapa interactivo</span>
        <h2 class="title-xl" id="map-title"><?= e(setting('map_title')) ?></h2>
        <p class="lead"><?= e(setting('map_subtitle')) ?></p>
      </div>
      <a class="btn btn-outline" href="<?= e(url('/turismo')) ?>">Explorador completo <?= icon('arrow-right', 'icon icon-arrow') ?></a>
    </div>
    <div class="home-map reveal-scale">
      <?php $meta = App\Models\Geo::mapMeta(); ?>
      <div class="map-card" data-home-map data-regions="<?= e(json_encode($meta['colors'])) ?>" data-region-list="<?= e(json_encode($meta['regions'], JSON_UNESCAPED_UNICODE)) ?>">
        <div class="map-hint"><?= icon('sparkle') ?> Toca un departamento o un marcador</div>
        <div class="map-legend" data-map-legend></div>
        <noscript><p style="padding:30px">Activa JavaScript para usar el mapa interactivo.</p></noscript>
      </div>
      <aside class="place-panel" data-place-panel aria-live="polite">
        <?php $first = $featured[0] ?? null; if ($first): ?>
          <div class="place-panel-media"><?= img($first['cover'], 'medium', '', '420px', true, $first['title']) ?><div class="badges"><span class="badge">Destacado</span></div></div>
          <div class="place-panel-body">
            <span class="place-loc"><?= icon('pin') ?> <?= e(trim(($first['municipality_name'] ?? '') . ', ' . ($first['department_name'] ?? ''), ', ')) ?></span>
            <h3><?= e($first['title']) ?></h3>
            <p><?= e($first['summary']) ?></p>
            <div class="place-panel-actions"><a class="btn btn-primary" href="<?= e($first['url']) ?>">Descubrir <?= icon('arrow-right', 'icon icon-arrow') ?></a></div>
          </div>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</section>

<!-- C. Exploración por categorías -->
<section class="section bg-arena" aria-labelledby="cat-title" style="padding-top: clamp(64px, 8vw, 110px)">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow" style="--c: var(--morado)">Siete maneras de viajar</span>
        <h2 class="title-xl" id="cat-title">Elige por dónde <span class="italic-accent text-gradient">empezar</span></h2>
      </div>
      <p class="lead" style="max-width:42ch">Cada sección es un viaje distinto: paisajes, sabores, aromas, oficios, fiestas, imágenes e historias.</p>
    </div>
    <div class="bento">
      <?php foreach ($sections as $i => $s): ?>
        <a class="bento-tile reveal" href="<?= e($s['url']) ?>" style="--c: <?= e($s['def']['color']) ?>; --i: <?= $i ?>">
          <?= img($s['cover'], $i === 0 ? 'large' : 'medium', '', '(max-width: 620px) 100vw, 40vw', true, $s['title']) ?>
          <span class="bento-icon"><?= icon($s['def']['icon']) ?></span>
          <span class="bento-arrow"><?= icon('arrow-right') ?></span>
          <span class="tag"><?= e($s['tagline']) ?></span>
          <h3><?= e($s['title']) ?></h3>
          <p><?= e($s['description']) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- D. Destinos destacados -->
<?php if ($featured): ?>
<section class="section" aria-labelledby="feat-title">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow" style="--c: var(--verde)">Destinos destacados</span>
        <h2 class="title-xl" id="feat-title"><?= e(setting('home_featured_title')) ?></h2>
      </div>
      <div class="rail-controls">
        <button class="btn btn-icon" type="button" data-rail-prev="feat" aria-label="Anterior"><?= icon('arrow-left') ?></button>
        <button class="btn btn-icon" type="button" data-rail-next="feat" aria-label="Siguiente"><?= icon('arrow-right') ?></button>
      </div>
    </div>
    <div class="rail" data-rail="feat">
      <?php foreach ($featured as $e): ?><?= View::partial('dest-card', ['e' => $e]) ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($gems): ?>
<section class="section gems" aria-labelledby="gems-title">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow">Fuera del radar</span>
        <h2 class="title-xl" id="gems-title"><?= e(setting('home_gems_title')) ?></h2>
        <p class="lead">Pueblos sin carros, páramos de cuento y cascadas escondidas. Lugares que merecen ser descubiertos.</p>
      </div>
      <a class="btn btn-light" href="<?= e(url('/turismo')) ?>">Ver en el mapa <?= icon('arrow-right', 'icon icon-arrow') ?></a>
    </div>
    <div class="polaroids">
      <?php foreach ($gems as $i => $g): ?>
        <a class="polaroid reveal" style="--i: <?= $i ?>" href="<?= e($g['url']) ?>">
          <?= img($g['cover'], 'thumb', '', '260px', true, $g['title']) ?>
          <strong><?= e($g['title']) ?></strong>
          <small><?= e($g['department_name'] ?? Sections::ALL[$g['section']]['label']) ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- E. Contenido dinámico: experiencias y eventos -->
<?php if ($experiences): ?>
<section class="section" aria-labelledby="exp-title">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow" style="--c: var(--rosa)">Experiencias</span>
        <h2 class="title-xl" id="exp-title">Sabores, aromas y <span class="italic-accent">tradiciones</span></h2>
      </div>
    </div>
    <div class="grid grid-3">
      <?php foreach ($experiences as $i => $e): ?><?= View::partial('card', ['e' => $e, 'showSection' => true, 'reveal' => true, 'i' => $i]) ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($events): ?>
<section class="section-tight" aria-labelledby="ev-title">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow" style="--c: var(--rosa)">Agenda</span>
        <h2 class="title-lg" id="ev-title">Próximas festividades</h2>
      </div>
      <a class="link-arrow" href="<?= e(url('/cultura')) ?>#calendario">Ver calendario <?= icon('arrow-right') ?></a>
    </div>
    <div class="event-row">
      <?php foreach ($events as $i => $ev): $ts = strtotime((string) $ev['event_start']); ?>
        <article class="event-card reveal" style="--i: <?= $i ?>; --c: <?= e(Sections::ALL[$ev['section']]['color']) ?>">
          <div class="date-tile"><b><?= date('j', $ts) ?></b><span><?= e(mb_substr(MESES[(int) date('n', $ts) - 1], 0, 3)) ?></span></div>
          <div><h3><a href="<?= e($ev['url']) ?>"><?= e($ev['title']) ?></a></h3><small><?= e($ev['municipality_name'] ?? $ev['department_name'] ?? 'Todo el país') ?></small></div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($routes): ?>
<section class="section" aria-labelledby="routes-title">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow" style="--c: var(--turquesa)">Rutas sugeridas</span>
        <h2 class="title-xl" id="routes-title">Un viaje, varias paradas</h2>
      </div>
      <a class="btn btn-outline" href="<?= e(url('/mi-ruta')) ?>"><?= icon('route') ?> Arma tu propia ruta</a>
    </div>
    <div class="grid grid-3">
      <?php foreach ($routes as $i => $r): ?>
        <article class="route-card reveal" style="--i: <?= $i ?>">
          <div class="card-media"><?= img($r['cover'], 'medium', '', '400px', true, $r['title']) ?><div class="route-line"><?php for ($k = 0; $k < (int) $r['stops_count']; $k++): ?><?= $k ? '<b></b>' : '' ?><i></i><?php endfor; ?></div></div>
          <div class="card-body">
            <div class="card-meta"><span><?= icon('clock') ?> <?= e($r['duration']) ?></span><span><?= icon('pin') ?> <?= (int) $r['stops_count'] ?> paradas</span></div>
            <h3><a href="<?= e($r['url']) ?>"><?= e($r['title']) ?></a></h3>
            <p><?= e($r['summary']) ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($news): $lead = array_shift($news); ?>
<section class="section bg-arena" aria-labelledby="news-title">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow" style="--c: var(--azul)">Noticias</span>
        <h2 class="title-xl" id="news-title">Lo último desde los territorios</h2>
      </div>
      <a class="link-arrow" href="<?= e(url('/noticias')) ?>">Todas las noticias <?= icon('arrow-right') ?></a>
    </div>
    <div class="news-layout">
      <a class="news-lead reveal" href="<?= e($lead['url']) ?>">
        <?= img($lead['cover'], 'large', '', '(max-width: 900px) 100vw, 60vw', true, $lead['title']) ?>
        <div class="inner">
          <span class="news-date" style="color: var(--amarillo)"><?= e(fecha($lead['published_at'])) ?><?= $lead['is_demo'] ? ' · Demostración' : '' ?></span>
          <h3><?= e($lead['title']) ?></h3>
          <p><?= e($lead['summary']) ?></p>
        </div>
      </a>
      <div class="news-list">
        <?php foreach ($news as $i => $n): ?>
          <article class="news-item reveal" style="--i: <?= $i + 1 ?>">
            <?= img($n['cover'], 'thumb', '', '130px', true, $n['title']) ?>
            <div><span class="news-date"><?= e(fecha($n['published_at'])) ?></span><h3><a href="<?= e($n['url']) ?>"><?= e($n['title']) ?></a></h3></div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($photos): ?>
<section class="section" aria-labelledby="photos-title">
  <div class="container container-wide">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow" style="--c: var(--amarillo)">Galería</span>
        <h2 class="title-xl" id="photos-title">Colombia en <span class="italic-accent">imágenes</span></h2>
      </div>
      <a class="btn btn-outline" href="<?= e(url('/galeria')) ?>"><?= icon('camera') ?> Abrir la galería</a>
    </div>
    <div class="photo-strip" data-gallery-group>
      <?php foreach ($photos as $p): $j = App\Models\Media::toJson($p); ?>
        <a href="<?= e(url('/galeria')) ?>" data-lightbox-item='<?= e(json_encode($j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'>
          <?= img($p, 'medium', '', '(max-width: 900px) 33vw, 20vw', true) ?>
          <span><?= e($p['title']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- F. Cierre visual -->
<section class="section closing" aria-labelledby="closing-title">
  <svg class="closing-infinity" viewBox="0 0 104 60" aria-hidden="true"><path d="M52 30C43 18 35 12 26 12a18 18 0 0 0 0 36c9 0 17-6 26-18zm0 0c9 12 17 18 26 18a18 18 0 0 0 0-36c-9 0-17 6-26 18z" fill="none" stroke="#fff" stroke-width="7" stroke-linecap="round"/></svg>
  <div class="container">
    <span class="eyebrow" style="--c: #fff">Sigue explorando</span>
    <h2 id="closing-title" class="reveal"><?= e(setting('closing_title')) ?></h2>
    <p class="lead reveal"><?= e(setting('closing_text')) ?></p>
    <div class="closing-links reveal">
      <?php foreach (Sections::ALL as $s): ?><a href="<?= e(url('/' . $s['path'])) ?>"><?= icon($s['icon']) ?> <?= e($s['label']) ?></a><?php endforeach; ?>
      <a href="<?= e(url('/galeria')) ?>"><?= icon('camera') ?> Galería</a>
    </div>
  </div>
</section>
