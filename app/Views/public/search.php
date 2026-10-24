<?php
use App\Core\View;
use App\Sections;
?>
<section class="page-hero" style="--c: var(--naranja); padding-bottom: 90px">
  <div class="container">
    <span class="eyebrow"><?= icon('search') ?> Buscador</span>
    <h1><?= $q !== '' ? 'Resultados para <em>«' . e($q) . '»</em>' : 'Encuentra <em>tu próximo viaje</em>' ?></h1>
    <form class="filters" style="position:static;margin:24px 0 0;max-width:720px" action="<?= e(url('/buscar')) ?>" method="get" role="search">
      <div class="filters-row">
        <div class="field grow"><?= icon('search') ?><label class="sr-only" for="sq">Buscar</label><input class="input" style="width:100%" id="sq" type="search" name="q" value="<?= e($q) ?>" placeholder="Destinos, municipios, platos, festivales, noticias…" autofocus></div>
        <button class="btn btn-primary" type="submit">Buscar</button>
      </div>
    </form>
  </div>
  <?= View::partial('wave') ?>
</section>
<section class="section" style="padding-top: 20px">
  <div class="container">
    <?php if ($q !== '' && mb_strlen($q) < 2): ?>
      <?= View::partial('empty', ['icon' => 'search', 'title' => 'Escribe al menos dos letras']) ?>
    <?php elseif ($q !== '' && !$total): ?>
      <?= View::partial('empty', ['icon' => 'search', 'title' => 'Sin resultados', 'text' => 'Prueba con otra palabra, el nombre de un departamento o municipio.']) ?>
    <?php endif; ?>
    <?php if ($places): ?>
      <div class="section-block">
        <div class="section-block-head" style="--c: var(--verde)"><span class="ico"><?= icon('map') ?></span><h2 class="title-md" style="margin:0">Departamentos y municipios</h2></div>
        <div class="chips">
          <?php foreach ($places as $p): ?>
            <?php if ($p['type'] === 'department'): ?>
              <a class="chip" href="<?= e(url('/departamentos/' . $p['slug'])) ?>"><?= icon('map') ?> <?= e($p['name']) ?></a>
            <?php else: ?>
              <a class="chip" href="<?= e(url('/turismo', ['departamento' => $p['department_slug'], 'municipio' => $p['slug']])) ?>"><?= icon('pin') ?> <?= e($p['name']) ?> <span class="chip-count"><?= e($p['department_name']) ?></span></a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <?php foreach ($groups as $key => $rows): $def = Sections::ALL[$key]; ?>
      <div class="section-block" style="--c: <?= e($def['color']) ?>">
        <div class="section-block-head"><span class="ico"><?= icon($def['icon']) ?></span><h2 class="title-md" style="margin:0"><?= e($def['label']) ?> <span class="muted" style="font-size:.7em">(<?= count($rows) ?>)</span></h2></div>
        <div class="grid grid-4"><?php foreach ($rows as $i => $e): ?><?= View::partial('card', ['e' => $e, 'i' => $i]) ?><?php endforeach; ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
