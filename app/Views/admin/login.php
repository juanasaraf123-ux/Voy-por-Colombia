<h1>Bienvenido de nuevo</h1>
<p class="muted">Ingresa con tu correo o nombre de usuario.</p>
<form method="post" action="<?= e(url('/admin/login')) ?>" class="stack" novalidate>
  <?= csrf_field() ?>
  <label class="field-label" for="login">Correo o usuario</label>
  <input class="input" id="login" name="login" type="text" autocomplete="username" required autofocus value="<?= e(val('login')) ?>">
  <label class="field-label" for="password">Contraseña</label>
  <input class="input" id="password" name="password" type="password" autocomplete="current-password" required>
  <button class="btn btn-primary btn-block" type="submit"><?= icon('arrow-right') ?> Ingresar</button>
</form>
<?php if ($noUsers): ?>
  <p class="hint mt">Aún no hay cuentas. Crea la primera desde la consola con <code>php bin/create-admin.php</code> (ver README).</p>
<?php endif; ?>
<p class="hint mt"><a href="<?= e(url('/')) ?>">← Volver al sitio</a></p>
