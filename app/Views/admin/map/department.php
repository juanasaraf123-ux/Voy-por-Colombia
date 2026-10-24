<?php use App\Core\View; ?>
<p><a href="<?= e(url('/admin/mapa')) ?>">← Volver a gestión del mapa</a></p>
<form class="grid-form" method="post" action="<?= e(url('/admin/mapa/departamentos/' . $dep['id'])) ?>" data-unsaved>
  <?= csrf_field() ?>
  <section class="card">
    <h2><?= icon('map') ?> <?= e($dep['name']) ?></h2>
    <div class="row">
      <div class="field"><label class="field-label" for="region_id">Región natural</label>
        <select class="select" id="region_id" name="region_id"><option value="">—</option><?php foreach ($regions as $r): ?><option value="<?= (int) $r['id'] ?>" <?= (int) val('region_id', $dep['region_id']) === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select><?= field_error('region_id') ?></div>
      <div class="field"><label class="field-label" for="capital">Capital</label><input class="input" id="capital" name="capital" maxlength="100" value="<?= e(val('capital', $dep['capital'])) ?>"></div>
    </div>
    <div class="field"><label class="field-label" for="summary">Descripción breve</label><textarea class="textarea" id="summary" name="summary" maxlength="500" data-counter="500"><?= e(val('summary', $dep['summary'])) ?></textarea><?= field_error('summary') ?></div>
    <p class="hint">Código ISO: <?= e($dep['iso_code'] ?? '—') ?> · <?= count($municipalities) ?> municipios. <a href="<?= e(url('/departamentos/' . $dep['slug'])) ?>" target="_blank">Ver página pública</a></p>
    <button class="btn btn-primary" type="submit"><?= icon('check') ?> Guardar</button>
  </section>
  <aside><section class="card"><?= View::partial('admin-cover', ['name' => 'cover_media_id', 'label' => 'Imagen del departamento', 'media' => $cover]) ?></section></aside>
</form>
