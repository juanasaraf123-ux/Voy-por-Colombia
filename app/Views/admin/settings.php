<?php $f = static fn ($k, $label, $type = 'text', $ph = '') => '<div class="field"><label class="field-label" for="' . $k . '">' . e($label) . '</label><input class="input' . (has_error($k) ? ' is-invalid' : '') . '" id="' . $k . '" name="' . $k . '" type="' . $type . '" placeholder="' . e($ph) . '" value="' . e(val($k, setting($k))) . '">' . field_error($k) . '</div>'; ?>
<form class="card" method="post" action="<?= e(url('/admin/configuracion')) ?>" data-unsaved>
  <?= csrf_field() ?>
  <h2><?= icon('settings') ?> Identidad del sitio</h2>
  <div class="row"><?= $f('site_name', 'Nombre del sitio') ?><?= $f('site_tagline', 'Lema') ?></div>
  <?= $f('footer_text', 'Texto del pie de página') ?>
  <?= $f('contact_email', 'Correo de contacto público', 'email') ?>
  <h2 class="mt"><?= icon('share') ?> Redes sociales</h2>
  <div class="row">
    <?= $f('social_instagram', 'Instagram', 'url', 'https://instagram.com/…') ?><?= $f('social_facebook', 'Facebook', 'url', 'https://facebook.com/…') ?>
    <?= $f('social_youtube', 'YouTube', 'url', 'https://youtube.com/@…') ?><?= $f('social_tiktok', 'TikTok', 'url', 'https://tiktok.com/@…') ?>
    <?= $f('social_x', 'X (Twitter)', 'url', 'https://x.com/…') ?>
  </div>
  <button class="btn btn-primary" type="submit"><?= icon('check') ?> Guardar configuración</button>
</form>
<section class="card" style="border:1px solid #f3c6ca">
  <h2><?= icon('trash') ?> Datos de demostración</h2>
  <p>Actualmente hay <strong><?= (int) $demo['entries'] ?></strong> publicaciones, <strong><?= (int) $demo['routes'] ?></strong> rutas y <strong><?= (int) $demo['media'] ?></strong> archivos marcados como demostración. Al eliminarlos, el contenido real que hayas creado no se modifica (las publicaciones demo que editaste desmarcando «Mantener como demostración» también se conservan).</p>
  <form class="flex" method="post" action="<?= e(url('/admin/configuracion/eliminar-demo')) ?>" data-confirm="Se eliminará todo el contenido de demostración. ¿Continuar?">
    <?= csrf_field() ?>
    <input class="input" style="max-width:240px" name="confirm" placeholder="Escribe ELIMINAR" aria-label="Escribe ELIMINAR para confirmar" autocomplete="off">
    <button class="btn btn-danger" type="submit" <?= array_sum($demo) ? '' : 'disabled' ?>><?= icon('trash') ?> Eliminar demostración</button>
  </form>
</section>
