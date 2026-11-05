<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'vpc:admin {--name=} {--email=} {--username=} {--role=admin}';
    protected $description = 'Crea una cuenta administrativa (la contraseña se pide oculta o se toma de VPC_ADMIN_PASSWORD).';

    public function handle(): int
    {
        $data = [
            'name' => $this->option('name') ?: $this->ask('Nombre completo'),
            'email' => mb_strtolower((string) ($this->option('email') ?: $this->ask('Correo'))),
            'username' => mb_strtolower((string) ($this->option('username') ?: $this->ask('Usuario'))),
            'role' => $this->option('role'),
            'password' => getenv('VPC_ADMIN_PASSWORD') ?: $this->secret('Contraseña (mínimo 10 caracteres, letras y números)'),
        ];
        $v = Validator::make($data, [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'username' => 'required|alpha_dash|min:3|max:60|unique:users,username',
            'role' => 'required|in:' . implode(',', array_keys(User::ROLES)),
            'password' => User::passwordRules(),
        ]);
        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) {
                $this->error($e);
            }
            return self::FAILURE;
        }
        $user = User::create($data + ['is_active' => true]);
        $this->info("Cuenta creada: {$user->username} ({$user->role}).");
        return self::SUCCESS;
    }
}
