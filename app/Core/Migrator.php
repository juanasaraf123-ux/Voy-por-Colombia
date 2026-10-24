<?php
declare(strict_types=1);

namespace App\Core;

/** Ejecuta las migraciones de database/migrations en orden y registra las aplicadas. */
final class Migrator
{
    public static function translate(string $sql): string
    {
        $mysql = Database::driver() === 'mysql';
        return strtr($sql, [
            '{PK}' => $mysql ? 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT',
            '{FK}' => $mysql ? 'INT UNSIGNED' : 'INTEGER',
            '{LONGTEXT}' => $mysql ? 'MEDIUMTEXT' : 'TEXT',
            '{ENGINE}' => $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '',
        ]);
    }

    /** @return string[] migraciones aplicadas en esta ejecución */
    public static function run(?callable $log = null): array
    {
        $pdo = Database::pdo();
        $pdo->exec(self::translate('CREATE TABLE IF NOT EXISTS migrations (migration VARCHAR(190) NOT NULL PRIMARY KEY, applied_at DATETIME NOT NULL){ENGINE}'));
        $done = array_column(Database::all('SELECT migration FROM migrations'), 'migration');
        $files = glob(BASE_PATH . '/database/migrations/*.php') ?: [];
        sort($files);
        $applied = [];
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $done, true)) {
                continue;
            }
            $statements = require $file;
            // MySQL confirma automáticamente las sentencias DDL, por eso cada migración se aplica sentencia por sentencia.
            foreach ($statements as $sql) {
                $pdo->exec(self::translate($sql));
            }
            Database::insert('migrations', ['migration' => $name, 'applied_at' => now()]);
            $applied[] = $name;
            if ($log) {
                $log("Migración aplicada: $name");
            }
        }
        return $applied;
    }
}
