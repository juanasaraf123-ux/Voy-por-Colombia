<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Database as DB;
use App\Core\Request;
use App\Models\User;

/** Registro de auditoría (solo administradores). */
final class AuditController extends AdminController
{
    protected function permission(): ?string
    {
        return 'audit.view';
    }

    public function index(): void
    {
        $w = ['1 = 1'];
        $p = [];
        if ($uid = Request::int('usuario')) {
            $w[] = 'a.user_id = ?';
            $p[] = $uid;
        }
        if (array_key_exists($a = Request::str('accion'), Audit::ACTIONS)) {
            $w[] = 'a.action = ?';
            $p[] = $a;
        }
        if ($q = Request::str('q', '', 100)) {
            $w[] = "a.summary LIKE ? ESCAPE '!'";
            $p[] = '%' . like_escape($q) . '%';
        }
        $where = implode(' AND ', $w);
        $page = max(1, Request::int('pagina', 1) ?? 1);
        $total = (int) DB::value("SELECT COUNT(*) FROM audit_log a WHERE $where", $p);
        $rows = DB::all("SELECT a.*, u.name AS user_name, u.username FROM audit_log a LEFT JOIN users u ON u.id = a.user_id WHERE $where ORDER BY a.id DESC LIMIT 50 OFFSET " . (($page - 1) * 50), $p);
        $this->render('audit', ['pageTitle' => 'Auditoría', 'rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int) max(1, ceil($total / 50)), 'users' => User::all()]);
    }
}
