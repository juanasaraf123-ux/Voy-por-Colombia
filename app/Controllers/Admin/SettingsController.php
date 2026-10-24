<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Request;
use App\Models\Demo;
use App\Models\Setting;

/** Configuración sensible del sitio (solo administradores). */
final class SettingsController extends AdminController
{
    private const FIELDS = [
        'site_name' => ['text', 60], 'site_tagline' => ['text', 100], 'footer_text' => ['text', 300], 'contact_email' => ['email', 190],
        'social_instagram' => ['url', 300], 'social_facebook' => ['url', 300], 'social_youtube' => ['url', 300], 'social_tiktok' => ['url', 300], 'social_x' => ['url', 300],
    ];

    protected function permission(): ?string
    {
        return 'settings.manage';
    }

    public function edit(): void
    {
        $this->render('settings', ['pageTitle' => 'Configuración', 'demo' => Demo::counts()]);
    }

    public function update(): void
    {
        $errors = [];
        $values = [];
        foreach (self::FIELDS as $key => [$type, $max]) {
            $v = Request::str($key, '', 1000);
            if (mb_strlen($v) > $max) {
                $errors[$key] = "Máximo $max caracteres.";
            } elseif ($v !== '' && $type === 'url' && (!filter_var($v, FILTER_VALIDATE_URL) || !preg_match('#^https://#i', $v))) {
                $errors[$key] = 'Escribe el enlace completo, empezando por https://';
            } elseif ($v !== '' && $type === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                $errors[$key] = 'Correo no válido.';
            }
            $values[$key] = $v;
        }
        if ($values['site_name'] === '') {
            $errors['site_name'] = 'El nombre del sitio es obligatorio.';
        }
        if ($errors) {
            $this->fail($errors, url('/admin/configuracion'));
        }
        foreach ($values as $k => $v) {
            Setting::set($k, $v);
        }
        Audit::log('settings', 'settings', null, 'Configuración general actualizada');
        flash('success', 'Configuración guardada.');
        redirect(url('/admin/configuracion'));
    }

    public function removeDemo(): void
    {
        if (Request::str('confirm') !== 'ELIMINAR') {
            flash('error', 'Escribe ELIMINAR para confirmar.');
            redirect(url('/admin/configuracion'));
        }
        $c = Demo::remove();
        Audit::log('delete', 'demo', null, "Contenido de demostración eliminado ({$c['entries']} publicaciones, {$c['media']} archivos)");
        flash('success', "Se eliminaron {$c['entries']} publicaciones, {$c['routes']} rutas y {$c['media']} archivos de demostración.");
        redirect(url('/admin/configuracion'));
    }
}
