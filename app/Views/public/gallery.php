<?php
use App\Core\View;
use App\Models\Media;
use App\Sections;
$q = static fn (array $over) => '?' . http_build_query(array_filter(array_merge(['tipo' => $filters['tipo'], 'categoria' => $filters['categoria'], 'coleccion' => $filters['collection_id'], 'departamento' => $filters['department_id'], 'municipio' => $filters['municipality_id']], $over), static fn ($v) => $v !== null && $v !== ''));
?>
<section class="page-hero" style="--c: <?= e($info['def']['color']) ?>; padding-bottom: 90px">
  <div class="container page-hero-grid">
    <div>
      <span class="eyebrow"><?= icon('camera') ?> <?= e($info['title']) ?></span>
      <h1>Colombia en <em>imágenes</em></h1>
      <p class="lead"><?= e($info['description']) ?></p>
    </div>
    <div class="hero-collage reveal-scale">
      <figure><?= img($items[0] ?? $info['cover'], 'large', '', '50vw', false) ?></figure>
      <?php if (!empty($items[1])): ?><figure><?= img($items[1], 'medium', '', '300px', false) ?></figure><?php endif; ?>
      <div class="sticker"><span><b><?= (int) $total ?></b>imágenes</span></div>
    </div>
  </div>
  <?= View::partial('wave') ?>
</section>

<section class="section" style="--c: <?= e($info['def']['color']) ?>; padding-top: 0">
  <div class="container container-wide">
    <form class="filters" method="get" action="<?= e(url('/galeria')) ?>">
      <div class="chips chips-scroll" role="group" aria-label="Categoría">
        <a class="chip <?= !$filters['categoria'] ? 'is-active' : '' ?>" href="<?= e($q(['categoria' => null])) ?>">Todo</a>
        <?php foreach (Sections::ALL as $k => $s): if ($k === 'news') { continue; } ?>
          <a class="chip <?= $filters['categoria'] === $k ? 'is-active' : '' ?>" style="--chip: <?= e($s['color']) ?>" href="<?= e($q(['categoria' => $k])) ?>"><?= icon($s['icon']) ?> <?= e($s['label']) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="filters-row">
        <input type="hidden" name="categoria" value="<?= e($filters['categoria']) ?>">
        <select class="select" name="tipo" data-autosubmit aria-label="Tipo de contenido">
          <option value="">Fotos y videos</option>
          <option value="foto" <?= $filters['tipo'] === 'foto' ? 'selected' : '' ?>>Solo fotos</option>
          <option value="video" <?= $filters['tipo'] === 'video' ? 'selected' : '' ?>>Solo videos</option>
        </select>
        <select class="select" name="coleccion" data-autosubmit aria-label="Colección">
          <option value="">Todas las colecciones</option>
          <?php foreach ($collections as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $filters['collection_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
        <select class="select" name="departamento" data-autosubmit data-reset="municipio" aria-label="Departamento">
          <option value="">Todos los departamentos</option>
          <?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>" <?= (int) $filters['department_id'] === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?>
        </select>
        <?php if ($municipalities): ?>
          <select class="select" name="municipio" data-autosubmit aria-label="Municipio">
            <option value="">Todos los municipios</option>
            <?php foreach ($municipalities as $m): ?><option value="<?= (int) $m['id'] ?>" <?= (int) $filters['municipality_id'] === (int) $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option><?php endforeach; ?>
          </select>
        <?php endif; ?>
        <noscript><button class="btn" type="submit">Filtrar</button></noscript>
        <span class="muted" style="margin-left:auto;font-weight:600"><?= (int) $total ?> elementos</span>
      </div>
    </form>

    <?php if ($items): ?>
      <div class="masonry" data-gallery-group data-gallery-grid data-total="<?= (int) $total ?>" data-endpoint="<?= e(url('/api/galeria') . $q([])) ?>">
        <?php foreach ($items as $m): $j = Media::toJson($m); ?>
          <button class="masonry-item" type="button" data-lightbox-item='<?= e(json_encode($j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>' aria-label="Ampliar: <?= e($m['title'] ?: 'imagen') ?>">
            <img src="<?= e($j['medium']) ?>" alt="<?= e($j['alt'] ?? '') ?>" loading="lazy" <?= $m['width'] ? 'width="' . (int) $m['width'] . '" height="' . (int) $m['height'] . '"' : '' ?>>
            <?php if ($m['kind'] !== 'image'): ?><span class="play"><?= icon('play') ?></span><?php endif; ?>
            <span class="cap"><strong><?= e($m['title']) ?></strong><small><?= e(trim(($m['municipality_name'] ?? $m['department_name'] ?? '') . ($m['credit'] ? ' · ' . $m['credit'] : ''), ' ·')) ?></small></span>
          </button>
        <?php endforeach; ?>
      </div>
      <?php if ($total > count($items)): ?><div class="load-more"><button class="btn btn-outline" type="button" data-gallery-more><?= icon('plus') ?> Cargar más</button></div><?php endif; ?>
    <?php else: ?>
      <?= View::partial('empty', ['icon' => 'camera', 'title' => 'No hay imágenes con esos filtros']) ?>
    <?php endif; ?>
  </div>
</section>
