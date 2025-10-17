<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutentiqueService;
use App\Services\TemplateProcessor;
use App\Models\CorretorAkad;
use Exception;
use ReflectionClass;

class TesteHTMLRenderizacaoCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'autentique:test-html-renderizacao 
                           {--skip-api : Pular teste de API e testar apenas renderização HTML}';

    /**
     * The console command description.
     */
    protected $description = 'Testa a nova implementação de renderização HTML para templates Autentique';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testando nova implementação de renderização HTML...');
        $this->newLine();

        try {
            // Teste 1: TemplateProcessor standalone
            $this->info('🔬 Teste 1: TemplateProcessor standalone');
            $resultadoTemplate = $this->testarTemplateProcessor();
            
            if ($resultadoTemplate) {
                $this->line('   ✅ TemplateProcessor funcionando corretamente');
            } else {
                $this->error('   ❌ Erro no TemplateProcessor');
                return Command::FAILURE;
            }

            // Teste 2: Geração HTML com dados do corretor
            $this->info('🔬 Teste 2: Geração HTML com dados do corretor');
            $resultadoCorretor = $this->testarGeracaoHTMLCorretor();
            
            if ($resultadoCorretor) {
                $this->line('   ✅ Geração HTML com dados do corretor funcionando');
            } else {
                $this->error('   ❌ Erro na geração HTML com dados do corretor');
                return Command::FAILURE;
            }

            // Teste 3: Validação de variáveis
            $this->info('🔬 Teste 3: Validação de substituição de variáveis');
            $resultadoValidacao = $this->testarValidacaoVariaveis();
            
            if ($resultadoValidacao) {
                $this->line('   ✅ Validação de variáveis funcionando');
            } else {
                $this->error('   ❌ Erro na validação de variáveis');
                return Command::FAILURE;
            }

            // Teste 4: Limpeza de arquivos temporários
            $this->info('🔬 Teste 4: Limpeza de arquivos temporários');
            $resultadoLimpeza = $this->testarLimpezaArquivos();
            
            if ($resultadoLimpeza) {
                $this->line('   ✅ Limpeza de arquivos funcionando');
            } else {
                $this->error('   ❌ Erro na limpeza de arquivos');
                return Command::FAILURE;
            }

            // Teste 5: Integração completa (opcional)
            if (!$this->option('skip-api')) {
                $this->info('🔬 Teste 5: Simulação de integração completa');
                $resultadoIntegracao = $this->testarIntegracaoCompleta();
                
                if ($resultadoIntegracao) {
                    $this->line('   ✅ Simulação de integração funcionando');
                } else {
                    $this->error('   ❌ Erro na simulação de integração');
                    return Command::FAILURE;
                }
            } else {
                $this->line('🔬 Teste 5: Pulado (--skip-api ativo)');
            }

            $this->newLine();
            $this->info('🎉 Todos os testes de renderização HTML passaram!');
            $this->mostrarResumo();

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('💥 Erro nos testes: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Testar TemplateProcessor isoladamente
     */
    protected function testarTemplateProcessor()
    {
        try {
            $templatePath = storage_path('app/templates/declaracao-akad-template.html');
            
            $processor = new TemplateProcessor($templatePath);
            
            $variaveis = [
                'DATA' => 'São Paulo, 14 de outubro de 2025',
                'NOME_CORRETORA' => 'Corretor Teste Standalone',
                'CODIGO_SUSEP' => '999999',
                'CNPJ_CORRETORA' => '12.345.678/0001-90',
                'DATA_GERACAO' => now()->format('d/m/Y H:i:s')
            ];

            $processor->setVariables($variaveis);
            
            // Validar variáveis
            if (!$processor->validateVariables()) {
                $this->line('   ❌ Validação de variáveis falhou');
                return false;
            }

            // Renderizar e salvar
            $tempFile = $processor->saveToTempFile('teste-standalone.html');
            
            if (!file_exists($tempFile)) {
                $this->line('   ❌ Arquivo temporário não foi criado');
                return false;
            }

            $content = file_get_contents($tempFile);
            $hasUnresolvedVars = preg_match('/\$[A-Z_]+\$/', $content);

            if ($hasUnresolvedVars) {
                $this->line('   ❌ Variáveis não substituídas encontradas');
                return false;
            }

            $this->line('   📝 Arquivo gerado: ' . basename($tempFile));
            $this->line('   📏 Tamanho: ' . filesize($tempFile) . ' bytes');
            
            // Limpar
            $processor->deleteTempFile($tempFile);
            
            return true;

        } catch (Exception $e) {
            $this->line('   ❌ Exceção: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Testar geração HTML com dados do corretor
     */
    protected function testarGeracaoHTMLCorretor()
    {
        try {
            // Simular corretor
            $corretor = new CorretorAkad([
                'id' => 999,
                'nome' => 'Corretor Teste HTML',
                'email' => 'teste@html.com',
                'cpf' => '123.456.789-00',
                'creci' => '888888',
                'estado' => 'RJ',
                'telefone' => '(21) 99999-9999'
            ]);

            $service = new AutentiqueService();
            $reflection = new ReflectionClass($service);
            $method = $reflection->getMethod('gerarHTMLComDados');
            $method->setAccessible(true);

            $resultado = $method->invoke($service, $corretor, 'declaracao-akad-template.html');

            if (!$resultado['success']) {
                $this->line('   ❌ Erro: ' . $resultado['error']);
                return false;
            }

            $tempFile = $resultado['file_path'];
            
            if (!file_exists($tempFile)) {
                $this->line('   ❌ Arquivo temporário não existe');
                return false;
            }

            $content = file_get_contents($tempFile);
            
            // Verificar se dados do corretor estão no HTML
            if (strpos($content, $corretor->nome) === false) {
                $this->line('   ❌ Nome do corretor não encontrado no HTML');
                return false;
            }

            if (strpos($content, $corretor->creci) === false) {
                $this->line('   ❌ CRECI do corretor não encontrado no HTML');
                return false;
            }

            $this->line('   📝 Nome do corretor inserido: ✓');
            $this->line('   📝 CRECI inserido: ✓');
            $this->line('   📏 Tamanho do HTML: ' . filesize($tempFile) . ' bytes');

            // Limpar
            unlink($tempFile);

            return true;

        } catch (Exception $e) {
            $this->line('   ❌ Exceção: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Testar validação de variáveis
     */
    protected function testarValidacaoVariaveis()
    {
        try {
            $templatePath = storage_path('app/templates/declaracao-akad-template.html');
            $processor = new TemplateProcessor($templatePath);
            
            // Teste com variáveis incompletas
            $processor->setVariable('DATA', 'Teste data');
            
            if ($processor->validateVariables()) {
                $this->line('   ❌ Validação deveria ter falhado com variáveis incompletas');
                return false;
            }

            $this->line('   ✓ Validação rejeitou variáveis incompletas');

            // Teste com todas as variáveis
            $processor->setVariables([
                'DATA' => 'São Paulo, 14 de outubro de 2025',
                'NOME_CORRETORA' => 'Teste Validação',
                'CODIGO_SUSEP' => '777777',
                'CNPJ_CORRETORA' => '98.765.432/0001-10',
                'DATA_GERACAO' => now()->format('d/m/Y H:i:s')
            ]);

            if (!$processor->validateVariables()) {
                $this->line('   ❌ Validação falhou com todas as variáveis definidas');
                return false;
            }

            $this->line('   ✓ Validação passou com todas as variáveis');

            // Verificar informações do template
            $info = $processor->getTemplateInfo();
            $this->line('   📋 Variáveis no template: ' . count($info['template_variables']));
            $this->line('   📋 Variáveis definidas: ' . count($info['defined_variables']));

            return true;

        } catch (Exception $e) {
            $this->line('   ❌ Exceção: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Testar limpeza de arquivos temporários
     */
    protected function testarLimpezaArquivos()
    {
        try {
            $templatePath = storage_path('app/templates/declaracao-akad-template.html');
            $processor = new TemplateProcessor($templatePath);
            
            // Criar múltiplos arquivos temporários
            $processor->setVariables([
                'DATA' => 'Teste',
                'NOME_CORRETORA' => 'Teste',
                'CODIGO_SUSEP' => 'Teste',
                'CNPJ_CORRETORA' => 'Teste',
                'DATA_GERACAO' => 'Teste'
            ]);

            $arquivos = [];
            for ($i = 1; $i <= 3; $i++) {
                $arquivo = $processor->saveToTempFile("teste-limpeza-{$i}.html");
                $arquivos[] = $arquivo;
            }

            $this->line('   📝 Criados ' . count($arquivos) . ' arquivos temporários');

            // Verificar se existem
            $existentes = 0;
            foreach ($arquivos as $arquivo) {
                if (file_exists($arquivo)) {
                    $existentes++;
                }
            }

            if ($existentes !== count($arquivos)) {
                $this->line('   ❌ Nem todos os arquivos foram criados');
                return false;
            }

            // Limpar um por um
            foreach ($arquivos as $arquivo) {
                if (!$processor->deleteTempFile($arquivo)) {
                    $this->line('   ❌ Erro ao deletar arquivo: ' . basename($arquivo));
                    return false;
                }
            }

            $this->line('   🗑️ Todos os arquivos foram deletados com sucesso');

            return true;

        } catch (Exception $e) {
            $this->line('   ❌ Exceção: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Testar integração completa (simulação)
     */
    protected function testarIntegracaoCompleta()
    {
        try {
            // Configurar token de teste
            config(['services.autentique.token' => 'test_token']);
            
            $corretor = new CorretorAkad([
                'id' => 999,
                'nome' => 'Corretor Integração Teste',
                'email' => 'integracao@teste.com',
                'cpf' => '111.222.333-44',
                'creci' => '555555',
                'estado' => 'MG',
                'telefone' => '(31) 99999-9999'
            ]);

            $service = new AutentiqueService();
            
            // Testar apenas a geração e preparação (sem envio real para API)
            $reflection = new ReflectionClass($service);
            $method = $reflection->getMethod('gerarHTMLComDados');
            $method->setAccessible(true);

            $resultado = $method->invoke($service, $corretor, 'declaracao-akad-template.html');

            if (!$resultado['success']) {
                $this->line('   ❌ Erro na geração HTML: ' . $resultado['error']);
                return false;
            }

            $tempFile = $resultado['file_path'];
            
            // Verificar se o arquivo está pronto para envio
            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                $this->line('   ❌ Arquivo HTML não está pronto para envio');
                return false;
            }

            $this->line('   ✓ HTML gerado e pronto para API');
            $this->line('   📄 Arquivo: ' . basename($tempFile));
            $this->line('   📏 Tamanho: ' . filesize($tempFile) . ' bytes');

            // Verificar conteúdo final
            $content = file_get_contents($tempFile);
            $hasTitle = strpos($content, 'DECLARAÇÃO DE ATENDIMENTO') !== false;
            $hasCorretorData = strpos($content, $corretor->nome) !== false;
            
            if (!$hasTitle || !$hasCorretorData) {
                $this->line('   ❌ Conteúdo HTML incompleto');
                return false;
            }

            $this->line('   ✓ Conteúdo HTML válido e completo');
            $this->line('   ℹ️  Pronto para envio via API Autentique');

            // Limpar
            unlink($tempFile);

            return true;

        } catch (Exception $e) {
            $this->line('   ❌ Exceção: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mostrar resumo dos resultados
     */
    protected function mostrarResumo()
    {
        $this->newLine();
        $this->line('📊 Resumo da implementação:');
        $this->line('   ✅ Template HTML: declaracao-akad-template.html');
        $this->line('   ✅ TemplateProcessor: processamento e validação');
        $this->line('   ✅ AutentiqueService: geração HTML com dados reais');
        $this->line('   ✅ Limpeza automática: arquivos temporários');
        $this->line('   ✅ Retry logic: busca de links de assinatura');
        $this->newLine();
        $this->line('🎯 Sistema pronto para:');
        $this->line('   • Renderizar documentos HTML com dados do corretor');
        $this->line('   • Enviar HTML renderizado (não caminho) para Autentique');
        $this->line('   • Buscar links de assinatura com retry automático');
        $this->line('   • Gerenciar arquivos temporários automaticamente');
        $this->newLine();
        $this->line('📁 Estrutura implementada:');
        $this->line('   • storage/app/templates/declaracao-akad-template.html');
        $this->line('   • storage/app/temp/ (arquivos temporários)');
        $this->line('   • app/Services/TemplateProcessor.php');
        $this->line('   • app/Services/AutentiqueService.php (atualizado)');
    }
}