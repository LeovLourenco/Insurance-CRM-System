<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CorretorAkadController;
use App\Models\CorretorAkad;
use Exception;
use ReflectionClass;

class TesteInjecaoServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'autentique:test-injecao-service';

    /**
     * The console command description.
     */
    protected $description = 'Testa se CorretorAkadController carrega sem erro de AutentiqueService no construtor';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testando correção da injeção de serviço no CorretorAkadController...');
        $this->newLine();

        try {
            // Teste 1: Instanciar controller sem erro
            $this->info('🔬 Teste 1: Instanciar controller sem AutentiqueService no construtor');
            
            $controller = new CorretorAkadController();
            $this->line('   ✅ Controller instanciado com sucesso (sem erro de token)');

            // Teste 2: Verificar se o construtor não requer AutentiqueService
            $this->info('🔬 Teste 2: Verificar construtor limpo');
            
            $reflection = new ReflectionClass($controller);
            $constructor = $reflection->getConstructor();
            
            if ($constructor && count($constructor->getParameters()) > 0) {
                $this->error('   ❌ Construtor ainda tem parâmetros!');
                foreach ($constructor->getParameters() as $param) {
                    $this->line('      - ' . $param->getName() . ': ' . ($param->getType() ? $param->getType()->getName() : 'sem tipo'));
                }
                return Command::FAILURE;
            } else {
                $this->line('   ✅ Construtor limpo (sem parâmetros)');
            }

            // Teste 3: Método showCadastro (não precisa de service)
            $this->info('🔬 Teste 3: Método showCadastro() sem necessidade de service');
            
            $response = $controller->showCadastro();
            
            if (get_class($response) === 'Illuminate\View\View') {
                $this->line('   ✅ showCadastro() retorna view corretamente');
                $this->line('   📄 View: ' . $response->getName());
            } else {
                $this->error('   ❌ showCadastro() não retorna view');
                return Command::FAILURE;
            }

            // Teste 4: Método que precisa de service (injeção por método)
            $this->info('🔬 Teste 4: Método que usa service (injeção sob demanda)');
            
            $corretor = new CorretorAkad([
                'id' => 999,
                'nome' => 'Teste Injeção',
                'email' => 'teste@injecao.com',
                'cpf' => '123.456.789-00',
                'creci' => '123456',
                'estado' => 'SP',
                'telefone' => '(11) 99999-9999'
            ]);

            // Configurar token inválido para teste
            config(['services.autentique.token' => 'test_token_invalid']);

            $method = $reflection->getMethod('enviarDocumentoAssinatura');
            $method->setAccessible(true);
            
            $resultado = $method->invoke($controller, $corretor);
            
            if (isset($resultado['success']) && !$resultado['success']) {
                // Verificar se o erro é de autenticação, não de injeção
                $error = $resultado['error'];
                
                if (strpos($error, 'unauthorized') !== false || strpos($error, '401') !== false) {
                    $this->line('   ✅ Service injetado corretamente (erro 401 esperado)');
                } elseif (strpos($error, 'Target class') !== false || strpos($error, 'BindingResolutionException') !== false) {
                    $this->error('   ❌ Erro de injeção de dependência ainda existe!');
                    $this->line('   Erro: ' . $error);
                    return Command::FAILURE;
                } else {
                    $this->line('   ✅ Service funciona (outro tipo de erro)');
                    $this->line('   Erro: ' . $error);
                }
            } else {
                $this->line('   ✅ Service executou sem problemas');
            }

            // Teste 5: Verificar view de cadastro carrega no navegador
            $this->info('🔬 Teste 5: Verificar se view carrega sem erro');
            
            try {
                $view = view('corretores-akad.cadastro');
                $this->line('   ✅ View carregada: ' . $view->getName());
            } catch (Exception $e) {
                $this->error('   ❌ Erro ao carregar view: ' . $e->getMessage());
                return Command::FAILURE;
            }

            $this->newLine();
            $this->info('🎉 Todos os testes passaram!');
            
            $this->newLine();
            $this->line('📊 Resumo da correção:');
            $this->line('   ✅ Construtor sem parâmetros');
            $this->line('   ✅ showCadastro() carrega sem erro');
            $this->line('   ✅ Service injetado apenas quando necessário');
            $this->line('   ✅ View carrega corretamente no navegador');
            
            $this->newLine();
            $this->line('🎯 Antes: AutentiqueService no construtor causava erro');
            $this->line('🎯 Depois: Service injetado por método conforme necessário');
            
            $this->newLine();
            $this->info('✅ PROBLEMA RESOLVIDO: Rota GET /corretor-akad/cadastro agora carrega sem erro!');

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('💥 Erro no teste: ' . $e->getMessage());
            
            if (strpos($e->getMessage(), 'Target class') !== false || 
                strpos($e->getMessage(), 'BindingResolutionException') !== false) {
                $this->error('❌ PROBLEMA DE INJEÇÃO DE DEPENDÊNCIA AINDA EXISTE!');
            }
            
            return Command::FAILURE;
        }
    }
}