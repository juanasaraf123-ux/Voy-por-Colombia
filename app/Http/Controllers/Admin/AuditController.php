<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index()
    {
        $page = AuditLog::with('user:id,name')->orderByDesc('id')->paginate(50, ['*'], 'pagina');
        return Inertia::render('Admin/Audit', [
            'title' => 'Auditoría',
            'items' => $page->getCollection()->map(fn ($a) => [
                'id' => $a->id, 'user' => $a->user?->name ?? 'Sistema', 'action' => AuditLog::ACTIONS[$a->action] ?? $a->action,
                'type' => $a->entity_type, 'summary' => $a->summary, 'ip' => $a->ip, 'at' => $a->created_at?->toIso8601String(),
            ]),
            'pagination' => ['page' => $page->currentPage(), 'pages' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }
}
