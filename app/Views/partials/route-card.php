<article class="route-card reveal" style="--i: <?= (int) ($i ?? 0) ?>">
  <div class="card-media"><?= img($r['cover'], 'medium', '', '400px', true, $r['title']) ?><div class="route-line"><?php for ($k = 0; $k < (int) $r['stops_count']; $k++): ?><?= $k ? '<b></b>' : '' ?><i></i><?php endfor; ?></div></div>
  <div class="card-body">
    <div class="card-meta"><?php if ($r['duration']): ?><span><?= icon('clock') ?> <?= e($r['duration']) ?></span><?php endif; ?><span><?= icon('pin') ?> <?= (int) $r['stops_count'] ?> paradas</span></div>
    <h3><a href="<?= e($r['url']) ?>"><?= e($r['title']) ?></a></h3>
    <p><?= e($r['summary']) ?></p>
  </div>
</article>
