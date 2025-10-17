<?php

namespace App\Http\Controllers;

use App\Models\CorretorAkad;
use App\Models\DocumentoAkad;
use App\Models\LogCorretorAkad;
use App\Services\AutentiqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CorretorAkadController extends Controller
{
    // Service injetado por método conforme necessário para evitar erro no construtor

    /**
     * Exibir página de cadastro público
     */
    public function showCadastro()
    {
        return view('corretores-akad.cadastro');
    }

    /**
     * Processar cadastro público
     */
    public function storeCadastro(Request $request)
    {
        Log::info('🚀 INÍCIO storeCadastro', ['data' => $request->all()]);
        
        try {
            // Validação dos dados
            $validator = $this->validarDadosCorretor($request);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $dados = $validator->validated();

            DB::beginTransaction();

            // Criar corretor com novos campos essenciais
            $corretor = CorretorAkad::create([
                // Campos essenciais para FORM003
                'razao_social' => $dados['razao_social'],
                'cnpj' => $dados['cnpj'] ?? null,
                'codigo_susep' => $dados['codigo_susep'] ?? null,
                'email' => $dados['email'],
                'nome' => $dados['nome'], // Nome do responsável legal
                'telefone' => $dados['telefone'],
                
                // Campos de sistema
                'status' => CorretorAkad::STATUS_PENDENTE,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                
                // Campos legados (se fornecidos)
                'cpf' => $dados['cpf'] ?? null,
                'creci' => $dados['creci'] ?? null,
                'estado' => $dados['estado'] ?? null
            ]);

            Log::info('🔥 Corretor criado', ['id' => $corretor->id]);

            // Log do cadastro
            Log::info('🔥 ANTES logCadastro');
            LogCorretorAkad::logCadastro($corretor->id, $dados, $request->ip(), $request->userAgent());
            Log::info('✅ DEPOIS logCadastro');

            // Tentar enviar documento automaticamente
            Log::info('🔥 ANTES enviarDocumento');
            // TEMPORÁRIO: Forçar nova instância devido a problema de container
            $autentiqueService = new \App\Services\AutentiqueService();
            $resultadoDocumento = $this->enviarDocumentoAssinatura($corretor, $autentiqueService);

            if ($resultadoDocumento['success']) {
                Log::info('✅ Documento enviado com sucesso', ['doc_id' => $resultadoDocumento['documento_id']]);
                $corretor->marcarDocumentoEnviado(
                    $resultadoDocumento['documento_id'],
                    null // Não precisamos mais do link_assinatura
                );

                $message = 'Cadastro realizado com sucesso! Verifique seu email para assinar o contrato.';
            } else {
                Log::error('❌ Falha ao enviar documento', ['error' => $resultadoDocumento['error']]);
                $message = 'Cadastro realizado com sucesso! O documento será enviado em breve.';
                
                // Log do erro
                $corretor->logEvento(
                    'erro_api_autentique',
                    'Erro ao enviar documento automaticamente: ' . $resultadoDocumento['error'],
                    $resultadoDocumento
                );
            }

            DB::commit();

            Log::info('✅ FIM storeCadastro - SUCESSO', ['corretor_id' => $corretor->id]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'corretor_id' => $corretor->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('💥 ERRO FATAL storeCadastro', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Validar dados do corretor via AJAX
     */
    public function validarDados(Request $request)
    {
        $campo = $request->input('campo');
        $valor = $request->input('valor');

        switch ($campo) {
            case 'cpf':
                if (!CorretorAkad::validarCPF($valor)) {
                    return response()->json(['valid' => false, 'message' => 'CPF inválido']);
                }
                
                $existente = CorretorAkad::where('cpf', $valor)->exists();
                if ($existente) {
                    return response()->json(['valid' => false, 'message' => 'CPF já cadastrado']);
                }
                break;

            case 'email':
                if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    return response()->json(['valid' => false, 'message' => 'Email inválido']);
                }
                break;

            case 'creci':
                if (strlen($valor) < 3) {
                    return response()->json(['valid' => false, 'message' => 'CRECI deve ter pelo menos 3 caracteres']);
                }
                break;

            default:
                return response()->json(['valid' => true]);
        }

        return response()->json(['valid' => true]);
    }

    /**
     * Dashboard administrativo
     */
    public function index(Request $request)
    {
        $query = CorretorAkad::query();

        // Filtros
        if ($request->filled('search')) {
            $query->busca($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('estado')) {
            $query->porEstado($request->estado);
        }

        $corretores = $query->with(['documentoAtivo'])
                           ->latest()
                           ->paginate(20)
                           ->withQueryString();

        // Estatísticas
        $estatisticas = [
            'total' => CorretorAkad::count(),
            'pendentes' => CorretorAkad::pendentes()->count(),
            'documento_enviado' => CorretorAkad::comDocumentoEnviado()->count(),
            'assinados' => CorretorAkad::assinados()->count(),
            'ativos' => CorretorAkad::ativos()->count(),
        ];

        return view('admin.corretores-akad.index', compact('corretores', 'estatisticas'));
    }

    /**
     * Visualizar corretor específico
     */
    public function show(CorretorAkad $corretor)
    {
        $corretor->load(['documentos', 'logs' => function($query) {
            $query->latest()->limit(20);
        }]);

        return view('admin.corretores-akad.show', compact('corretor'));
    }

    /**
     * Enviar/reenviar documento para assinatura
     */
    public function enviarDocumento(CorretorAkad $corretor)
    {
        try {
            if (!$corretor->podeEnviarDocumento()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível enviar documento para este corretor no status atual.'
                ], 422);
            }

            // TEMPORÁRIO: Forçar nova instância devido a problema de container
            $autentiqueService = new \App\Services\AutentiqueService();
            $resultado = $this->enviarDocumentoAssinatura($corretor, $autentiqueService);

            if ($resultado['success']) {
                $documento = $corretor->marcarDocumentoEnviado(
                    $resultado['documento_id'],
                    $resultado['link_assinatura']
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Documento enviado com sucesso!',
                    'link_assinatura' => $resultado['link_assinatura']
                ]);
            } else {
                // Log do erro
                $corretor->logEvento(
                    'erro_api_autentique',
                    'Erro ao enviar documento: ' . $resultado['error'],
                    $resultado
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao enviar documento: ' . $resultado['error']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar documento AKAD', [
                'error' => $e->getMessage(),
                'corretor_id' => $corretor->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    /**
     * Ativar corretor
     */
    public function ativar(CorretorAkad $corretor)
    {
        if (!$corretor->isAssinado()) {
            return response()->json([
                'success' => false,
                'message' => 'Corretor deve ter assinado o documento antes de ser ativado.'
            ], 422);
        }

        $corretor->ativar();
        $corretor->logEvento('corretor_ativado', 'Corretor foi ativado no sistema');

        return response()->json([
            'success' => true,
            'message' => 'Corretor ativado com sucesso!'
        ]);
    }

    /**
     * Desativar corretor
     */
    public function desativar(CorretorAkad $corretor)
    {
        $corretor->desativar();
        $corretor->logEvento('corretor_desativado', 'Corretor foi desativado no sistema');

        return response()->json([
            'success' => true,
            'message' => 'Corretor desativado com sucesso!'
        ]);
    }

    /**
     * Processar webhook do Autentique
     * Baseado na documentação oficial: https://docs.autentique.com.br/webhook/
     */
    public function webhook(Request $request)
    {
        try {
            // 1. Validar assinatura HMAC do webhook
            if (!$this->validarAssinaturaWebhook($request)) {
                Log::warning('Webhook Autentique - Assinatura inválida', [
                    'headers' => $request->headers->all(),
                    'ip' => $request->ip()
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // 2. Obter dados do webhook
            $payload = $request->all();
            
            Log::info('Webhook Autentique recebido', [
                'event' => $payload['event']['type'] ?? 'unknown',
                'document_id' => $payload['event']['data']['document'] ?? 'unknown',
                'payload' => $payload
            ]);

            // 3. Validar estrutura do payload
            if (!isset($payload['event']) || !isset($payload['event']['data']['document'])) {
                Log::warning('Webhook Autentique - Payload inválido', $payload);
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $evento = $payload['event']['type'];
            $eventData = $payload['event']['data'];
            $documentoId = $eventData['document'];

            // 4. Buscar documento no banco
            $documento = DocumentoAkad::where('documento_id', $documentoId)->first();

            if (!$documento) {
                Log::warning('Webhook Autentique - Documento não encontrado', [
                    'documento_id' => $documentoId,
                    'evento' => $evento
                ]);
                return response()->json(['error' => 'Document not found'], 404);
            }

            // 5. Buscar corretor relacionado
            $corretor = $documento->corretorAkad;
            if (!$corretor) {
                Log::warning('Webhook Autentique - Corretor não encontrado', [
                    'documento_id' => $documentoId,
                    'documento_table_id' => $documento->id
                ]);
                return response()->json(['error' => 'Broker not found'], 404);
            }

            // 6. Processar evento específico
            $resultado = $this->processarEventoWebhook($evento, $eventData, $documento, $corretor);

            if ($resultado['success']) {
                Log::info('Webhook Autentique processado com sucesso', [
                    'documento_id' => $documentoId,
                    'evento' => $evento,
                    'corretor_id' => $corretor->id,
                    'novo_status' => $corretor->fresh()->status
                ]);
                
                return response()->json(['success' => true]);
            } else {
                Log::error('Erro ao processar evento do webhook', [
                    'documento_id' => $documentoId,
                    'evento' => $evento,
                    'error' => $resultado['error']
                ]);
                
                return response()->json(['error' => $resultado['error']], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erro fatal no webhook Autentique', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'payload' => $request->all()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Validar assinatura HMAC do webhook
     */
    private function validarAssinaturaWebhook(Request $request): bool
    {
        $secret = config('services.autentique.webhook_secret');
        
        if (!$secret) {
            Log::warning('Webhook secret não configurado - pulando validação HMAC');
            return true; // Permitir se não configurado (desenvolvimento)
        }

        $signature = $request->header('X-Autentique-Signature');
        if (!$signature) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Processar evento específico do webhook
     */
    private function processarEventoWebhook(string $evento, array $eventData, DocumentoAkad $documento, CorretorAkad $corretor): array
    {
        try {
            switch ($evento) {
                case 'signature.accepted':
                    return $this->processarAssinatura($eventData, $documento, $corretor);

                case 'signature.viewed':
                    return $this->processarVisualizacao($eventData, $documento, $corretor);

                case 'signature.rejected':
                    return $this->processarRejeicao($eventData, $documento, $corretor);

                case 'document.finished':
                    return $this->processarDocumentoFinalizado($eventData, $documento, $corretor);

                case 'document.expired':
                    return $this->processarExpiracao($eventData, $documento, $corretor);

                default:
                    Log::info('Evento webhook não processado', [
                        'evento' => $evento,
                        'documento_id' => $documento->documento_id
                    ]);
                    
                    return ['success' => true, 'message' => 'Evento não processado'];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro ao processar evento: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Processar assinatura do documento
     */
    private function processarAssinatura(array $eventData, DocumentoAkad $documento, CorretorAkad $corretor): array
    {
        DB::beginTransaction();
        
        try {
            $dadosAssinatura = $eventData;
            $dataAssinatura = isset($dadosAssinatura['signed']) 
                ? new \DateTime($dadosAssinatura['signed']) 
                : now();

            // Atualizar documento
            $documento->update([
                'status' => 'assinado',
                'data_assinatura' => $dataAssinatura,
                'dados_assinatura' => $dadosAssinatura
            ]);

            // Atualizar corretor apenas se ainda não estiver assinado
            if ($corretor->status !== CorretorAkad::STATUS_ASSINADO) {
                $corretor->update([
                    'status' => CorretorAkad::STATUS_ASSINADO,
                    'assinado_em' => $dataAssinatura
                ]);

                // Log do evento
                $corretor->logEvento(
                    'documento_assinado',
                    'Documento assinado via webhook Autentique',
                    [
                        'documento_id' => $documento->documento_id,
                        'assinatura_data' => $dataAssinatura->format('c'),
                        'webhook_payload' => $eventData
                    ]
                );
            }

            DB::commit();
            
            return ['success' => true, 'message' => 'Assinatura processada'];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Processar visualização do documento
     */
    private function processarVisualizacao(array $eventData, DocumentoAkad $documento, CorretorAkad $corretor): array
    {
        $dadosVisualizacao = $eventData;
        
        // Log da visualização
        $corretor->logEvento(
            'documento_visualizado',
            'Documento visualizado pelo corretor',
            [
                'documento_id' => $documento->documento_id,
                'visualizacao_data' => now()->toIso8601String(),
                'webhook_payload' => $eventData
            ]
        );

        return ['success' => true, 'message' => 'Visualização registrada'];
    }

    /**
     * Processar rejeição do documento
     */
    private function processarRejeicao(array $eventData, DocumentoAkad $documento, CorretorAkad $corretor): array
    {
        DB::beginTransaction();
        
        try {
            $dadosRejeicao = $eventData;
            $motivo = $dadosRejeicao['rejection_reason'] ?? 'Não informado';

            // Atualizar documento
            $documento->update([
                'status' => 'rejeitado',
                'motivo_rejeicao' => $motivo,
                'dados_rejeicao' => $dadosRejeicao
            ]);

            // Atualizar corretor
            $corretor->update([
                'status' => CorretorAkad::STATUS_PENDENTE // Volta para pendente
            ]);

            // Log do evento
            $corretor->logEvento(
                'documento_recusado',
                "Documento rejeitado: {$motivo}",
                [
                    'documento_id' => $documento->documento_id,
                    'motivo' => $motivo,
                    'webhook_payload' => $eventData
                ]
            );

            DB::commit();
            
            return ['success' => true, 'message' => 'Rejeição processada'];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Processar documento finalizado (todos assinaram)
     */
    private function processarDocumentoFinalizado(array $eventData, DocumentoAkad $documento, CorretorAkad $corretor): array
    {
        DB::beginTransaction();
        
        try {
            // Atualizar documento
            $documento->update([
                'status' => 'finalizado',
                'finalizado_em' => now(),
                'dados_finalizacao' => $eventData
            ]);

            // Se corretor ainda não está como assinado, marcar
            if ($corretor->status !== CorretorAkad::STATUS_ASSINADO) {
                $corretor->update([
                    'status' => CorretorAkad::STATUS_ASSINADO,
                    'assinado_em' => now()
                ]);
            }

            // Log do evento
            $corretor->logEvento(
                'documento_finalizado',
                'Documento finalizado - todas as partes assinaram',
                [
                    'documento_id' => $documento->documento_id,
                    'webhook_payload' => $eventData
                ]
            );

            DB::commit();
            
            return ['success' => true, 'message' => 'Documento finalizado'];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Processar expiração do documento
     */
    private function processarExpiracao(array $eventData, DocumentoAkad $documento, CorretorAkad $corretor): array
    {
        DB::beginTransaction();
        
        try {
            // Atualizar documento
            $documento->update([
                'status' => 'expirado',
                'expirado_em' => now(),
                'dados_expiracao' => $eventData
            ]);

            // Atualizar corretor - volta para pendente
            $corretor->update([
                'status' => CorretorAkad::STATUS_PENDENTE
            ]);

            // Log do evento
            $corretor->logEvento(
                'documento_expirado',
                'Documento expirou sem ser assinado',
                [
                    'documento_id' => $documento->documento_id,
                    'webhook_payload' => $eventData
                ]
            );

            DB::commit();
            
            return ['success' => true, 'message' => 'Expiração processada'];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Métodos auxiliares privados
     */
    private function validarDadosCorretor(Request $request)
    {
        return Validator::make($request->all(), [
            // Campos essenciais para FORM003
            'razao_social' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:18',
            'codigo_susep' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'nome' => 'required|string|max:255', // Nome do responsável legal
            'telefone' => 'required|string|max:20',
            
            // Campos legados (agora opcionais)
            'cpf' => 'nullable|string|size:14',
            'creci' => 'nullable|string|max:20',
            'estado' => 'nullable|string|size:2|in:AC,AL,AP,AM,BA,CE,DF,ES,GO,MA,MT,MS,MG,PA,PB,PR,PE,PI,RJ,RN,RS,RO,RR,SC,SP,SE,TO'
        ], [
            // Mensagens para campos essenciais
            'razao_social.required' => 'Razão social da corretora é obrigatória',
            'email.required' => 'Email corporativo é obrigatório',
            'email.email' => 'Email deve ter formato válido',
            'nome.required' => 'Nome do responsável legal é obrigatório',
            'telefone.required' => 'Telefone é obrigatório',
            
            // Mensagens para campos opcionais
            'cpf.size' => 'CPF deve ter formato XXX.XXX.XXX-XX',
            'estado.size' => 'Estado deve ser uma UF válida (2 caracteres)',
            'estado.in' => 'Estado deve ser uma UF válida'
        ]);
    }

    private function enviarDocumentoAssinatura(CorretorAkad $corretor, $autentiqueService = null)
    {
        try {
            $service = $autentiqueService ?? app(AutentiqueService::class);
            return $service->criarDocumentoCorretor($corretor);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Exportar dados dos corretores
     */
    public function export(Request $request)
    {
        $query = CorretorAkad::query();

        // Aplicar filtros
        if ($request->filled('search')) {
            $query->busca($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('estado')) {
            $query->porEstado($request->estado);
        }

        $corretores = $query->with(['documentoAtivo'])->get();

        $formato = $request->get('formato', 'csv');

        if ($formato === 'excel') {
            return $this->exportarExcel($corretores);
        }

        return $this->exportarCSV($corretores);
    }

    private function exportarCSV($corretores)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="corretores_akad_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($corretores) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Cabeçalhos
            fputcsv($file, [
                'Nome',
                'Email',
                'CPF',
                'CRECI',
                'Estado',
                'Telefone',
                'Status',
                'Cadastrado em',
                'Documento Enviado em',
                'Assinado em'
            ], ';');

            foreach ($corretores as $corretor) {
                fputcsv($file, [
                    $corretor->nome,
                    $corretor->email,
                    $corretor->cpf,
                    $corretor->creci,
                    $corretor->estado,
                    $corretor->telefone,
                    $corretor->status_badge['text'],
                    $corretor->created_at->format('d/m/Y H:i:s'),
                    $corretor->documento_enviado_em ? $corretor->documento_enviado_em->format('d/m/Y H:i:s') : '',
                    $corretor->assinado_em ? $corretor->assinado_em->format('d/m/Y H:i:s') : ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportarExcel($corretores)
    {
        // Implementar export Excel se necessário
        // Por enquanto, usar CSV como fallback
        return $this->exportarCSV($corretores);
    }
}