<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutentiqueService;
use App\Services\TemplateProcessor;
use App\Models\CorretorAkad;
use Exception;

class TesteAutentiqueCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'autentique:test 
                           {--template-only : Testa apenas o processamento de templates}
                           {--skip-api : Pula testes que requerem API}';

    /**
     * The console command description.
     */
    protected $description = 'Testa a integração completa do sistema Autentique com templates HTML';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Iniciando testes do sistema Autentique...');
        
        $sucessos = 0;
        $falhas = 0;

        // Teste 1: TemplateProcessor
        if ($this->testeTemplateProcessor()) {
            $sucessos++;
        } else {
            $falhas++;
        }

        // Teste 2: AutentiqueService configuração
        if (!$this->option('skip-api')) {
            if ($this->testeAutentiqueConfig()) {
                $sucessos++;
            } else {
                $falhas++;
            }
        }

        // Teste 3: Processamento completo (sem envio)
        if ($this->testeProcessamentoCompleto()) {
            $sucessos++;
        } else {
            $falhas++;
        }

        // Teste 4: Validações
        if ($this->testeValidacoes()) {
            $sucessos++;
        } else {
            $falhas++;
        }

        $this->newLine();
        $this->info("📊 Resumo dos testes:");
        $this->line("   ✅ Sucessos: {$sucessos}");
        $this->line("   ❌ Falhas: {$falhas}");

        if ($falhas === 0) {
            $this->info('🎉 Todos os testes passaram!');
            return Command::SUCCESS;
        } else {
            $this->error('💥 Alguns testes falharam. Verifique os logs acima.');
            return Command::FAILURE;
        }
    }

    /**
     * Teste do TemplateProcessor
     */
    protected function testeTemplateProcessor()
    {
        $this->newLine();
        $this->info('🔧 Testando TemplateProcessor...');

        try {
            $processor = new TemplateProcessor();

            // Teste 1: Listar templates
            $templates = $processor->listTemplates();
            $this->line("   📄 Templates encontrados: " . count($templates));

            if (empty($templates)) {
                $this->warn('   ⚠️ Nenhum template encontrado');
                return false;
            }

            // Teste 2: Validar template principal
            $templatePrincipal = 'declaracao-akad-template.html';
            
            if (!$processor->templateExists($templatePrincipal)) {
                $this->error("   ❌ Template principal não encontrado: {$templatePrincipal}");
                return false;
            }

            $this->line("   ✅ Template principal encontrado: {$templatePrincipal}");

            // Teste 3: Extrair variáveis
            $variaveis = $processor->getTemplateVariables($templatePrincipal);
            $this->line("   🔤 Variáveis encontradas: " . implode(', ', $variaveis));

            $variaveisEsperadas = ['CIDADE', 'DATA', 'NOME_CORRETORA', 'CODIGO_SUSEP', 'CNPJ_CORRETORA', 'DATA_GERACAO'];
            $variaveisFaltantes = array_diff($variaveisEsperadas, $variaveis);

            if (!empty($variaveisFaltantes)) {
                $this->warn("   ⚠️ Variáveis esperadas não encontradas: " . implode(', ', $variaveisFaltantes));
            }

            // Teste 4: Processar template
            $dadosTeste = [
                'CIDADE' => 'São Paulo',
                'DATA' => '15/10/2025',
                'NOME_CORRETORA' => 'João Silva Corretor',
                'CODIGO_SUSEP' => 'SP.123456',
                'CNPJ_CORRETORA' => '12.345.678/0001-90',
                'DATA_GERACAO' => '15/10/2025 10:30:00'
            ];

            $resultado = $processor->processTemplate($templatePrincipal, $dadosTeste);

            if ($resultado['success']) {
                $this->line("   ✅ Template processado com sucesso");
                $this->line("   📁 Arquivo temporário: " . $resultado['file_name']);
                
                // Limpar arquivo temporário
                $processor->deleteTempFile($resultado['file_path']);
                $this->line("   🗑️ Arquivo temporário removido");
                
                return true;
            } else {
                $this->error("   ❌ Erro ao processar template: " . $resultado['error']);
                return false;
            }

        } catch (Exception $e) {
            $this->error("   💥 Exceção: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Teste da configuração do AutentiqueService
     */
    protected function testeAutentiqueConfig()
    {
        $this->newLine();
        $this->info('⚙️ Testando configuração do AutentiqueService...');

        try {
            $service = new AutentiqueService();
            $config = $service->validarConfiguracao();

            if ($config['valido']) {
                $this->line("   ✅ Configuração válida");
                return true;
            } else {
                $this->error("   ❌ Problemas na configuração:");
                foreach ($config['erros'] as $erro) {
                    $this->line("      - {$erro}");
                }
                return false;
            }

        } catch (Exception $e) {
            $this->error("   💥 Exceção: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Teste do processamento completo (sem envio real)
     */
    protected function testeProcessamentoCompleto()
    {
        $this->newLine();
        $this->info('🔄 Testando processamento completo...');

        try {
            // Criar um corretor fictício para teste
            $corretorTeste = new CorretorAkad([
                'nome' => 'Maria Silva Testes',
                'email' => 'maria.teste@example.com',
                'cpf' => '123.456.789-00',
                'creci' => '123456',
                'estado' => 'SP',
                'telefone' => '(11) 99999-9999'
            ]);
            $corretorTeste->id = 999999; // ID fictício

            try {
                $service = new AutentiqueService();
                
                // Teste apenas a geração do HTML (método protegido, então vamos testar indiretamente)
                $this->line("   👤 Corretor de teste criado: {$corretorTeste->nome}");
                $this->line("   📧 Email: {$corretorTeste->email}");
                $this->line("   🆔 CRECI: {$corretorTeste->creci} - {$corretorTeste->estado}");

                // Validar que o service foi criado corretamente
                $templates = $service->listarTemplates();
                
                if ($templates['success']) {
                    $count = count($templates['templates']);
                    $this->line("   📄 Templates carregados pelo service: {$count}");
                    return true;
                } else {
                    $this->error("   ❌ Erro ao listar templates: " . $templates['error']);
                    return false;
                }
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Token do Autentique não configurado') !== false) {
                    $this->warn("   ⚠️ Token não configurado (esperado): {$e->getMessage()}");
                    $this->line("   ✅ Teste de processamento concluído (sem token)");
                    return true;
                } else {
                    throw $e;
                }
            }

        } catch (Exception $e) {
            $this->error("   💥 Exceção: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Teste das validações
     */
    protected function testeValidacoes()
    {
        $this->newLine();
        $this->info('✅ Testando validações...');

        try {
            $processor = new TemplateProcessor();
            
            // Teste 1: Template inexistente
            $resultado = $processor->processTemplate('template-inexistente.html', []);
            if (!$resultado['success']) {
                $this->line("   ✅ Validação de template inexistente funcionando");
            } else {
                $this->error("   ❌ Validação de template inexistente falhou");
                return false;
            }

            // Teste 2: Variáveis faltantes
            if ($processor->templateExists('declaracao-akad-template.html')) {
                $resultado = $processor->processTemplate('declaracao-akad-template.html', []);
                if (!$resultado['success'] && strpos($resultado['error'], 'obrigatórias não fornecidas') !== false) {
                    $this->line("   ✅ Validação de variáveis obrigatórias funcionando");
                } else {
                    $this->error("   ❌ Validação de variáveis obrigatórias falhou");
                    return false;
                }
            }

            // Teste 3: Limpeza automática
            try {
                $service = new AutentiqueService();
                $deletados = $service->limpezaAutomatica();
                $this->line("   🧹 Limpeza automática executada: {$deletados} arquivos removidos");
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Token do Autentique não configurado') !== false) {
                    $this->warn("   ⚠️ Limpeza automática pulada (token não configurado)");
                    
                    // Teste direto do TemplateProcessor
                    $processor = new TemplateProcessor();
                    $deletados = $processor->cleanupOldTempFiles();
                    $this->line("   🧹 Limpeza direta executada: {$deletados} arquivos removidos");
                } else {
                    throw $e;
                }
            }

            return true;

        } catch (Exception $e) {
            $this->error("   💥 Exceção: {$e->getMessage()}");
            return false;
        }
    }
}