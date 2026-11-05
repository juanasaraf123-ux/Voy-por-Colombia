<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Support\Sections;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

/** Categorías de cada sección (filtros del sitio y del mapa). */
class CategoryController extends Controller
{
    public const ICONS = ['leaf', 'mountain', 'wave', 'columns', 'farm', 'fork', 'mask', 'bird', 'city', 'cup', 'star', 'book', 'fire', 'user', 'users', 'cycle', 'route', 'hand', 'party', 'music', 'dance', 'calendar', 'map', 'compass', 'coffee', 'store', 'camera', 'news', 'sparkle', 'tag', 'heart', 'globe', 'waterfall', 'binoculars', 'footprints', 'tent', 'palm', 'sun'];

    public function index()
    {
        return Inertia::render('Admin/Categories', [
            'title' => 'Categorías',
            'sections' => collect(Sections::ALL)->map(fn ($s, $k) => ['key' => $k] + $s)->values(),
            'categories' => Category::withCount('entries')->orderBy('section')->orderBy('sort_order')->orderBy('name')->get(),
            'icons' => self::ICONS,
        ]);
    }

    private function data(Request $request): array
    {
        $v = $request->validate([
            'name' => 'required|string|min:2|max:80',
            'icon' => 'nullable|in:' . implode(',', self::ICONS),
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort_order' => 'nullable|integer|min:0|max:99',
        ]);
        return ['name' => $v['name'], 'icon' => $v['icon'] ?? 'tag', 'color' => $v['color'] ?? '#0f9d77', 'sort_order' => (int) ($v['sort_order'] ?? 0)];
    }

    public function store(Request $request)
    {
        $section = $request->validate(['section' => 'required|in:' . implode(',', array_keys(Sections::ALL))])['section'];
        $data = $this->data($request);
        $slug = Str::slug($data['name']);
        if (Category::where('section', $section)->where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'Ya existe una categoría con ese nombre en la sección.']);
        }
        $c = Category::create($data + ['section' => $section, 'slug' => $slug]);
        AuditLog::record('create', 'category', $c->id, 'Categoría: ' . $c->name);
        return back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->data($request));
        AuditLog::record('update', 'category', $category->id, 'Categoría: ' . $category->name);
        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        AuditLog::record('delete', 'category', $category->id, 'Categoría: ' . $category->name);
        return back()->with('success', 'Categoría eliminada. Las publicaciones se conservan.');
    }
}
