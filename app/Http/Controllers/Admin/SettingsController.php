<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Services\Demo;
use Illuminate\Http\Request;
use Inertia\Inertia;

/** Configuración general del sitio y contenido de demostración (solo administradores). */
class SettingsController extends Controller
{
    private const FIELDS = [
        'site_name' => ['required|string|max:60', 'Nombre del sitio'],
        'site_tagline' => ['nullable|string|max:100', 'Lema'],
        'logo_pre' => ['nullable|string|max:30', 'Logotipo: texto pequeño superior'],
        'logo_title' => ['required|string|max:30', 'Logotipo: palabra principal'],
        'logo_subtitle' => ['nullable|string|max:40', 'Logotipo: subtítulo'],
        'footer_text' => ['nullable|string|max:300', 'Texto del pie de página'],
        'contact_email' => ['nullable|email|max:190', 'Correo de contacto'],
        'social_instagram' => ['nullable|url:https|max:300', 'Instagram'],
        'social_facebook' => ['nullable|url:https|max:300', 'Facebook'],
        'social_youtube' => ['nullable|url:https|max:300', 'YouTube'],
        'social_tiktok' => ['nullable|url:https|max:300', 'TikTok'],
        'social_x' => ['nullable|url:https|max:300', 'X (Twitter)'],
    ];

    public function edit()
    {
        return Inertia::render('Admin/Settings', [
            'title' => 'Configuración',
            'fields' => collect(self::FIELDS)->map(fn ($f, $k) => ['key' => $k, 'label' => $f[1], 'value' => Setting::value($k, ''), 'required' => str_starts_with($f[0], 'required')])->values(),
            'demo' => Demo::counts(),
        ]);
    }

    public function update(Request $request)
    {
        $v = $request->validate(collect(self::FIELDS)->map(fn ($f) => $f[0])->all(), ['url' => 'Escribe el enlace completo, empezando por https://']);
        foreach (array_keys(self::FIELDS) as $k) {
            Setting::put($k, (string) ($v[$k] ?? ''));
        }
        AuditLog::record('settings', 'settings', null, 'Configuración general actualizada');
        return back()->with('success', 'Configuración guardada.');
    }

    public function removeDemo(Request $request)
    {
        if ($request->input('confirm') !== 'ELIMINAR') {
            return back()->with('error', 'Escribe ELIMINAR para confirmar.');
        }
        $c = Demo::remove();
        AuditLog::record('delete', 'demo', null, "Contenido de demostración eliminado ({$c['entries']} publicaciones, {$c['media']} archivos)");
        return back()->with('success', "Se eliminaron {$c['entries']} publicaciones, {$c['routes']} rutas y {$c['media']} archivos de demostración.");
    }
}
