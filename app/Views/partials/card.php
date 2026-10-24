<?php
/** Tarjeta estándar de publicación. @var array $e */
$def = App\Sections::ALL[$e['section']];
$loc = trim(($e['municipality_name'] ? $e['municipality_name'] . ', ' : '') . ($e['department_name'] ?? ''), ', ');
?>
<article class="card <?= !empty($reveal) ? 'reveal' : '' ?>" style="--c: <?= e($def['color']) ?>; --i: <?= (int) ($i ?? 0) ?>">
  <div class="card-media">
    <?= img($e['cover'], 'medium', '', '(max-width: 620px) 100vw, (max-width: 1000px) 50vw, 400px', true, $e['title']) ?>
    <div class="badges">
      <?php if (!empty($showSection)): ?><span class="badge badge-section"><?= icon($def['icon']) ?> <?= e($def['label']) ?></span><?php endif; ?>
      <?php if ($e['is_hidden_gem']): ?><span class="badge badge-gem"><?= icon('sparkle') ?> Joya</span><?php endif; ?>
      <?php if ($e['is_demo'] && in_array($e['section'], ['entrepreneurship', 'news'], true)): ?><span class="badge badge-demo">Demostración</span><?php endif; ?>
    </div>
    <?php if ($e['section'] !== 'news'): ?>
      <button class="fav-btn" type="button" data-fav="<?= (int) $e['id'] ?>" aria-label="Guardar «<?= e($e['title']) ?>» en favoritos"><span class="off"><?= icon('heart') ?></span><span class="on"><?= icon('heart-fill') ?></span></button>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <div class="card-meta">
      <?php if ($e['section'] === 'news'): ?>
        <span><?= icon('calendar') ?> <?= e(fecha($e['published_at'])) ?></span>
      <?php elseif ($e['event_start']): ?>
        <span><?= icon('calendar') ?> <?= e(fecha($e['event_start'], false)) ?></span>
      <?php endif; ?>
      <?php if ($loc): ?><span><?= icon('pin') ?> <?= e($loc) ?></span><?php endif; ?>
    </div>
    <h3><a href="<?= e($e['url']) ?>"><?= e($e['title']) ?></a></h3>
    <p><?= e(str_limit($e['summary'], 130)) ?></p>
    <?php if ($e['categories']): ?>
      <div class="card-cats">
        <?php foreach (array_slice($e['categories'], 0, 3) as $c): ?><span class="mini-tag" style="--t: <?= e($c['color'] ?: $def['color']) ?>"><?= e($c['name']) ?></span><?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</article>
