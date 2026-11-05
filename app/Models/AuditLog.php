<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const UPDATED_AT = null;
    protected $table = 'audit_log';
    protected $guarded = [];

    public const ACTIONS = [
        'login' => 'Inició sesión', 'logout' => 'Cerró sesión', 'create' => 'Creó', 'update' => 'Editó', 'publish' => 'Publicó',
        'unpublish' => 'Despublicó', 'archive' => 'Archivó', 'delete' => 'Eliminó', 'upload' => 'Subió', 'settings' => 'Configuró',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Registra una acción administrativa. */
    public static function record(string $action, string $type, ?int $id, string $summary): void
    {
        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $type,
            'entity_id' => $id,
            'summary' => mb_substr($summary, 0, 255),
            'ip' => request()->ip(),
        ]);
    }
}
