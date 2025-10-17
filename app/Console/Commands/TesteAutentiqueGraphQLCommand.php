<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutentiqueService;
use App\Models\CorretorAkad;
use Exception;
use ReflectionClass;
use ReflectionMethod;

class TesteAutentiqueGraphQLCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'autentique:test-graphql';

    /**
     * The console command description.
     */
    protected $description = 'Testa a construção do multipart GraphQL para Autentique (sem envio)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testando construção GraphQL multipart...');

        try {
            // Simular um corretor para teste
            $corretor = new CorretorAkad([
                'id' => 999,
                'nome' => 'João Silva Teste',
                'email' => 'joao.teste@exemplo.com',
                'cpf' => '123.456.789-00',
                'creci' => '654321',
                'estado' => 'SP',
                'telefone' => '(11) 98765-4321'
            ]);

            // Criar AutentiqueService sem token para teste
            config(['services.autentique.token' => 'test_token_fake']);
            $service = new AutentiqueService();

            // Usar reflexão para acessar método protegido
            $reflection = new ReflectionClass($service);
            $gerarHTMLMethod = $reflection->getMethod('gerarHTMLComDados');
            $gerarHTMLMethod->setAccessible(true);

            // Gerar HTML com os dados
            $this->line('📄 Gerando HTML com dados do corretor...');
            $htmlResult = $gerarHTMLMethod->invoke($service, $corretor, 'declaracao-akad-template.html');

            if (!$htmlResult['success']) {
                $this->error('❌ Erro ao gerar HTML: ' . $htmlResult['error']);
                return Command::FAILURE;
            }

            $this->line('✅ HTML gerado com sucesso: ' . $htmlResult['file_name']);

            // Testar construção do multipart
            $this->line('🔧 Testando construção do multipart...');
            
            $nomeDocumento = "Declaração de Atendimento AKAD - {$corretor->nome}";
            $signatarios = [
                [
                    'email' => $corretor->email,
                    'name' => $corretor->nome,
                    'action' => 'SIGN'
                ]
            ];

            $configs = [
                'message' => "Teste de construção multipart",
                'sandbox' => true // Forçar sandbox para teste
            ];

            // Acessar método privado buildMultipartBody
            $buildMethod = $reflection->getMethod('buildMultipartBody');
            $buildMethod->setAccessible(true);

            // Configurações do documento
            $defaultConfigs = [
                'name' => $nomeDocumento,
                'message' => "Por favor, assine o documento: {$nomeDocumento}",
                'reminder' => 'WEEKLY',
                'refusable' => true,
                'show_audit_page' => true,
                'ignore_cpf' => false,
                'ignore_birthdate' => true,
                'new_signature_style' => true,
                'scrolling_required' => true,
                'stop_on_rejected' => true,
                'configs' => [
                    'notification_finished' => true,
                    'notification_signed' => true,
                    'signature_appearance' => 'ELETRONIC'
                ]
            ];

            $documentConfig = array_merge($defaultConfigs, $configs);

            // Determinar modo sandbox (igual ao service)
            $sandboxMode = $configs['sandbox'] ?? (config('app.env') !== 'production');

            // Mutation GraphQL
            $mutation = <<<'GRAPHQL'
mutation CreateDocumentMutation(
    $document: DocumentInput!,
    $signers: [SignerInput!]!,
    $file: Upload!
) {
    createDocument(
        document: $document,
        signers: $signers,
        file: $file,
        sandbox: %s
    ) {
        id
        name
        refusable
        created_at
        signatures {
            public_id
            name
            email
            created_at
            action { name }
            link { short_link }
        }
    }
}
GRAPHQL;

            // Aplicar sandbox na mutation
            $mutation = sprintf($mutation, $sandboxMode ? 'true' : 'false');

            // Variáveis
            $variables = [
                'document' => $documentConfig,
                'signers' => $signatarios,
                'file' => null
            ];

            // Operations e Map
            $operations = json_encode([
                'query' => $mutation,
                'variables' => $variables
            ]);

            $map = json_encode([
                '0' => ['variables.file']
            ]);

            $boundary = '----WebKitFormBoundary' . uniqid();

            // Construir multipart
            $multipartBody = $buildMethod->invoke($service, $boundary, $operations, $map, $htmlResult['file_path']);

            $this->line('✅ Multipart construído com sucesso');
            $this->line('📏 Tamanho do body: ' . $this->formatBytes(strlen($multipartBody)));

            // Analisar estrutura do multipart
            $this->analisarMultipart($multipartBody, $boundary);

            // Verificar JSON válido
            $operationsDecoded = json_decode($operations, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->line('✅ JSON operations válido');
                $this->line('   📄 Mutation: ' . strlen($operationsDecoded['query']) . ' caracteres');
                $this->line('   📊 Variáveis: ' . count($operationsDecoded['variables']) . ' itens');
            } else {
                $this->error('❌ JSON operations inválido: ' . json_last_error_msg());
            }

            $mapDecoded = json_decode($map, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->line('✅ JSON map válido');
            } else {
                $this->error('❌ JSON map inválido: ' . json_last_error_msg());
            }

            // Limpar arquivo temporário
            $service->getTemplateProcessor()->deleteTempFile($htmlResult['file_path']);
            $this->line('🗑️ Arquivo temporário removido');

            $this->newLine();
            $this->info('🎉 Teste de construção GraphQL concluído com sucesso!');
            $this->line('📝 Endpoint correto: /v2/graphql');
            $this->line('🔧 Content-Type: multipart/form-data; boundary=' . substr($boundary, 0, 20) . '...');
            $this->line('📊 Estrutura: operations + map + arquivo');
            $this->line('🧪 Modo sandbox: ' . ($sandboxMode ? 'ATIVADO (teste)' : 'DESATIVADO (produção)'));
            $this->line('🌍 Ambiente atual: ' . config('app.env'));

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('💥 Erro no teste: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Analisar estrutura do multipart
     */
    protected function analisarMultipart($body, $boundary)
    {
        $this->line('🔍 Analisando estrutura do multipart...');

        $parts = explode('--' . $boundary, $body);
        $validParts = 0;

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part) || $part === '--') continue;

            $validParts++;

            if (strpos($part, 'name="operations"') !== false) {
                $this->line('   ✅ Campo "operations" encontrado');
            } elseif (strpos($part, 'name="map"') !== false) {
                $this->line('   ✅ Campo "map" encontrado');
            } elseif (strpos($part, 'name="0"') !== false) {
                $this->line('   ✅ Campo arquivo "0" encontrado');
                if (strpos($part, 'Content-Type: text/html; charset=utf-8') !== false) {
                    $this->line('   ✅ Content-Type HTML correto');
                }
            }
        }

        $this->line("   📊 Total de partes válidas: {$validParts}");

        if ($validParts >= 3) {
            $this->line('   ✅ Estrutura multipart correta');
        } else {
            $this->warn('   ⚠️ Estrutura multipart pode estar incompleta');
        }
    }

    /**
     * Formatar bytes
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}