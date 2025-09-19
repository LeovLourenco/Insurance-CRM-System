<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResetUserPasswordsSeeder extends Seeder
{
    public function run()
    {
        // Hash já gerado no Tinker
        $senhaHash = '$2y$10$I/3ms46dgPyvyCmEweq41Ooe6iAqgSHfaOEzM.3O58F/rrK2jrCs.';
        $senhaDefault = 'Inova@2025';

        // Atualiza TODOS os usuários
        DB::table('users')->update(['password' => $senhaHash]);

        $this->command->info("✅ Todas as senhas foram resetadas para: {$senhaDefault}");
    }
}
