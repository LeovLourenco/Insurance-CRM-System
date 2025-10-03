<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MarkExistingUsersPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Marcar todos os usuários existentes como tendo senha padrão (não alterada)
        // Isso forçará a troca de senha na próxima sessão
        \App\Models\User::where('password_changed', true)
            ->orWhereNull('password_changed')
            ->update([
                'password_changed' => false,
                'password_changed_at' => null
            ]);
            
        $this->command->info('Todos os usuários foram marcados para trocar senha padrão.');
    }
}
