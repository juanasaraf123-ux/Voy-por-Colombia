<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Database as DB;
use App\Core\HttpException;
use App\Core\Request;
use App\Sections;

/** Categorías de cada sección (filtros del sitio y del mapa). */
final class CategoryController extends AdminController
{
    public const ICONS = ['leaf', 'mountain', 'wave', 'columns', 'farm', 'fork', 'mask', 'bird', 'city', 'cup', 'star', 'book', 'fire', 'user', 'cycle', 'route', 'hand', 'party', 'music', 'dance', 'calendar', 'map', 'compass', 'coffee', 'store', 'camera', 'news', 'sparkle', 'tag', 'heart', 'globe'];

    protected function permission(): ?string
    {
        return 'categories.manage';
    }

    public function index(): void
    {
        $rows = DB::all('SELECT c.*, (SELECT COUNT(*) FROM entry_categories ec WHERE ec.category_id = c.id) AS uses FROM categories c ORDER BY c.section, c.sort_order, c.name');
        $by = [];
        foreach ($rows as $r) {
            $by[$r['section']][] = $r;
        }
        $this->render('categories', ['pageTitle' => 'Categorías', 'bySection' => $by, 'icons' => self::ICONS]);
    }

    private function data(): array
    {
        $name = Request::str('name', '', 100);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
            $this->fail(['name' => 'El nombre debe tener entre 2 y 80 caracteres.'], url('/admin/categorias'));
        }
        $color = Request::str('color', '#0f9d77', 9);
        return [
            'name' => $name,
            'icon' => in_array(Request::str('icon'), self::ICONS, true) ? Request::str('icon') : 'tag',
            'color' => preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#0f9d77',
            'sort_order' => max(0, min(99, Request::int('sort_order', 0) ?? 0)),
        ];
    }

    public function store(): void
    {
        $section = Request::str('section');
        if (!Sections::exists($section)) {
            throw new HttpException(422, 'Sección no válida.');
        }
        $data = $this->data();
        $slug = slugify($data['name']);
        if (DB::value('SELECT id FROM categories WHERE section = ? AND slug = ?', [$section, $slug])) {
            $this->fail(['name' => 'Ya existe una categoría con ese nombre en la sección.'], url('/admin/categorias'));
        }
        $id = DB::insert('categories', $data + ['section' => $section, 'slug' => $slug]);
        Audit::log('create', 'category', $id, 'Categoría: ' . $data['name']);
        flash('success', 'Categoría creada.');
        redirect(url('/admin/categorias') . '#sec-' . $section);
    }

    public function update(string $id): void
    {
        $cat = DB::one('SELECT * FROM categories WHERE id = ?', [(int) $id]) ?? throw new HttpException(404);
        $data = $this->data();
        DB::update('categories', $data, 'id = :cid', ['cid' => (int) $id]);
        Audit::log('update', 'category', (int) $id, 'Categoría: ' . $data['name']);
        flash('success', 'Categoría actualizada.');
        redirect(url('/admin/categorias') . '#sec-' . $cat['section']);
    }

    public function destroy(string $id): void
    {
        $cat = DB::one('SELECT * FROM categories WHERE id = ?', [(int) $id]) ?? throw new HttpException(404);
        DB::run('DELETE FROM categories WHERE id = ?', [(int) $id]);
        Audit::log('delete', 'category', (int) $id, 'Categoría: ' . $cat['name']);
        flash('success', 'Categoría eliminada. Las publicaciones se conservan.');
        redirect(url('/admin/categorias') . '#sec-' . $cat['section']);
    }
}
