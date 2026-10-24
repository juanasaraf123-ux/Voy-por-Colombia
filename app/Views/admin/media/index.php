<?php
use App\Core\MediaProcessor;
use App\Models\Media;
$q = $_GET;
$tabUrl = static function (?string $t) use ($q) { $q['tipo'] = $t; unset($q['pagina']); return '?' . http_build_query(array_filter($q)); };
?>
<div class="grid grid-2" style="margin-bottom:18px">
  <section class="card">
    <h2><?= icon('upload') ?> Subir archivos</h2>
    <form method="post" action="<?= e(url('/admin/multimedia/subir')) ?>" enctype="multipart/form-data" data-upload-form>
      <?= csrf_field() ?>
      <label class="dropzone" data-dropzone>
        <?= icon('upload') ?>
        <strong>Arrastra aquí fotos, videos, audios o PDF</strong>
        <span class="hint" style="display:block">o haz clic para elegirlos desde tu computador</span>
        <input class="sr-only" type="file" name="files[]" multiple accept="<?= e(MediaProcessor::acceptAttribute()) ?>" data-file-input>
        <span class="hint" data-file-names style="display:block;margin-top:8px"></span>
      </label>
      <div class="row mt">
        <div class="field" style="margin:0"><label class="field-label" for="up-credit">Crédito o autor (opcional)</label><input class="input" id="up-credit" name="credit" maxlength="190" placeholder="Ej. Foto: Nombre Apellido"></div>
        <label class="check" style="align-self:end"><input type="checkbox" name="in_gallery" value="1" checked> Mostrar fotos en la galería</label>
      </div>
      <div class="progress" hidden data-progress><span></span></div>
      <button class="btn btn-primary mt" type="submit"><?= icon('upload') ?> Subir</button>
      <p class="field-help mt">Formatos: JPG, PNG, WebP, GIF (máx. <?= human_bytes($limits['image']) ?>), MP4 y WebM (máx. <?= human_bytes($limits['video']) ?>), MP3, OGG, WAV, M4A (máx. <?= human_bytes($limits['audio']) ?>) y PDF (máx. <?= human_bytes($limits['document']) ?>). Las imágenes se optimizan automáticamente en WebP y se eliminan sus metadatos.</p>
    </form>
  </section>
  <section class="card">
    <h2><?= icon('video') ?> Video de YouTube o Vimeo</h2>
    <form method="post" action="<?= e(url('/admin/multimedia/video-en-linea')) ?>" class="stack">
      <?= csrf_field() ?>
      <label class="field-label" for="emb-url">Enlace del video</label>
      <input class="input" id="emb-url" name="url" type="url" required placeholder="https://www.youtube.com/watch?v=…">
      <label class="field-label" for="emb-title">Título</label>
      <input class="input" id="emb-title" name="title" maxlength="190">
      <label class="check"><input type="checkbox" name="in_gallery" value="1"> Mostrar en la galería</label>
      <button class="btn" type="submit"><?= icon('plus') ?> Agregar video</button>
      <p class="field-help">El video se reproduce desde la plataforma original (modo de privacidad mejorada de YouTube); no ocupa espacio en el servidor.</p>
    </form>
  </section>
</div>

<nav class="tabs">
  <?php foreach ([null => 'Todo', 'image' => 'Imágenes', 'video' => 'Videos', 'audio' => 'Audios', 'document' => 'Documentos'] as $t => $label): ?>
    <a href="<?= e($tabUrl($t ?: null)) ?>" aria-current="<?= ($tipo ?: '') === ($t ?: '') ? 'true' : 'false' ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>
<form class="filters" method="get">
  <?php if ($tipo): ?><input type="hidden" name="tipo" value="<?= e($tipo) ?>"><?php endif; ?>
  <input class="input" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Buscar por título, crédito o nombre de archivo" aria-label="Buscar">
  <select class="select" name="departamento" aria-label="Departamento"><option value="">Todos los departamentos</option><?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>" <?= (int) $filters['department_id'] === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select>
  <select class="select" name="coleccion" aria-label="Colección"><option value="">Todas las colecciones</option><?php foreach ($collections as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) $filters['collection_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <select class="select" name="galeria" aria-label="Galería"><option value="">En galería o no</option><option value="1" <?= $filters['gallery'] ? 'selected' : '' ?>>Solo en galería</option></select>
  <button class="btn" type="submit"><?= icon('filter') ?> Filtrar</button>
</form>
<p class="hint"><?= (int) $total ?> archivos</p>
<?php if ($items): ?>
<div class="media-grid">
  <?php foreach ($items as $m): ?>
    <a class="media-tile" href="<?= e(url('/admin/multimedia/' . $m['id'])) ?>">
      <div class="m-img"><img src="<?= e(Media::thumbUrl($m)) ?>" alt="" loading="lazy">
        <?php if ($m['is_demo']): ?><span class="pill pill-demo">Demo</span><?php elseif ($m['in_gallery']): ?><span class="pill pill-published">Galería</span><?php endif; ?></div>
      <div class="m-body"><strong><?= e($m['title'] ?: $m['original_name'] ?: 'Sin título') ?></strong><small><?= e(Media::KINDS[$m['kind']]) ?><?= $m['size_bytes'] ? ' · ' . human_bytes((int) $m['size_bytes']) : '' ?><?= $m['width'] ? ' · ' . (int) $m['width'] . '×' . (int) $m['height'] : '' ?></small></div>
    </a>
  <?php endforeach; ?>
</div>
<?php else: ?><div class="card empty-state"><?= icon('image') ?><p>No hay archivos con estos filtros.</p></div><?php endif; ?>
<?php if ($pages > 1): ?><nav class="pagination"><?php for ($p = 1; $p <= $pages; $p++): $q['pagina'] = $p; ?><?= $p === $page ? '<span class="is-current">' . $p . '</span>' : '<a href="?' . e(http_build_query($q)) . '">' . $p . '</a>' ?><?php endfor; ?></nav><?php endif; ?>
