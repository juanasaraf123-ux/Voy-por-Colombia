<?php /** @var array $e */ $loc = $e['municipality_name'] ?: $e['department_name']; ?>
<article class="dest-card" style="--c: <?= e(App\Sections::ALL[$e['section']]['color']) ?>">
  <?= img($e['cover'], 'medium', '', '(max-width: 700px) 80vw, 360px', true, $e['title']) ?>
  <div class="dest-card-top">
    <div class="flex gap-2 wrap">
      <?php if ($e['is_hidden_gem']): ?><span class="badge badge-gem"><?= icon('sparkle') ?> Joya escondida</span><?php endif; ?>
      <?php if ($e['is_demo'] && $e['section'] === 'entrepreneurship'): ?><span class="badge badge-demo">Demo</span><?php endif; ?>
    </div>
    <button class="fav-btn" type="button" data-fav="<?= (int) $e['id'] ?>" aria-label="Guardar «<?= e($e['title']) ?>» en favoritos"><span class="off"><?= icon('heart') ?></span><span class="on"><?= icon('heart-fill') ?></span></button>
  </div>
  <a class="dest-card-body" href="<?= e($e['url']) ?>">
    <?php if ($loc): ?><span class="loc"><?= icon('pin') ?> <?= e($loc) ?></span><?php endif; ?>
    <h3><?= e($e['title']) ?></h3>
    <p><?= e(str_limit($e['summary'], 140)) ?></p>
  </a>
</article>
