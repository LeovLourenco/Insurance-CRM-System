<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Corretora;
use App\Models\User;
use App\Models\Produto;
use App\Models\Seguradora;
use App\Models\CadastroCorretor;
use App\Models\CorretoraSeguradora;
use App\Models\SeguradoraProduto;
use Spatie\Activitylog\Models\Activity;

class TestAuditoria extends Command
{
    protected $signature = 'test:auditoria {--restore : Restaurar os dados alterados durante o teste}';
    protected $description = 'Testa se o sistema de auditoria Spatie ActivityLog está funcionando corretamente';

    private $dadosOriginais = [];

    public function handle()
    {
        if ($this->option('restore')) {
            return $this->restaurarDados();
        }

        $this->info('🔍 Iniciando teste do sistema de auditoria...');
        $this->newLine();

        $sucessos = 0;
        $falhas = 0;

        $testes = [
            'Corretora' => function() { return $this->testarCorretora(); },
            'User' => function() { return $this->testarUser(); },
            'Produto' => function() { return $this->testarProduto(); },
            'Seguradora' => function() { return $this->testarSeguradora(); },
            'CadastroCorretor' => function() { return $this->testarCadastroCorretor(); },
            'CorretoraSeguradora (Relacionamento)' => function() { return $this->testarCorretoraSeguradora(); },
            'SeguradoraProduto (Relacionamento)' => function() { return $this->testarSeguradoraProduto(); },
        ];

        foreach ($testes as $modelo => $funcaoTeste) {
            $this->info("📋 Testando model: {$modelo}");
            
            if ($funcaoTeste()) {
                $this->info("✅ {$modelo}: SUCESSO");
                $sucessos++;
            } else {
                $this->error("❌ {$modelo}: FALHA");
                $falhas++;
            }
            $this->newLine();
        }

        $this->info('📊 RESULTADOS FINAIS:');
        $this->info("✅ Sucessos: {$sucessos}");
        $this->info("❌ Falhas: {$falhas}");
        
        if ($falhas === 0) {
            $this->info('🎉 Sistema de auditoria funcionando perfeitamente!');
            $this->newLine();
            $this->warn('💡 Execute "php artisan test:auditoria --restore" para limpar os dados de teste');
            return 0;
        } else {
            $this->error('⚠️  Sistema de auditoria apresenta problemas!');
            return 1;
        }
    }

    private function testarCorretora()
    {
        try {
            $corretora = Corretora::first();
            if (!$corretora) {
                $this->warn('   Nenhuma corretora encontrada para teste');
                return false;
            }

            $this->dadosOriginais['Corretora'][$corretora->id] = [
                'nome' => $corretora->nome,
                'email' => $corretora->email
            ];

            $nomeOriginal = $corretora->nome;
            $emailOriginal = $corretora->email;

            $corretora->update([
                'nome' => $nomeOriginal . ' [TESTE AUDITORIA]',
                'email' => 'teste.auditoria@exemplo.com'
            ]);

            return $this->verificarLog($corretora, 'updated');

        } catch (\Exception $e) {
            $this->error("   Erro: {$e->getMessage()}");
            return false;
        }
    }

    private function testarUser()
    {
        try {
            $user = User::first();
            if (!$user) {
                $this->warn('   Nenhum usuário encontrado para teste');
                return false;
            }

            $this->dadosOriginais['User'][$user->id] = [
                'name' => $user->name,
                'telefone' => $user->telefone
            ];

            $nomeOriginal = $user->name;
            
            $user->update([
                'name' => $nomeOriginal . ' [TESTE AUDITORIA]',
                'telefone' => '(11) 99999-9999'
            ]);

            return $this->verificarLog($user, 'updated');

        } catch (\Exception $e) {
            $this->error("   Erro: {$e->getMessage()}");
            return false;
        }
    }

    private function testarProduto()
    {
        try {
            $produto = Produto::first();
            if (!$produto) {
                $this->warn('   Nenhum produto encontrado para teste');
                return false;
            }

            $this->dadosOriginais['Produto'][$produto->id] = [
                'nome' => $produto->nome,
                'descricao' => $produto->descricao
            ];

            $nomeOriginal = $produto->nome;
            
            $produto->update([
                'nome' => $nomeOriginal . ' [TESTE AUDITORIA]',
                'descricao' => 'Teste de auditoria do sistema'
            ]);

            return $this->verificarLog($produto, 'updated');

        } catch (\Exception $e) {
            $this->error("   Erro: {$e->getMessage()}");
            return false;
        }
    }

    private function testarSeguradora()
    {
        try {
            $seguradora = Seguradora::first();
            if (!$seguradora) {
                $this->warn('   Nenhuma seguradora encontrada para teste');
                return false;
            }

            $this->dadosOriginais['Seguradora'][$seguradora->id] = [
                'nome' => $seguradora->nome,
                'cnpj' => $seguradora->cnpj
            ];

            $nomeOriginal = $seguradora->nome;
            
            $seguradora->update([
                'nome' => $nomeOriginal . ' [TESTE AUDITORIA]',
                'cnpj' => '12.345.678/0001-90'
            ]);

            return $this->verificarLog($seguradora, 'updated');

        } catch (\Exception $e) {
            $this->error("   Erro: {$e->getMessage()}");
            return false;
        }
    }

    private function testarCadastroCorretor()
    {
        try {
            $cadastro = CadastroCorretor::first();
            if (!$cadastro) {
                $this->warn('   Nenhum cadastro de corretor encontrado para teste');
                return false;
            }

            $this->dadosOriginais['CadastroCorretor'][$cadastro->id] = [
                'corretora' => $cadastro->corretora,
                'responsavel' => $cadastro->responsavel
            ];

            $corretoraOriginal = $cadastro->corretora;
            
            $cadastro->update([
                'corretora' => $corretoraOriginal . ' [TESTE AUDITORIA]',
                'responsavel' => 'Teste Auditoria Responsavel'
            ]);

            return $this->verificarLog($cadastro, 'updated');

        } catch (\Exception $e) {
            $this->error("   Erro: {$e->getMessage()}");
            return false;
        }
    }

    private function verificarLog($model, $evento)
    {
        $activity = Activity::where('subject_type', get_class($model))
                           ->where('subject_id', $model->id)
                           ->where('event', $evento)
                           ->latest()
                           ->first();

        if ($activity) {
            $this->info("   ✓ Log de auditoria criado (ID: {$activity->id})");
            $this->info("   ✓ Evento: {$activity->event}");
            $this->info("   ✓ Descrição: {$activity->description}");
            
            if ($activity->properties && $activity->properties->has('attributes')) {
                $this->info("   ✓ Propriedades registradas: " . count($activity->properties['attributes']) . " campos");
            }
            
            return true;
        } else {
            $this->error("   ✗ Nenhum log de auditoria encontrado");
            return false;
        }
    }

    private function restaurarDados()
    {
        $this->info('🔄 Restaurando dados alterados durante o teste...');
        $this->newLine();

        $modelos = [
            'Corretora' => Corretora::class,
            'User' => User::class,
            'Produto' => Produto::class,
            'Seguradora' => Seguradora::class,
            'CadastroCorretor' => CadastroCorretor::class,
            'CorretoraSeguradora' => CorretoraSeguradora::class,
            'SeguradoraProduto' => SeguradoraProduto::class,
        ];

        foreach ($this->dadosOriginais as $nomeModelo => $registros) {
            $classeModelo = $modelos[$nomeModelo];
            
            foreach ($registros as $id => $dadosOriginais) {
                try {
                    $modelo = $classeModelo::find($id);
                    if ($modelo) {
                        $modelo->update($dadosOriginais);
                        $this->info("✅ {$nomeModelo} ID {$id} restaurado");
                    }
                } catch (\Exception $e) {
                    $this->error("❌ Erro ao restaurar {$nomeModelo} ID {$id}: {$e->getMessage()}");
                }
            }
        }

        $this->info('🧹 Removendo logs de teste...');
        
        $logsRemovidos = Activity::where('description', 'like', '%[TESTE AUDITORIA]%')
                                ->orWhere('properties->attributes', 'like', '%TESTE AUDITORIA%')
                                ->delete();
        
        $this->info("✅ {$logsRemovidos} logs de teste removidos");
        $this->info('🎉 Restauração concluída!');
        
        return 0;
    }

    private function testarCorretoraSeguradora()
    {
        try {
            $corretora = Corretora::first();
            $seguradora = Seguradora::first();
            
            if (!$corretora || !$seguradora) {
                $this->warn('   Nenhuma corretora ou seguradora encontrada para teste');
                return false;
            }

            // Verificar se já existe relacionamento
            $relacionamentoExistente = CorretoraSeguradora::where('corretora_id', $corretora->id)
                ->where('seguradora_id', $seguradora->id)
                ->first();

            if ($relacionamentoExistente) {
                $this->warn('   Relacionamento já existe, testando remoção e criação');
                
                // Testar DELETE (auditoria de remoção)
                $relacionamentoExistente->delete();
                $logDelete = $this->verificarLog($relacionamentoExistente, 'deleted');
                
                if (!$logDelete) {
                    $this->error('   ✗ Auditoria de DELETE não funcionou');
                    return false;
                }
                
                $this->info('   ✓ DELETE auditado com sucesso');
            }

            // Testar CREATE (auditoria de criação)
            $novoRelacionamento = CorretoraSeguradora::create([
                'corretora_id' => $corretora->id,
                'seguradora_id' => $seguradora->id
            ]);

            $this->dadosOriginais['CorretoraSeguradora'][$novoRelacionamento->id] = [
                'corretora_id' => $corretora->id,
                'seguradora_id' => $seguradora->id
            ];

            $logCreate = $this->verificarLog($novoRelacionamento, 'created');
            
            if (!$logCreate) {
                $this->error('   ✗ Auditoria de CREATE não funcionou');
                return false;
            }

            $this->info('   ✓ CREATE auditado com sucesso');
            $this->info('   🎉 Relacionamento N:N sendo auditado corretamente!');
            
            return true;

        } catch (\Exception $e) {
            $this->error("   Erro: {$e->getMessage()}");
            return false;
        }
    }

    private function testarSeguradoraProduto()
    {
        try {
            $seguradora = Seguradora::first();
            $produto = Produto::first();
            
            if (!$seguradora || !$produto) {
                $this->warn('   Nenhuma seguradora ou produto encontrado para teste');
                return false;
            }

            // Verificar se já existe relacionamento
            $relacionamentoExistente = SeguradoraProduto::where('seguradora_id', $seguradora->id)
                ->where('produto_id', $produto->id)
                ->first();

            if ($relacionamentoExistente) {
                $this->warn('   Relacionamento já existe, testando remoção e criação');
                
                // Testar DELETE (auditoria de remoção)
                $relacionamentoExistente->delete();
                $logDelete = $this->verificarLog($relacionamentoExistente, 'deleted');
                
                if (!$logDelete) {
                    $this->error('   ✗ Auditoria de DELETE não funcionou');
                    return false;
                }
                
                $this->info('   ✓ DELETE auditado com sucesso');
            }

            // Testar CREATE (auditoria de criação)
            $novoRelacionamento = SeguradoraProduto::create([
                'seguradora_id' => $seguradora->id,
                'produto_id' => $produto->id
            ]);

            $this->dadosOriginais['SeguradoraProduto'][$novoRelacionamento->id] = [
                'seguradora_id' => $seguradora->id,
                'produto_id' => $produto->id
            ];

            $logCreate = $this->verificarLog($novoRelacionamento, 'created');
            
            if (!$logCreate) {
                $this->error('   ✗ Auditoria de CREATE não funcionou');
                return false;
            }

            $this->info('   ✓ CREATE auditado com sucesso');
            $this->info('   🎉 Relacionamento Seguradora-Produto sendo auditado corretamente!');
            
            return true;

        } catch (\Exception $e) {
            $this->error("   Erro: {$e->getMessage()}");
            return false;
        }
    }
}
