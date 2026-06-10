<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminUser extends Command
{
    protected $signature = 'admin:reset
                            {--email=admin@bellacria.com.br : E-mail do usuário admin}
                            {--password=admin@bellacria2026 : Senha do usuário admin}
                            {--name=Admin : Nome do usuário admin}';

    protected $description = 'Remove todos os usuários e cria um único admin';

    public function handle(): int
    {
        $email    = $this->option('email');
        $password = $this->option('password');
        $name     = $this->option('name');

        $count = User::count();

        if (! $this->confirm("Isso vai apagar {$count} usuário(s) existente(s) e criar apenas \"{$email}\". Confirma?", true)) {
            $this->info('Operação cancelada.');
            return self::SUCCESS;
        }

        User::query()->delete();

        User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info("Feito. Usuário criado: {$email}");

        return self::SUCCESS;
    }
}
