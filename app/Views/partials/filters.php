<?php
/** Barra de filtros común. Variables: $filters, $categories, $departments, $municipalities, $regions, $placeholder */
$action = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
?>
<form class="filters" method="get" action="<?= e($action) ?>" data-filters>
  <div class="filters-row">
    <div class="field grow">
      <?= icon('search') ?>
      <label class="sr-only" for="f-q">Buscar</label>
      <input class="input" style="width:100%" id="f-q" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e($placeholder ?? 'Buscar…') ?>">
    </div>
    <label class="sr-only" for="f-region">Región</label>
    <select class="select" id="f-region" name="region" data-autosubmit>
      <option value="">Todas las regiones</option>
      <?php foreach ($regions as $r): ?><option value="<?= e($r['slug']) ?>" <?= $filters['region'] === $r['slug'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
    </select>
    <label class="sr-only" for="f-dep">Departamento</label>
    <select class="select" id="f-dep" name="departamento" data-autosubmit data-reset="municipio">
      <option value="">Todos los departamentos</option>
      <?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>" <?= (int) $filters['department_id'] === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?>
    </select>
    <?php if ($municipalities): ?>
      <label class="sr-only" for="f-mun">Municipio</label>
      <select class="select" id="f-mun" name="municipio" data-autosubmit>
        <option value="">Todos los municipios</option>
        <?php foreach ($municipalities as $m): ?><option value="<?= (int) $m['id'] ?>" <?= (int) $filters['municipality_id'] === (int) $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option><?php endforeach; ?>
      </select>
    <?php endif; ?>
    <button class="btn" type="submit"><?= icon('filter') ?> Filtrar</button>
  </div>
  <?php if ($categories): ?>
    <div class="chips chips-scroll" role="group" aria-label="Categorías">
      <button class="chip <?= $filters['category'] === '' ? 'is-active' : '' ?>" type="submit" name="categoria" value="">Todo</button>
      <?php foreach ($categories as $c): ?>
        <button class="chip <?= $filters['category'] === $c['slug'] ? 'is-active' : '' ?>" style="--chip: <?= e($c['color']) ?>" type="submit" name="categoria" value="<?= e($c['slug']) ?>">
          <?= icon($c['icon'] ?: 'tag') ?> <?= e($c['name']) ?> <span class="chip-count"><?= (int) $c['total'] ?></span>
        </button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</form>
