<?php
/** Campo de varios archivos ordenables. Variables: $name, $label, $items, $kinds, $help */
$kinds = $kinds ?? 'image,video,embed,audio,document';
?>
<div class="field media-field" data-media-multi data-name="<?= e($name) ?>" data-kind="<?= e($kinds) ?>">
  <div class="flex between"><span class="field-label"><?= e($label) ?></span>
    <button class="btn btn-outline btn-sm" type="button" data-media-choose><?= icon('plus') ?> Agregar archivos</button></div>
  <div class="thumbs" data-thumbs>
    <?php foreach ($items as $m): ?>
      <div class="thumb" draggable="true" data-id="<?= (int) $m['id'] ?>">
        <img src="<?= e(App\Models\Media::thumbUrl($m)) ?>" alt="<?= e($m['title'] ?? '') ?>">
        <?php if ($m['kind'] !== 'image'): ?><span class="kind"><?= e(App\Models\Media::KINDS[$m['kind']]) ?></span><?php endif; ?>
        <input type="hidden" name="<?= e($name) ?>[]" value="<?= (int) $m['id'] ?>">
        <button type="button" data-remove aria-label="Quitar"><?= icon('close') ?></button>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if (!empty($help)): ?><p class="field-help"><?= e($help) ?></p><?php endif; ?>
  <?= field_error($name) ?>
</div>
