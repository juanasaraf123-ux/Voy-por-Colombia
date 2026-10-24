<?php
/** Campo de imagen única con selector de la biblioteca. Variables: $name, $label, $media, $kind (image|video), $help */
$kind = $kind ?? 'image';
$id = 'mf-' . preg_replace('/[^a-z0-9]/i', '', $name);
$value = val($name, $media['id'] ?? '');
?>
<div class="field media-field" data-media-single data-kind="<?= e($kind) ?>">
  <span class="field-label"><?= e($label) ?></span>
  <input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>" data-media-input>
  <div class="cover-box" data-media-preview>
    <?php if ($media && $media['kind'] === 'image'): ?><img src="<?= e(media_url($media, 'medium')) ?>" alt="">
    <?php elseif ($media): ?><span class="empty-label"><?= icon('video') ?> <?= e($media['title'] ?: $media['original_name']) ?></span>
    <?php else: ?><span class="empty-label"><?= icon($kind === 'video' ? 'video' : 'image') ?> Sin <?= $kind === 'video' ? 'video' : 'imagen' ?></span><?php endif; ?>
  </div>
  <div class="flex">
    <button class="btn btn-outline btn-sm" type="button" data-media-choose><?= icon('image') ?> Elegir de la biblioteca</button>
    <button class="btn btn-outline btn-sm" type="button" data-media-clear <?= $value ? '' : 'hidden' ?>><?= icon('close') ?> Quitar</button>
  </div>
  <?php if (!empty($help)): ?><p class="field-help"><?= e($help) ?></p><?php endif; ?>
  <?= field_error($name) ?>
</div>
