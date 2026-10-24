<?php
use App\Sections;
$socials = array_filter([
    'instagram' => setting('social_instagram'), 'facebook' => setting('social_facebook'), 'youtube' => setting('social_youtube'),
    'tiktok' => setting('social_tiktok'), 'x' => setting('social_x'),
]);
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a class="brand" href="<?= e(url('/')) ?>" style="color:#fff"><?= App\Core\View::partial('logo') ?><span class="brand-text"><span class="brand-name"><?= e(setting('site_name')) ?></span><span class="brand-tag">Tierra de Infinitos</span></span></a>
        <p><?= e(setting('footer_text')) ?></p>
        <?php if ($socials): ?>
          <div class="socials">
            <?php foreach ($socials as $net => $link): ?>
              <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(ucfirst($net)) ?>"><?= icon($net) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div>
        <h4>Explora</h4>
        <ul>
          <?php foreach (Sections::ALL as $s): ?><li><a href="<?= e(url('/' . $s['path'])) ?>"><?= e($s['label']) ?></a></li><?php endforeach; ?>
          <li><a href="<?= e(url('/galeria')) ?>">Galería de fotos</a></li>
        </ul>
      </div>
      <div>
        <h4>Planea</h4>
        <ul>
          <li><a href="<?= e(url('/turismo')) ?>">Mapa interactivo</a></li>
          <li><a href="<?= e(url('/rutas')) ?>">Rutas sugeridas</a></li>
          <li><a href="<?= e(url('/mi-ruta')) ?>">Mi ruta</a></li>
          <li><a href="<?= e(url('/favoritos')) ?>">Favoritos</a></li>
          <li><a href="<?= e(url('/buscar')) ?>">Buscar</a></li>
        </ul>
      </div>
      <div>
        <h4>Regiones</h4>
        <ul>
          <?php foreach (App\Models\Geo::regions() as $r): ?><li><a href="<?= e(url('/turismo', ['region' => $r['slug']])) ?>"><?= e($r['name']) ?></a></li><?php endforeach; ?>
        </ul>
        <?php if (setting('contact_email')): ?><p class="mt-2"><a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></p><?php endif; ?>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= e(setting('site_name')) ?> · <?= e(setting('site_tagline')) ?></span>
      <span>Límites geográficos: <a href="https://www.geoboundaries.org" target="_blank" rel="noopener">geoBoundaries</a> (datos © colaboradores de OpenStreetMap, ODbL) · Las imágenes conservan los créditos de sus autores.</span>
    </div>
  </div>
</footer>
