<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersProdutivosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. REMOVER usuários de teste existentes
        $this->command->info('Removendo usuários de teste...');
        User::whereIn('email', [
            'admin@admin.com',
            'diretor@diretor.com', 
            'comercial@comercial.com',
            'visitante@visitante.com'
        ])->delete();

        // 2. LISTA DE USUÁRIOS REAIS
        $usuariosReais = [
            [
                'email' => 'cesar@inovarepresentacao.com.br',
                'role' => 'admin',
                'nome' => 'César'
            ],
            [
                'email' => 'cintia.garcia@inovarepresentacao.com.br', 
                'role' => 'comercial',
                'nome' => 'Cintia Garcia'
            ],
            [
                'email' => 'cristiane@inovarepresentacao.com.br',
                'role' => 'admin', 
                'nome' => 'Cristiane'
            ],
            [
                'email' => 'daiane.escouto@inovarepresentacao.com.br',
                'role' => 'comercial',
                'nome' => 'Daiane Escouto'
            ],
            [
                'email' => 'julia.silva@inovarepresentacao.com.br',
                'role' => 'comercial',
                'nome' => 'Julia Silva'
            ],
            [
                'email' => 'juliana.specht@inovarepresentacao.com.br',
                'role' => 'comercial', 
                'nome' => 'Juliana Specht'
            ],
            [
                'email' => 'kelly.santos@inovarepresentacao.com.br',
                'role' => 'comercial',
                'nome' => 'Kelly Santos'
            ],
            [
                'email' => 'luciana.oliveira@inovarepresentacao.com.br',
                'role' => 'comercial',
                'nome' => 'Luciana Oliveira'
            ],
            [
                'email' => 'marcelo.ceroni@inovarepresentacao.com.br',
                'role' => 'comercial',
                'nome' => 'Marcelo Ceroni'
            ],
            [
                'email' => 'roberta.silva@inovarepresentacao.com.br',
                'role' => 'comercial',
                'nome' => 'Roberta Silva'
            ],
            [
                'email' => 'claudia.bavaresco@inovarepresentacao.com.br',
                'role' => 'diretor',
                'nome' => 'Claudia Bavaresco'
            ]
        ];

        // 3. CRIAR USUÁRIOS COM DADOS ADEQUADOS
        $this->command->info('Criando usuários reais...');
        $senhaDefault = 'Inova@2024'; // Senha documentada para primeiro acesso

        foreach ($usuariosReais as $userData) {
            // Verificar se já existe
            $user = User::where('email', $userData['email'])->first();
            
            if (!$user) {
                // Criar usuário
                $user = User::create([
                    'name' => $userData['nome'],
                    'email' => $userData['email'],
                    'password' => Hash::make($senhaDefault),
                    'email_verified_at' => now() // Consideramos verificado
                ]);

                $this->command->info("✓ Usuário criado: {$userData['nome']} ({$userData['email']})");
            } else {
                $this->command->info("→ Usuário já existe: {$userData['nome']} ({$userData['email']})");
            }

            // Atribuir role
            if (!$user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
                $this->command->info("  ✓ Role '{$userData['role']}' atribuída");
            }
        }

        // 4. RELATÓRIO DE COMERCIAIS DISPONÍVEIS
        $this->command->info("\n=== COMERCIAIS DISPONÍVEIS PARA ATRIBUIÇÃO DE CORRETORAS ===");
        $comerciais = User::role('comercial')->get();
        
        foreach ($comerciais as $comercial) {
            $this->command->info("• {$comercial->name} ({$comercial->email}) - ID: {$comercial->id}");
        }

        $this->command->info("\n=== INFORMAÇÕES IMPORTANTES ===");
        $this->command->info("📧 Senha padrão para todos: {$senhaDefault}");
        $this->command->info("👥 Total de usuários: " . count($usuariosReais));
        $this->command->info("🏢 Comerciais: " . $comerciais->count());
        $this->command->info("🔧 Admins: " . User::role('admin')->count());
        $this->command->info("📊 Diretores: " . User::role('diretor')->count());
        
        $this->command->info("\n✅ UsersProdutivosSeeder executado com sucesso!");
    }
}