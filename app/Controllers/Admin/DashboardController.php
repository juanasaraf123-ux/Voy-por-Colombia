<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Models\Demo;
use App\Models\Entry;
use App\Sections;

final class DashboardController extends AdminController
{
    public function index(): void
    {
        $bySection = [];
        foreach (DB::all("SELECT section, status, COUNT(*) AS total FROM entries GROUP BY section, status") as $r) {
            $bySection[$r['section']][$r['status']] = (int) $r['total'];
        }
        $this->render('dashboard', [
            'pageTitle' => 'Panel',
            'bySection' => $bySection,
            'stats' => [
                ['Destinos', Entry::count(['section' => 'tourism'], false), 'compass', '#0f9d77', url('/admin/contenido/turismo')],
                ['Departamentos', (int) DB::value('SELECT COUNT(*) FROM departments'), 'map', '#2bb673', url('/admin/mapa')],
                ['Municipios', (int) DB::value('SELECT COUNT(*) FROM municipalities'), 'pin', '#12b5b0', url('/admin/mapa')],
                ['Fotografías', (int) DB::value("SELECT COUNT(*) FROM media WHERE kind = 'image'"), 'image', '#f2a900', url('/admin/multimedia?tipo=image')],
                ['Videos', (int) DB::value("SELECT COUNT(*) FROM media WHERE kind IN ('video', 'embed')"), 'video', '#ff3f8e', url('/admin/multimedia?tipo=video')],
                ['Noticias', Entry::count(['section' => 'news'], false), 'news', '#1d6fe0', url('/admin/contenido/noticias')],
                ['Experiencias', Entry::count(['sections' => ['gastronomy', 'coffee', 'culture']], false), 'sparkle', '#ff7a1a', url('/admin/contenido/cultura')],
                ['Emprendimientos', Entry::count(['section' => 'entrepreneurship'], false), 'store', '#7b3fe4', url('/admin/contenido/emprendimiento')],
                ['Rutas', (int) DB::value('SELECT COUNT(*) FROM routes'), 'route', '#0f5c4a', url('/admin/rutas')],
            ],
            'published' => Entry::count([], true),
            'drafts' => Entry::count(['status' => 'draft'], false),
            'scheduled' => Entry::count(['status' => 'scheduled'], false),
            'recent' => Entry::query(['order' => 'updated', 'limit' => 8], false),
            'activity' => DB::all('SELECT a.*, u.name AS user_name FROM audit_log a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 10'),
            'noCoords' => (int) DB::value("SELECT COUNT(*) FROM entries WHERE section = 'tourism' AND (lat IS NULL OR lng IS NULL) AND status = 'published'"),
            'demo' => Demo::counts(),
            'sections' => Sections::ALL,
        ]);
    }
}
