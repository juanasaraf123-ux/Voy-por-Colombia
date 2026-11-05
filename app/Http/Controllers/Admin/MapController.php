<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Entry;
use App\Models\Media;
use App\Models\Municipality;
use App\Models\Region;
use Illuminate\Http\Request;
use Inertia\Inertia;

/** Gestión del mapa: ubicación de publicaciones y datos de departamentos. */
class MapController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Map/Index', [
            'title' => 'Gestión del mapa',
            'markers' => Entry::whereNotNull('lat')->whereNotNull('lng')->orderBy('title')->get(['id', 'title', 'lat', 'lng', 'status', 'section']),
            'missing' => Entry::where('section', '<>', 'news')->where(fn ($q) => $q->whereNull('lat')->orWhereNull('lng'))->orderBy('title')->get(['id', 'title', 'section', 'status']),
            'departments' => Department::with('region:id,name,color')->orderBy('name')->get(['id', 'name', 'slug', 'capital', 'region_id', 'cover_media_id']),
            'counts' => Entry::groupBy('department_id')->selectRaw('department_id, COUNT(*) AS n')->pluck('n', 'department_id'),
        ]);
    }

    public function editDepartment(Department $department)
    {
        $department->load('cover');
        return Inertia::render('Admin/Map/Department', [
            'title' => 'Departamento: ' . $department->name,
            'dep' => $department->only(['id', 'name', 'slug', 'capital', 'summary', 'region_id', 'cover_media_id']),
            'cover' => $department->cover?->toPublic(),
            'regions' => Region::orderBy('sort_order')->get(['id', 'name']),
            'municipalities' => Municipality::where('department_id', $department->id)->orderBy('name')->pluck('name'),
        ]);
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $v = $request->validate([
            'region_id' => 'nullable|integer|exists:regions,id', 'capital' => 'nullable|string|max:100',
            'summary' => 'nullable|string|max:500', 'cover_media_id' => 'nullable|integer|exists:media,id',
        ]);
        if (!empty($v['cover_media_id']) && Media::find($v['cover_media_id'])->kind !== 'image') {
            return back()->withErrors(['cover_media_id' => 'Selecciona una imagen.']);
        }
        $department->update($v);
        AuditLog::record('update', 'department', $department->id, 'Departamento: ' . $department->name);
        return back()->with('success', 'Departamento actualizado.');
    }

    /** Coordenadas al arrastrar el marcador en el mapa del panel. */
    public function updateLocation(Request $request, Entry $entry)
    {
        $v = $request->validate(['lat' => 'required|numeric|between:-5,14', 'lng' => 'required|numeric|between:-83,-66'], ['between' => 'Coordenadas fuera de Colombia.']);
        $entry->update(['lat' => round((float) $v['lat'], 6), 'lng' => round((float) $v['lng'], 6), 'updated_by' => $request->user()->id]);
        AuditLog::record('update', 'entry', $entry->id, 'Ubicación en el mapa: ' . $entry->title);
        return back()->with('success', 'Ubicación de «' . $entry->title . '» actualizada.');
    }

    /** Municipios de un departamento (selects dependientes y selector de coordenadas). */
    public function apiMunicipalities(Request $request)
    {
        $dep = $request->integer('departamento');
        return ['items' => $dep ? Municipality::where('department_id', $dep)->orderBy('name')->get(['id', 'name', 'slug', 'lat', 'lng']) : []];
    }
}
