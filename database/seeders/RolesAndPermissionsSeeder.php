<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar permissions para ENTIDADES BASE (compartilhadas)
        $basePermissions = [
            // Segurados
            'segurados.view',
            'segurados.create',
            'segurados.update',
            'segurados.delete',
            
            // Corretoras
            'corretoras.view',
            'corretoras.create',
            'corretoras.update',
            'corretoras.delete',
            
            // Produtos
            'produtos.view',
            'produtos.create',
            'produtos.update',
            'produtos.delete',
            
            // Seguradoras
            'seguradoras.view',
            'seguradoras.create',
            'seguradoras.update',
            'seguradoras.delete',
        ];

        // Criar permissions para CORE OPERACIONAL (isolado por comercial)
        $corePermissions = [
            // Cotações
            'cotacoes.view',
            'cotacoes.view.all',     // Para diretor ver todas
            'cotacoes.create',
            'cotacoes.update',
            'cotacoes.update.all',   // Para admin editar qualquer uma
            'cotacoes.delete',
            'cotacoes.delete.all',   // Para admin deletar qualquer uma
            
            // Cotação Seguradoras
            'cotacao-seguradoras.view',
            'cotacao-seguradoras.view.all',
            'cotacao-seguradoras.create',
            'cotacao-seguradoras.update',
            'cotacao-seguradoras.update.all',
            'cotacao-seguradoras.delete',
            'cotacao-seguradoras.delete.all',
            
            // Atividades
            'atividades-cotacao.view',
            'atividades-cotacao.view.all',
            'atividades-cotacao.create',
            'atividades-cotacao.update',
            'atividades-cotacao.delete',
        ];

        // Criar permissions administrativas
        $adminPermissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'dashboard.admin',
        ];

        // Criar todas as permissions (ou atualizar se já existirem)
        foreach (array_merge($basePermissions, $corePermissions, $adminPermissions) as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // === CRIAR ROLES ===

        // 1. ADMIN - CRUD total (base + todos os cores)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // 2. DIRETOR - READ base + CRUD cotações próprias + READ todas (supervisão)
        $diretorRole = Role::firstOrCreate(['name' => 'diretor']);
        $diretorRole->syncPermissions([
            // Base entities - full read access
            'segurados.view',
            'corretoras.view', 
            'produtos.view',
            'seguradoras.view',
            
            // Core - CRUD próprias + supervisão de todas
            'cotacoes.view',        // ✅ Para ver suas próprias
            'cotacoes.view.all',    // ✅ Para supervisionar todas
            'cotacoes.create',      // ✅ Para criar (com ownership)
            'cotacoes.update',      // ✅ Para editar suas próprias
            'cotacoes.delete',      // ✅ Para deletar suas próprias
            
            'cotacao-seguradoras.view',
            'cotacao-seguradoras.view.all',
            'cotacao-seguradoras.create',
            'cotacao-seguradoras.update',
            'cotacao-seguradoras.delete',
            
            'atividades-cotacao.view',
            'atividades-cotacao.view.all',
            'atividades-cotacao.create',
            'atividades-cotacao.update',
        ]);

        // 3. COMERCIAL - READ base + CRUD cadastros necessários + CRUD apenas suas cotações
        $comercialRole = Role::firstOrCreate(['name' => 'comercial']);
        $comercialRole->syncPermissions([
            // Base entities - READ + CREATE (para workflow de cotações)
            'segurados.view',
            'segurados.create',    // ✅ NECESSÁRIO: Criar segurados durante cotações
            'segurados.update',    // ✅ NECESSÁRIO: Atualizar dados dos segurados
            
            'corretoras.view',
            'corretoras.create',   // ✅ NECESSÁRIO: Criar corretoras durante cotações
            'corretoras.update',   // ✅ NECESSÁRIO: Atualizar dados das corretoras
            
            // Produtos e Seguradoras - apenas read (admin mantém controle)
            'produtos.view', 
            'seguradoras.view',
            
            // Core - own records only
            'cotacoes.view',
            'cotacoes.create',
            'cotacoes.update',
            'cotacoes.delete',
            
            'cotacao-seguradoras.view',
            'cotacao-seguradoras.create',
            'cotacao-seguradoras.update',
            'cotacao-seguradoras.delete',
            
            'atividades-cotacao.view',
            'atividades-cotacao.create',
            'atividades-cotacao.update',
        ]);

        // 4. VISITANTE - Para implementação futura
        $visitanteRole = Role::firstOrCreate(['name' => 'visitante']);
        // Sem permissions por enquanto

        $this->command->info('Roles e permissions criadas com sucesso!');
        $this->command->info('- admin: CRUD total');
        $this->command->info('- diretor: READ base + CRUD próprias cotações + supervisão todas');
        $this->command->info('- comercial: READ base + CRUD próprias cotações');
        $this->command->info('- visitante: Sem permissions (implementação futura)');
    }
}