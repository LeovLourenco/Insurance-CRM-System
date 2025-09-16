<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cotacao;
use App\Models\User;

class AssignUsersToCotacoes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cotacoes:assign-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atribui user_id às cotações existentes que não têm usuário definido';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔄 Iniciando atribuição de usuários às cotações...');

        // Buscar cotações sem user_id
        $cotacoesSemUser = Cotacao::whereNull('user_id')->count();
        
        if ($cotacoesSemUser === 0) {
            $this->info('✅ Todas as cotações já possuem usuário atribuído!');
            return 0;
        }

        $this->info("📊 Encontradas {$cotacoesSemUser} cotações sem usuário definido");

        // Buscar primeiro comercial para atribuir
        $comercial = User::role('comercial')->first();
        
        if (!$comercial) {
            $this->error('❌ Nenhum usuário com role "comercial" encontrado!');
            $this->info('💡 Execute primeiro: php artisan db:seed --class=UsersSeeder');
            return 1;
        }

        // Confirmar ação
        if ($this->confirm("Deseja atribuir todas as cotações ao usuário '{$comercial->name}' ({$comercial->email})?", true)) {
            
            $this->info("🔄 Atribuindo cotações ao usuário: {$comercial->name}");
            
            // Atualizar cotações
            $atualizada = Cotacao::whereNull('user_id')->update([
                'user_id' => $comercial->id
            ]);

            $this->info("✅ {$atualizada} cotações atualizadas com sucesso!");
            
            // Mostrar resumo
            $this->table(
                ['Usuário', 'Email', 'Role', 'Cotações Atribuídas'],
                [[$comercial->name, $comercial->email, 'comercial', $atualizada]]
            );
            
        } else {
            $this->info('❌ Operação cancelada pelo usuário');
            return 0;
        }

        return 0;
    }
}