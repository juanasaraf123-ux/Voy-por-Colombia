<?php
use App\Core\View;
use App\Models\Media;
use App\Sections;

View::share('transparentHeader', true);
$titleWords = preg_split('/\s+/', trim((string) setting('hero_title'))) ?: [];
$firstWord = array_shift($titleWords);
$meta = App\Models\Geo::mapMeta();
$heroVideoJson = $heroVideo ? json_encode(Media::toJson($heroVideo), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
?>
<!-- A + B. Portada con mapa interactivo -->
<section class="hero2" aria-label="Portada" data-hero>
  <div class="hero-slides" aria-hidden="true">
    <?php if ($heroVideo && $heroVideo['kind'] === 'video' && setting('hero_video_background') === '1'): ?>
      <div class="hero-slide is-active"><video src="<?= e(media_url($heroVideo, 'original')) ?>" autoplay muted loop playsinline preload="metadata"></video></div>
    <?php else: ?>
      <?php foreach ($heroMedia as $i => $m): ?>
        <div class="hero-slide <?= $i === 0 ? 'is-active' : '' ?>"><?= img($m, 'large', '', '100vw', $i > 0) ?></div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="hero2-grid">
    <div class="hero2-copy">
      <p class="hero2-kicker"><?= e(setting('hero_kicker')) ?></p>
      <h1 class="hero2-title">
        <span class="t1"><?= e($firstWord) ?><svg class="brush brush-a" viewBox="0 0 60 30" aria-hidden="true"><path d="M8 24 C 20 12, 34 6, 52 4" /><path d="M24 28 C 34 22, 44 20, 56 20"/></svg></span>
        <span class="t2"><?= e(implode(' ', $titleWords)) ?><svg class="heart-doodle" viewBox="0 0 40 36" aria-hidden="true"><path d="M20 32 C 6 22, 2 14, 6 8 C 10 2, 18 4, 20 11 C 22 4, 30 2, 34 8 C 38 14, 34 22, 20 32 Z"/></svg></span>
      </h1>
      <p class="hero2-sub"><?= e(setting('hero_subtitle')) ?></p>
      <div class="hero2-actions">
        <a class="btn-explore" href="<?= e(url('/turismo')) ?>"><span class="ico"><?= icon('compass') ?></span><?= e(setting('hero_cta')) ?><?= icon('chevron-right') ?></a>
        <?php if ($heroVideoJson): ?>
          <button class="btn-video" type="button" data-lightbox-item='<?= e($heroVideoJson) ?>'><span class="ico"><?= icon('play') ?></span><span>Ver video<small><?= e($heroVideo['title'] ?? '') ?></small></span></button>
        <?php endif; ?>
      </div>
    </div>

    <div class="hero2-map" data-hero-map
      data-regions="<?= e(json_encode($meta['colors'])) ?>"
      data-spotlight="<?= (int) ($spotlight['id'] ?? 0) ?>"
      data-explore="<?= e(url('/turismo')) ?>"
      role="application" aria-label="Mapa interactivo de Colombia con destinos destacados">
      <a class="islands-inset" href="<?= e(url('/turismo', ['departamento' => 'san-andres-y-providencia'])) ?>" data-islands>
        <span>San Andrés, Providencia y Santa Catalina</span>
        <svg viewBox="0 0 60 70" aria-hidden="true"><path d="M14 58 C 10 48, 14 36, 20 30 C 24 38, 22 50, 14 58 Z"/><path d="M40 22 C 38 16, 42 8, 46 6 C 48 12, 46 18, 40 22 Z"/></svg>
      </a>
      <div class="compass" aria-hidden="true"><span>N</span><?= icon('compass') ?></div>
      <noscript><p class="hero2-noscript">Activa JavaScript para usar el mapa interactivo.</p></noscript>
    </div>

    <div class="hero2-side">
      <div class="hero2-chips" role="group" aria-label="Filtrar el mapa">
        <button class="mchip is-active" type="button" data-hchip="" aria-pressed="true"><?= icon('grid') ?> Todos</button>
        <?php foreach ($heroChips as $c): ?>
          <button class="mchip" type="button" style="--c: <?= e($c['color']) ?>" data-hchip="<?= e($c['slug']) ?>" aria-pressed="false" title="<?= e($c['name']) ?>"><?= icon($c['icon'] ?: 'tag') ?> <span><?= e($c['name'] === 'Experiencias comunitarias' ? 'Comunitario' : $c['name']) ?></span></button>
        <?php endforeach; ?>
        <button class="mchip mchip-routes" type="button" style="--c: #ff3f8e" data-hroutes aria-pressed="false"><?= icon('route') ?> Rutas</button>
      </div>
      <div class="spot-wrap" data-spot aria-live="polite">
        <?php if ($spotlight): ?><?= View::partial('spot-card', ['c' => $spotlight]) ?><?php endif; ?>
      </div>
      <p class="hand-note"><?= e(setting('hero_note')) ?> <svg viewBox="0 0 30 26" aria-hidden="true"><path d="M15 23 C 5 16, 2 10, 5 6 C 8 2, 13 3, 15 8 C 17 3, 22 2, 25 6 C 28 10, 25 16, 15 23 Z"/></svg></p>
    </div>
  </div>
  <svg class="hero2-wave" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0 70 C 200 30, 380 20, 560 46 S 920 100, 1100 70 S 1340 30, 1440 52 V120 H0 Z"/></svg>
</section>

<!-- D. Destinos destacados -->
<?php if ($strip): ?>
<section class="featured-strip" aria-labelledby="strip-title">
  <div class="strip-inner">
    <div class="strip-label">
      <svg class="strip-arrow" viewBox="0 0 70 30" aria-hidden="true"><path d="M4 26 C 20 6, 44 2, 62 10"/><path d="M54 4 L 63 10 L 54 17"/></svg>
      <h2 id="strip-title">Destinos destacados</h2>
      <div class="strip-pager"><span class="dots" data-strip-dots></span><button type="button" data-strip-next aria-label="Ver más destinos"><?= icon('arrow-right') ?></button></div>
      <small data-strip-count>1 / <?= count($strip) ?></small>
    </div>
    <div class="strip-rail" data-strip>
      <?php foreach ($strip as $c): ?>
        <article class="strip-card">
          <?= img($c['_cover'], 'medium', '', '260px', true, $c['shortName']) ?>
          <button class="fav-btn" type="button" data-fav="<?= (int) $c['id'] ?>" aria-label="Guardar «<?= e($c['shortName']) ?>» en favoritos"><span class="off"><?= icon('heart') ?></span><span class="on"><?= icon('heart-fill') ?></span></button>
          <a href="<?= e($c['url']) ?>"><strong><span class="ic"><?= icon($c['icon']) ?></span><?= e($c['shortName']) ?></strong><small><?= icon('pin') ?> <?= e($c['department'] ?? '') ?></small></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="benefits" aria-label="Lo que puedes hacer">
  <div class="benefits-inner">
    <a href="<?= e(url('/cultura')) ?>"><?= icon('palm') ?><span>Más que un destino, <b>una experiencia</b></span></a>
    <a href="<?= e(url('/turismo')) ?>"><?= icon('compass') ?><span>Explora por <b>departamentos</b></span></a>
    <a href="<?= e(url('/mi-ruta')) ?>"><?= icon('star') ?><span>Crea tu <b>ruta personalizada</b></span></a>
    <a href="<?= e(url('/emprendimiento')) ?>"><?= icon('users') ?><span>Conoce a las <b>comunidades locales</b></span></a>
    <a href="<?= e(url('/noticias')) ?>"><?= icon('leaf2') ?><span>Viaja responsable, <b>viaja Colombia</b></span></a>
    <div class="es-vida" aria-hidden="true">
      <svg viewBox="0 0 200 110"><path d="M8 52 C 50 30, 110 24, 150 22 L 146 44 C 104 46, 56 54, 12 72 Z" fill="#ffc531"/><path d="M14 74 C 60 56, 108 50, 148 48 L 144 64 C 104 66, 62 74, 18 92 Z" fill="#1d4fd6"/><path d="M20 94 C 64 78, 110 72, 142 70 L 138 84 C 104 86, 66 92, 26 106 Z" fill="#e5233b"/></svg>
      <span><?= e(setting('logo_title')) ?><br>es vida</span>
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
