<a class="brand" href="<?= e(url('/')) ?>" aria-label="<?= e(setting('site_name')) ?>, ir al inicio">
  <?= App\Core\View::partial('logo') ?>
  <span class="brand-text">
    <?php if (setting('logo_pre')): ?><span class="brand-pre"><?= e(setting('logo_pre')) ?></span><?php endif; ?>
    <span class="brand-name"><?= e(setting('logo_title')) ?></span>
    <span class="brand-tag"><?= e(setting('logo_subtitle')) ?></span>
  </span>
</a>
