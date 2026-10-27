<h1>Crear la primera cuenta</h1>
<p class="muted">Te damos la bienvenida. Crea la cuenta de administración para gestionar el contenido. Esta página desaparece en cuanto exista la primera cuenta.</p>
<form method="post" action="<?= e(url('/admin/instalar')) ?>" class="stack" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="setup_token" value="<?= e($token) ?>">
  <label class="field-label" for="name">Nombre completo</label>
  <input class="input" id="name" name="name" required value="<?= e(val('name')) ?>"><?= field_error('name') ?>
  <label class="field-label" for="email">Correo</label>
  <input class="input" id="email" name="email" type="email" required value="<?= e(val('email')) ?>"><?= field_error('email') ?>
  <label class="field-label" for="username">Usuario</label>
  <input class="input" id="username" name="username" required value="<?= e(val('username')) ?>"><?= field_error('username') ?>
  <label class="field-label" for="password">Contraseña</label>
  <input class="input" id="password" name="password" type="password" autocomplete="new-password" required><?= field_error('password') ?>
  <label class="field-label" for="password_confirmation">Repite la contraseña</label>
  <input class="input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required><?= field_error('password_confirmation') ?>
  <button class="btn btn-primary btn-block" type="submit">Crear administrador</button>
</form>
