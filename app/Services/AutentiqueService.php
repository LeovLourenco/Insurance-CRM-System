<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class AutentiqueService
{
    protected $apiUrl;
    protected $token;
    protected $timeout;
    protected $templateProcessor;

    public function __construct()
    {
        $this->apiUrl = config('services.autentique.api_url', 'https://api.autentique.com.br/v2/graphql');
        $this->token = config('services.autentique.token');
        $this->timeout = config('services.autentique.timeout', 30);

        if (!$this->token) {
            throw new Exception('Token do Autentique não configurado');
        }
    }

    /**
     * Criar documento para assinatura usando GraphQL multipart
     */
    public function criarDocumento($nomeDocumento, $htmlFilePath, $signatarios, $configs = [])
    {
        Log::info('🔬 INÍCIO criarDocumento()', [
            'nome_documento' => $nomeDocumento,
            'html_file_path_recebido' => $htmlFilePath,
            'file_exists' => file_exists($htmlFilePath),
            'realpath' => realpath($htmlFilePath) ?: 'FALSO',
            'is_readable' => is_readable($htmlFilePath),
            'filesize' => file_exists($htmlFilePath) ? filesize($htmlFilePath) : 'N/A',
            'cwd' => getcwd()
        ]);
        
        // Verificação crítica ANTES de qualquer processamento
        if (!file_exists($htmlFilePath)) {
            $tempDir = storage_path('app/temp');
            $arquivosTemp = is_dir($tempDir) ? scandir($tempDir) : ['diretório não existe'];
            
            Log::error('❌ ARQUIVO NÃO EXISTE dentro de criarDocumento()', [
                'path_recebido' => $htmlFilePath,
                'cwd' => getcwd(),
                'temp_dir' => $tempDir,
                'arquivos_em_temp' => $arquivosTemp,
                'dirname_do_path' => dirname($htmlFilePath),
                'basename_do_path' => basename($htmlFilePath)
            ]);
            
            return [
                'success' => false,
                'error' => "Arquivo não encontrado no início de criarDocumento(): {$htmlFilePath}"
            ];
        }
        
        Log::info('✅ Arquivo confirmado no início de criarDocumento()', [
            'path' => $htmlFilePath,
            'size' => filesize($htmlFilePath)
        ]);
        
        try {
            // Configurações padrão baseadas na documentação oficial
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

            // Determinar modo sandbox ANTES de criar as variáveis
            $sandboxMode = $configs['sandbox'] ?? (config('app.env') !== 'production');
            
            // CRÍTICO: Remover 'sandbox' do $documentConfig 
            // pois sandbox é parâmetro da mutation, não do DocumentInput
            unset($documentConfig['sandbox']);

            // 1. MUTATION GRAPHQL
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

            // 2. VARIÁVEIS (agora $documentConfig já está limpo)
            $variables = [
                'document' => $documentConfig,
                'signers' => $signatarios,
                'file' => null
            ];

            // 3. OPERATIONS (JSON)
            $operations = json_encode([
                'query' => $mutation,
                'variables' => $variables
            ]);

            // 4. MAP
            $map = json_encode([
                '0' => ['variables.file']
            ]);

            // 5. MULTIPART BODY
            $boundary = '----WebKitFormBoundary' . uniqid();
            $body = $this->buildMultipartBody($boundary, $operations, $map, $htmlFilePath);

            // 6. cURL (NÃO usar Http::asMultipart() - não funciona com GraphQL)
            $response = $this->sendCurlRequest($body, $boundary);
            $data = $this->processResponse($response);

            Log::info('Documento criado via GraphQL com sucesso', [
                'document_id' => $data['id'] ?? 'N/A',
                'name' => $nomeDocumento,
                'sandbox' => $sandboxMode ? 'SIM' : 'NÃO'
            ]);

            return [
                'success' => true,
                'data' => $data
            ];

        } catch (Exception $e) {
            Log::error('Erro ao criar documento via GraphQL', [
                'error' => $e->getMessage(),
                'nome_documento' => $nomeDocumento
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Construir body multipart para GraphQL
     */
    private function buildMultipartBody($boundary, $operations, $map, $filePath)
    {
        $delimiter = "\r\n";
        $body = '';

        // Campo 'operations'
        $body .= '--' . $boundary . $delimiter;
        $body .= 'Content-Disposition: form-data; name="operations"' . $delimiter . $delimiter;
        $body .= $operations . $delimiter;

        // Campo 'map'
        $body .= '--' . $boundary . $delimiter;
        $body .= 'Content-Disposition: form-data; name="map"' . $delimiter . $delimiter;
        $body .= $map . $delimiter;

        // Arquivo HTML
        Log::info('🔬 buildMultipartBody() processando arquivo', [
            'filePath_recebido' => $filePath,
            'file_exists_direto' => file_exists($filePath),
            'is_absolute_path' => str_starts_with($filePath, '/'),
            'storage_app_path' => storage_path('app/'),
        ]);
        
        if (is_string($filePath) && !file_exists($filePath)) {
            // ERRO: Arquivo não existe - vamos tentar diferentes caminhos
            $tentativas = [
                $filePath, // Caminho original
                storage_path('app/' . $filePath), // Caminho relativo a storage/app
                storage_path($filePath), // Caminho relativo a storage
                basename($filePath) // Apenas nome do arquivo
            ];
            
            Log::error('❌ Arquivo não encontrado - tentando caminhos alternativos', [
                'tentativas' => $tentativas
            ]);
            
            foreach ($tentativas as $tentativa) {
                if (file_exists($tentativa)) {
                    Log::info('✅ Arquivo encontrado em caminho alternativo', [
                        'caminho_encontrado' => $tentativa
                    ]);
                    $filePath = $tentativa;
                    break;
                }
            }
            
            // Se ainda não existe, tratar como conteúdo HTML direto
            if (!file_exists($filePath)) {
                Log::warning('⚠️ Tratando como conteúdo HTML direto', [
                    'content_preview' => substr($filePath, 0, 100)
                ]);
                $fileContent = $filePath;
                $filename = 'document.html';
            } else {
                $fileContent = file_get_contents($filePath);
                $filename = basename($filePath);
            }
        } else {
            // Arquivo existe - lê diretamente
            Log::info('✅ Arquivo existe - lendo conteúdo', [
                'path' => $filePath,
                'size' => filesize($filePath)
            ]);
            
            try {
                $fileContent = file_get_contents($filePath);
                if ($fileContent === false) {
                    throw new Exception("Falha ao ler conteúdo do arquivo: {$filePath}");
                }
                $filename = basename($filePath);
                
                Log::info('✅ Arquivo lido com sucesso', [
                    'filename' => $filename,
                    'content_size' => strlen($fileContent)
                ]);
            } catch (Exception $e) {
                Log::error('❌ Erro ao ler arquivo', [
                    'path' => $filePath,
                    'error' => $e->getMessage()
                ]);
                throw new Exception("Erro ao ler arquivo {$filePath}: " . $e->getMessage());
            }
        }
        
        $body .= '--' . $boundary . $delimiter;
        $body .= 'Content-Disposition: form-data; name="0"; filename="' . $filename . '"' . $delimiter;
        $body .= 'Content-Type: text/html; charset=utf-8' . $delimiter . $delimiter;
        $body .= $fileContent . $delimiter;

        // Finaliza
        $body .= '--' . $boundary . '--' . $delimiter;

        return $body;
    }

    /**
     * Enviar requisição cURL
     */
    private function sendCurlRequest($body, $boundary)
    {
        $ch = curl_init($this->apiUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: multipart/form-data; boundary=' . $boundary
            ],
            CURLOPT_TIMEOUT => $this->timeout
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);

        if ($error) {
            throw new Exception('Erro cURL: ' . $error);
        }

        if ($httpCode !== 200) {
            Log::error('Erro HTTP na requisição GraphQL', [
                'http_code' => $httpCode,
                'response' => $response
            ]);
            throw new Exception('Erro HTTP ' . $httpCode . ': ' . $response);
        }

        return $response;
    }

    /**
     * Processar resposta da API
     */
    private function processResponse($response)
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Erro ao decodificar JSON: ' . json_last_error_msg());
        }

        if (isset($data['errors'])) {
            $errorMsg = $data['errors'][0]['message'] ?? 'Erro desconhecido';
            Log::error('Erro GraphQL retornado pela API', [
                'errors' => $data['errors']
            ]);
            throw new Exception('Erro da API Autentique: ' . $errorMsg);
        }

        if (!isset($data['data']['createDocument'])) {
            Log::error('Resposta inválida da API', [
                'response' => $data
            ]);
            throw new Exception('Resposta inválida da API - createDocument não encontrado');
        }

        return $data['data']['createDocument'];
    }

    /**
     * Buscar documento por ID
     */
    public function buscarDocumento($documentoId)
    {
        try {
            $query = '
                query GetDocument($id: ID!) {
                    document(id: $id) {
                        id
                        name
                        status
                        public_id
                        created_at
                        signed_at
                        signatures {
                            public_id
                            name
                            email
                            created_at
                            signed_at
                            status
                            reject_reason
                            link {
                                short_link
                            }
                        }
                    }
                }
            ';

            $variables = ['id' => $documentoId];

            $response = $this->makeRequest($query, $variables);

            if (isset($response['data']['document'])) {
                return [
                    'success' => true,
                    'data' => $response['data']['document']
                ];
            }

            return [
                'success' => false,
                'error' => 'Documento não encontrado',
                'details' => $response
            ];

        } catch (Exception $e) {
            Log::error('Erro ao buscar documento no Autentique', [
                'error' => $e->getMessage(),
                'document_id' => $documentoId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Listar documentos
     */
    public function listarDocumentos($limite = 20, $cursor = null)
    {
        try {
            $query = '
                query ListDocuments($limit: Int, $cursor: String) {
                    documents(limit: $limit, cursor: $cursor) {
                        data {
                            id
                            name
                            status
                            public_id
                            created_at
                            signed_at
                        }
                        pagination {
                            cursor
                            has_next_page
                        }
                    }
                }
            ';

            $variables = [
                'limit' => $limite,
                'cursor' => $cursor
            ];

            $response = $this->makeRequest($query, $variables);

            if (isset($response['data']['documents'])) {
                return [
                    'success' => true,
                    'data' => $response['data']['documents']
                ];
            }

            return [
                'success' => false,
                'error' => 'Erro ao listar documentos',
                'details' => $response
            ];

        } catch (Exception $e) {
            Log::error('Erro ao listar documentos no Autentique', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancelar documento
     */
    public function cancelarDocumento($documentoId)
    {
        try {
            $mutation = '
                mutation DeleteDocument($id: ID!) {
                    deleteDocument(id: $id)
                }
            ';

            $variables = ['id' => $documentoId];

            $response = $this->makeRequest($mutation, $variables);

            if (isset($response['data']['deleteDocument']) && $response['data']['deleteDocument']) {
                Log::info('Documento cancelado com sucesso no Autentique', [
                    'document_id' => $documentoId
                ]);

                return [
                    'success' => true,
                    'data' => ['cancelled' => true]
                ];
            }

            return [
                'success' => false,
                'error' => 'Erro ao cancelar documento',
                'details' => $response
            ];

        } catch (Exception $e) {
            Log::error('Erro ao cancelar documento no Autentique', [
                'error' => $e->getMessage(),
                'document_id' => $documentoId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Criar documento específico para corretor AKAD usando template HTML
     */
    public function criarDocumentoCorretor($corretor, $templateName = null)
    {
        $tempFilePath = null;
        
        try {
            // Usar template padrão se não especificado
            $templateName = $templateName ?: 'declaracao-akad-template.html';
            
            // Gerar HTML com dados do corretor
            $htmlResult = $this->gerarHTMLComDados($corretor, $templateName);
            
            if (!$htmlResult['success']) {
                return $htmlResult;
            }
            
            $tempFilePath = $htmlResult['file_path'];
            
            // VERIFICAR SE ARQUIVO EXISTE ANTES DE CONTINUAR
            if (!file_exists($tempFilePath)) {
                Log::error('Arquivo temporário não existe após gerarHTMLComDados', [
                    'expected_path' => $tempFilePath,
                    'corretor_id' => $corretor->id
                ]);
                return [
                    'success' => false,
                    'error' => 'Falha ao gerar arquivo HTML temporário'
                ];
            }
            
            Log::info('Arquivo temporário verificado e existe', [
                'path' => $tempFilePath,
                'size' => filesize($tempFilePath)
            ]);

            $signatarios = [
                [
                    'email' => $corretor->email,
                    'name' => $corretor->nome,
                    'action' => 'SIGN'
                ]
            ];

            $nomeDocumento = "Declaração de Atendimento AKAD - {$corretor->nome}";

            // Configurações específicas para corretor AKAD
            $configs = [
                'message' => "Olá {$corretor->nome}, por favor assine sua Declaração de Atendimento AKAD para finalizar seu cadastro como corretor parceiro.",
                'sandbox' => config('app.env') !== 'production' // Sandbox em dev/staging
            ];

            // Passar CAMINHO do arquivo, não conteúdo
            $resultado = $this->criarDocumento($nomeDocumento, $tempFilePath, $signatarios, $configs);

            // DELETAR ARQUIVO APÓS TENTATIVA (sucesso ou falha)
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
                Log::info('Arquivo temporário deletado', [
                    'file' => $tempFilePath,
                    'resultado' => $resultado['success'] ? 'sucesso' : 'falha'
                ]);
            }

            if ($resultado['success']) {
                $documento = $resultado['data'];
                
                // SUCESSO: Documento criado
                // Autentique envia email automaticamente
                return [
                    'success' => true,
                    'documento_id' => $documento['id'],
                    'message' => 'Documento criado. Email enviado automaticamente pelo Autentique'
                ];
            }

            return $resultado;

        } catch (Exception $e) {
            Log::error('Erro ao criar documento para corretor AKAD', [
                'error' => $e->getMessage(),
                'corretor_id' => $corretor->id,
                'template' => $templateName ?? 'N/A'
            ]);

            // Deletar arquivo temporário em caso de exceção
            if ($tempFilePath && file_exists($tempFilePath)) {
                unlink($tempFilePath);
                Log::info('Arquivo temporário deletado após exceção', [
                    'file' => $tempFilePath,
                    'error' => $e->getMessage()
                ]);
            }

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Criar documento de teste (sempre sandbox)
     */
    public function criarDocumentoTeste($nomeDocumento, $htmlFilePath, $signatarios, $configs = [])
    {
        $configs['sandbox'] = true; // Força sandbox
        return $this->criarDocumento($nomeDocumento, $htmlFilePath, $signatarios, $configs);
    }

    /**
     * Verificar status de documento via webhook ou polling
     */
    public function verificarStatusDocumento($documentoId)
    {
        $cacheKey = "autentique_doc_status_{$documentoId}";
        
        // Verifica cache primeiro (evita muitas chamadas à API)
        if (Cache::has($cacheKey)) {
            $dados = Cache::get($cacheKey);
            if ($dados['updated_at'] > now()->subMinutes(5)) {
                return $dados;
            }
        }

        $resultado = $this->buscarDocumento($documentoId);

        if ($resultado['success']) {
            $documento = $resultado['data'];
            $status = [
                'success' => true,
                'status' => $documento['status'],
                'assinado_em' => $documento['signed_at'],
                'signatures' => $documento['signatures'],
                'updated_at' => now()
            ];

            // Cache por 5 minutos
            Cache::put($cacheKey, $status, now()->addMinutes(5));

            return $status;
        }

        return $resultado;
    }

    /**
     * Processar webhook do Autentique
     */
    public function processarWebhook($dadosWebhook)
    {
        try {
            Log::info('Webhook recebido do Autentique', $dadosWebhook);

            $documentoId = $dadosWebhook['document']['id'] ?? null;
            $evento = $dadosWebhook['event'] ?? null;

            if (!$documentoId || !$evento) {
                return [
                    'success' => false,
                    'error' => 'Dados do webhook inválidos'
                ];
            }

            // Limpar cache do status do documento
            Cache::forget("autentique_doc_status_{$documentoId}");

            $resultado = [
                'success' => true,
                'documento_id' => $documentoId,
                'evento' => $evento,
                'dados' => $dadosWebhook
            ];

            // Processar evento específico
            switch ($evento) {
                case 'document.signed':
                    $resultado['acao'] = 'assinado';
                    break;
                case 'document.rejected':
                    $resultado['acao'] = 'recusado';
                    $resultado['motivo'] = $dadosWebhook['document']['signatures'][0]['reject_reason'] ?? 'Não informado';
                    break;
                case 'document.expired':
                    $resultado['acao'] = 'expirado';
                    break;
                default:
                    $resultado['acao'] = 'outro';
            }

            return $resultado;

        } catch (Exception $e) {
            Log::error('Erro ao processar webhook do Autentique', [
                'error' => $e->getMessage(),
                'webhook_data' => $dadosWebhook
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Fazer requisição GraphQL para o Autentique
     */
    protected function makeRequest($query, $variables = [])
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json'
            ])
            ->post($this->apiUrl, [
                'query' => $query,
                'variables' => $variables
            ]);

        if (!$response->successful()) {
            throw new Exception('Erro na requisição: ' . $response->status() . ' - ' . $response->body());
        }

        $data = $response->json();

        if (isset($data['errors'])) {
            throw new Exception('Erro GraphQL: ' . json_encode($data['errors']));
        }

        return $data;
    }

    /**
     * Gerar HTML com dados do corretor usando TemplateProcessor
     */
    protected function gerarHTMLComDados($corretor, $templateName)
    {
        try {
            // Caminho do template
            $templatePath = storage_path("app/templates/{$templateName}");
            
            // Preparar variáveis para o template FORM003
            $variaveis = [
                'DATA' => $this->obterCidadeCorretor($corretor) . ', ' . now()->locale('pt_BR')->isoFormat('DD [de] MMMM [de] YYYY'),
                'NOME_CORRETORA' => $corretor->razao_social ?? $corretor->nome, // Usar razão social se disponível
                'CODIGO_SUSEP' => $corretor->codigo_susep ?? 'Não informado',
                'CNPJ_CORRETORA' => $corretor->cnpj ?? 'Não informado',
                'DATA_GERACAO' => now()->format('d/m/Y H:i:s')
            ];

            Log::info('Processando template HTML para corretor', [
                'corretor_id' => $corretor->id,
                'template' => $templateName,
                'template_path' => $templatePath,
                'variaveis' => array_keys($variaveis)
            ]);

            // Criar instância do TemplateProcessor
            $processor = new TemplateProcessor($templatePath);
            
            // Definir variáveis e renderizar
            $processor->setVariables($variaveis);
            
            // Salvar em arquivo temporário
            $tempFilePath = $processor->saveToTempFile();

            Log::info('HTML gerado e salvo com sucesso', [
                'corretor_id' => $corretor->id,
                'temp_file' => $tempFilePath
            ]);

            return [
                'success' => true,
                'file_path' => $tempFilePath
            ];

        } catch (Exception $e) {
            Log::error('Exceção ao gerar HTML com dados do corretor', [
                'error' => $e->getMessage(),
                'corretor_id' => $corretor->id,
                'template' => $templateName
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter cidade do corretor (baseado no estado por enquanto)
     */
    protected function obterCidadeCorretor($corretor)
    {
        // Mapeamento básico estado -> capital (pode ser melhorado com dados reais)
        $capitais = [
            'SP' => 'São Paulo',
            'RJ' => 'Rio de Janeiro',
            'MG' => 'Belo Horizonte',
            'RS' => 'Porto Alegre',
            'PR' => 'Curitiba',
            'SC' => 'Florianópolis',
            'GO' => 'Goiânia',
            'DF' => 'Brasília',
            'BA' => 'Salvador',
            'PE' => 'Recife',
            'CE' => 'Fortaleza',
            'PA' => 'Belém',
            'AM' => 'Manaus',
            'MA' => 'São Luís',
            'PB' => 'João Pessoa',
            'ES' => 'Vitória',
            'AL' => 'Maceió',
            'SE' => 'Aracaju',
            'RN' => 'Natal',
            'PI' => 'Teresina',
            'MT' => 'Cuiabá',
            'MS' => 'Campo Grande',
            'AC' => 'Rio Branco',
            'RO' => 'Porto Velho',
            'RR' => 'Boa Vista',
            'AP' => 'Macapá',
            'TO' => 'Palmas'
        ];

        return $capitais[$corretor->estado] ?? $corretor->estado;
    }


    /**
     * Validar configuração do serviço e templates
     */
    public function validarConfiguracao()
    {
        $erros = [];

        if (!$this->token) {
            $erros[] = 'Token do Autentique não configurado';
        }

        if (!$this->apiUrl) {
            $erros[] = 'URL da API não configurada';
        }

        // Validar templates
        try {
            $templateErros = $this->templateProcessor->validateTemplate('declaracao-akad-template.html');
            if (!empty($templateErros)) {
                $erros[] = 'Problemas no template: ' . implode(', ', $templateErros);
            }
        } catch (Exception $e) {
            $erros[] = 'Erro ao validar template: ' . $e->getMessage();
        }

        // Validar diretórios
        $directorios = [
            storage_path('app/templates'),
            storage_path('app/temp')
        ];

        foreach ($directorios as $dir) {
            if (!is_dir($dir)) {
                $erros[] = "Diretório não existe: {$dir}";
            } elseif (!is_writable($dir)) {
                $erros[] = "Diretório sem permissão de escrita: {$dir}";
            }
        }

        if (empty($erros)) {
            try {
                // Testar conexão com API GraphQL
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->token,
                        'Content-Type' => 'application/json'
                    ])
                    ->post($this->apiUrl, [
                        'query' => 'query { viewer { name } }'
                    ]);

                if (!$response->successful()) {
                    $erros[] = 'Não foi possível conectar à API GraphQL do Autentique';
                }
            } catch (Exception $e) {
                $erros[] = 'Erro ao testar conexão GraphQL: ' . $e->getMessage();
            }
        }

        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }

    /**
     * Limpeza automática de arquivos temporários antigos
     */
    public function limpezaAutomatica()
    {
        try {
            $deletados = $this->templateProcessor->cleanupOldTempFiles();
            
            Log::info('Limpeza automática de arquivos temporários executada', [
                'arquivos_deletados' => $deletados
            ]);
            
            return $deletados;
        } catch (Exception $e) {
            Log::error('Erro na limpeza automática', [
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Obter instância do TemplateProcessor
     */
    public function getTemplateProcessor()
    {
        return $this->templateProcessor;
    }

    /**
     * Obter informações sobre templates disponíveis
     */
    public function listarTemplates()
    {
        try {
            $templates = $this->templateProcessor->listTemplates();
            $informacoes = [];

            foreach ($templates as $template) {
                try {
                    $info = $this->templateProcessor->getTemplateInfo($template);
                    $informacoes[] = $info;
                } catch (Exception $e) {
                    $informacoes[] = [
                        'name' => $template,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return [
                'success' => true,
                'templates' => $informacoes
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obter estatísticas de uso
     */
    public function obterEstatisticas()
    {
        try {
            $query = '
                query GetUsage {
                    viewer {
                        name
                        plan {
                            name
                            documents_limit
                            documents_used
                        }
                    }
                }
            ';

            $response = $this->makeRequest($query);

            if (isset($response['data']['viewer'])) {
                return [
                    'success' => true,
                    'data' => $response['data']['viewer']
                ];
            }

            return [
                'success' => false,
                'error' => 'Erro ao obter estatísticas'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // MÉTODO REMOVIDO: buscarLinkAssinaturaComRetry()
    // Autentique envia email automaticamente ao criar documento

    // MÉTODO REMOVIDO: extrairLinkAssinatura()
    // Não precisamos mais extrair links manualmente

    // MÉTODO REMOVIDO: buscarDocumentoPorId()
    // Não precisamos mais buscar documentos para extrair links
}