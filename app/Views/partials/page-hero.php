<?php
/** Encabezado de sección. Variables: $info (SectionInfo), $title, $titleAccent, $images (media[]), $sticker */
$def = $info['def'];
$cover = $info['cover'] ?? null;
?>
<section class="page-hero" style="--c: <?= e($def['color']) ?>">
  <div class="container page-hero-grid">
    <div class="reveal">
      <span class="eyebrow"><?= icon($def['icon']) ?> <?= e($info['title']) ?></span>
      <h1><?= e($title ?? $info['title']) ?><?php if (!empty($titleAccent)): ?> <em><?= e($titleAccent) ?></em><?php endif; ?></h1>
      <p class="lead"><?= e($info['description'] ?: $info['tagline']) ?></p>
      <?= $heroExtra ?? '' ?>
    </div>
    <div class="hero-collage reveal-scale">
      <figure><?= img($images[0] ?? $cover, 'large', '', '(max-width: 900px) 100vw, 50vw', false, $info['title']) ?></figure>
      <?php if (!empty($images[1])): ?><figure><?= img($images[1], 'medium', '', '300px', false) ?></figure><?php endif; ?>
      <?php if (!empty($sticker)): ?><div class="sticker"><span><b><?= e($sticker[0]) ?></b><?= e($sticker[1]) ?></span></div><?php endif; ?>
    </div>
  </div>
  <?= App\Core\View::partial('wave') ?>
</section>
