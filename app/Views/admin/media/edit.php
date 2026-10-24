<?php
use App\Models\Media;
use App\Sections;
$cls = static fn (string $n) => has_error($n) ? ' is-invalid' : '';
?>
<p><a href="<?= e(url('/admin/multimedia')) ?>">← Volver al centro multimedia</a></p>
<div class="grid-form">
  <form class="card" method="post" action="<?= e(url('/admin/multimedia/' . $m['id'])) ?>" data-unsaved>
    <?= csrf_field() ?>
    <h2><?= icon('edit') ?> Datos del archivo</h2>
    <div class="row">
      <div class="field"><label class="field-label" for="title">Título</label><input class="input" id="title" name="title" maxlength="190" value="<?= e(val('title', $m['title'])) ?>"></div>
      <div class="field"><label class="field-label" for="credit">Crédito / autor</label><input class="input" id="credit" name="credit" maxlength="190" value="<?= e(val('credit', $m['credit'])) ?>" placeholder="Foto: Nombre Apellido"></div>
      <?php if ($m['kind'] === 'image'): ?>
      <div class="field" style="grid-column:1/-1"><label class="field-label" for="alt_text">Texto alternativo</label><input class="input<?= $cls('alt_text') ?>" id="alt_text" name="alt_text" maxlength="255" value="<?= e(val('alt_text', $m['alt_text'])) ?>" placeholder="Describe lo que se ve, para personas que usan lectores de pantalla"><?= field_error('alt_text') ?></div>
      <?php endif; ?>
      <div class="field" style="grid-column:1/-1"><label class="field-label" for="caption">Descripción breve</label><textarea class="textarea" id="caption" name="caption" maxlength="500" rows="2" data-counter="500"><?= e(val('caption', $m['caption'])) ?></textarea></div>
      <div class="field"><label class="field-label" for="license">Licencia o permiso de uso</label><input class="input" id="license" name="license" maxlength="120" value="<?= e(val('license', $m['license'])) ?>" placeholder="Ej. CC BY 4.0, uso autorizado por el autor"></div>
      <div class="field"><label class="field-label" for="source_url">Fuente (enlace)</label><input class="input<?= $cls('source_url') ?>" id="source_url" name="source_url" type="url" value="<?= e(val('source_url', $m['source_url'])) ?>"><?= field_error('source_url') ?></div>
      <div class="field"><label class="field-label" for="department_id">Departamento</label>
        <select class="select" id="department_id" name="department_id" data-dep-select data-target="#municipality_id"><option value="">—</option><?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>" <?= (int) val('department_id', $m['department_id']) === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label class="field-label" for="municipality_id">Municipio</label>
        <select class="select<?= $cls('municipality_id') ?>" id="municipality_id" name="municipality_id" data-mun-select data-selected="<?= (int) val('municipality_id', $m['municipality_id']) ?>"><option value="">—</option><?php foreach ($municipalities as $mu): ?><option value="<?= (int) $mu['id'] ?>" <?= (int) val('municipality_id', $m['municipality_id']) === (int) $mu['id'] ? 'selected' : '' ?>><?= e($mu['name']) ?></option><?php endforeach; ?></select><?= field_error('municipality_id') ?></div>
      <div class="field"><label class="field-label" for="collection_id">Colección de la galería</label>
        <select class="select" id="collection_id" name="collection_id"><option value="">—</option><?php foreach ($collections as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) val('collection_id', $m['collection_id']) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label class="field-label" for="gallery_section">Categoría en la galería</label>
        <select class="select" id="gallery_section" name="gallery_section"><option value="">—</option><?php foreach (Sections::ALL as $k => $s): ?><option value="<?= e($k) ?>" <?= val('gallery_section', $m['gallery_section']) === $k ? 'selected' : '' ?>><?= e($s['label']) ?></option><?php endforeach; ?></select></div>
    </div>
    <label class="check"><input type="checkbox" name="in_gallery" value="1" <?= val('in_gallery', $m['in_gallery']) ? 'checked' : '' ?>> Apto para aparecer en la Galería de fotos</label>
    <label class="check"><input type="checkbox" name="gallery_featured" value="1" <?= val('gallery_featured', $m['gallery_featured']) ? 'checked' : '' ?>> Destacar en la galería y en la portada</label>
    <?php if ($m['is_demo']): ?><label class="check"><input type="checkbox" name="keep_demo" value="1" checked> Mantener marcado como demostración</label><?php endif; ?>
    <button class="btn btn-primary mt" type="submit"><?= icon('check') ?> Guardar</button>
  </form>
  <aside>
    <section class="card">
      <div class="preview-large">
        <?php if ($m['kind'] === 'image'): ?><img src="<?= e(media_url($m, 'large')) ?>" alt="<?= e($m['alt_text'] ?? '') ?>">
        <?php elseif ($m['kind'] === 'video'): ?><video src="<?= e(media_url($m, 'original')) ?>" controls preload="metadata"></video>
        <?php elseif ($m['kind'] === 'audio'): ?><div style="padding:20px;background:#fff"><audio src="<?= e(media_url($m, 'original')) ?>" controls style="width:100%"></audio></div>
        <?php elseif ($m['kind'] === 'embed'): ?><iframe src="<?= e(Media::embedUrl($m)) ?>" title="Vista previa" allowfullscreen></iframe>
        <?php else: ?><div style="padding:30px;background:#fff;text-align:center"><a class="btn" href="<?= e(media_url($m, 'original')) ?>" target="_blank"><?= icon('file') ?> Abrir PDF</a></div><?php endif; ?>
      </div>
      <dl class="hint" style="margin:12px 0 0;display:grid;grid-template-columns:auto 1fr;gap:4px 10px">
        <dt>Tipo</dt><dd style="margin:0"><?= e(Media::KINDS[$m['kind']]) ?> <?= e($m['mime'] ?? '') ?></dd>
        <?php if ($m['size_bytes']): ?><dt>Peso</dt><dd style="margin:0"><?= human_bytes((int) $m['size_bytes']) ?></dd><?php endif; ?>
        <?php if ($m['width']): ?><dt>Tamaño</dt><dd style="margin:0"><?= (int) $m['width'] ?> × <?= (int) $m['height'] ?> px</dd><?php endif; ?>
        <?php if ($m['original_name']): ?><dt>Archivo</dt><dd style="margin:0;word-break:break-all"><?= e($m['original_name']) ?></dd><?php endif; ?>
        <dt>Subido</dt><dd style="margin:0"><?= e(fecha_hora($m['created_at'])) ?></dd>
      </dl>
      <?php if ($m['kind'] !== 'embed'): ?><p class="hint">URL: <code style="word-break:break-all"><?= e(media_url($m, 'original')) ?></code></p><?php endif; ?>
    </section>
    <section class="card">
      <h2><?= icon('link') ?> Dónde se usa</h2>
      <?php if ($usedIn): ?><ul style="margin:0;padding-left:18px"><?php foreach ($usedIn as $u): ?><li><a href="<?= e(url('/admin/publicaciones/' . $u['id'] . '/editar')) ?>"><?= e($u['title']) ?></a></li><?php endforeach; ?></ul>
      <?php else: ?><p class="hint">No está asociado a publicaciones.</p><?php endif; ?>
      <?php if ($usage > count($usedIn)): ?><p class="hint">También se usa como portada de secciones, rutas o departamentos.</p><?php endif; ?>
    </section>
    <section class="card">
      <h2 style="color:#a31d2b"><?= icon('trash') ?> Eliminar</h2>
      <form method="post" action="<?= e(url('/admin/multimedia/' . $m['id'] . '/eliminar')) ?>" data-confirm="<?= $usage ? 'Este archivo se usa en ' . (int) $usage . ' lugar(es) y se quitará de ellos. ¿Eliminarlo definitivamente?' : '¿Eliminar definitivamente este archivo?' ?>">
        <?= csrf_field() ?>
        <button class="btn btn-danger" type="submit"><?= icon('trash') ?> Eliminar archivo</button>
      </form>
    </section>
  </aside>
</div>
