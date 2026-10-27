<?php
/** Tarjeta del lugar seleccionado en el mapa de la portada. $c = Entry::toCard(). map.js genera el mismo marcado. */
$loc = $c['municipality'] && $c['municipality'] !== $c['department'] ? $c['municipality'] . ', ' . $c['department'] : ($c['department'] ?? '');
$m = $c['media'];
?>
<article class="spot-card" style="--c: <?= e($c['catColor']) ?>">
  <div class="spot-media">
    <?php if ($c['large']): ?><img src="<?= e($c['large']) ?>" alt="<?= e($c['shortName']) ?>"><?php endif; ?>
    <span class="spot-chip"><?= icon($c['icon']) ?> <?= e($c['catName']) ?></span>
    <button class="spot-fav fav-btn" type="button" data-fav="<?= (int) $c['id'] ?>" aria-label="Guardar en favoritos"><span class="off"><?= icon('heart') ?></span><span class="on"><?= icon('heart-fill') ?></span></button>
    <?php if ($m['video']): ?><button class="spot-play" type="button" data-media-open="video" data-entry="<?= (int) $c['id'] ?>" aria-label="Ver video de <?= e($c['shortName']) ?>"><?= icon('play') ?></button><?php endif; ?>
    <div class="spot-title"><h3><?= e($c['shortName']) ?></h3><?php if ($loc): ?><span><?= icon('pin') ?> <?= e($loc) ?></span><?php endif; ?></div>
  </div>
  <div class="spot-body">
    <?php if ($c['tagline']): ?><h4><?= e($c['tagline']) ?></h4><?php endif; ?>
    <p><?= e($c['summary']) ?></p>
    <div class="spot-actions">
      <button type="button" data-media-open="image" data-entry="<?= (int) $c['id'] ?>"><span><?= icon('camera') ?></span>Galería</button>
      <button type="button" data-media-open="video" data-entry="<?= (int) $c['id'] ?>" class="<?= $m['video'] ? '' : 'is-off' ?>" <?= $m['video'] ? '' : 'disabled' ?>><span><?= icon('play') ?></span>Video</button>
      <button type="button" data-media-open="audio" data-entry="<?= (int) $c['id'] ?>" class="<?= $m['audio'] ? '' : 'is-off' ?>" <?= $m['audio'] ? '' : 'disabled' ?>><span><?= icon('volume') ?></span>Audio</button>
      <a href="<?= e($c['lat'] !== null ? 'https://www.openstreetmap.org/directions?to=' . $c['lat'] . ',' . $c['lng'] : $c['url']) ?>" target="_blank" rel="noopener"><span><?= icon('route') ?></span>Cómo llegar</a>
    </div>
    <a class="btn-yellow" href="<?= e($c['url']) ?>">Ver más <?= icon('chevron-right') ?></a>
  </div>
</article>
