<?php
declare(strict_types=1);

namespace App\Core;

/** Registro de acciones administrativas (quién, qué, cuándo). */
final class Audit
{
    public const ACTIONS = [
        'create' => 'Creó', 'update' => 'Editó', 'publish' => 'Publicó', 'unpublish' => 'Despublicó',
        'archive' => 'Archivó', 'delete' => 'Eliminó', 'upload' => 'Subió', 'login' => 'Inició sesión',
        'logout' => 'Cerró sesión', 'deactivate' => 'Desactivó', 'activate' => 'Activó', 'settings' => 'Configuró',
    ];

    public static function log(string $action, string $entityType, ?int $entityId, string $summary): void
    {
        Database::insert('audit_log', [
            'user_id' => Auth::id() ?? (Session::get('user_id') ?: null),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'summary' => mb_substr($summary, 0, 255),
            'ip' => mb_substr(PHP_SAPI === 'cli' ? 'cli' : Request::ip(), 0, 45),
            'created_at' => now(),
        ]);
    }
}
