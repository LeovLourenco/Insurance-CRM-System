<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class AuditoriaController extends Controller
{
    /**
     * Exibir relatório de auditoria
     */
    public function index(Request $request)
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest();
        
        // Filtros
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }
        
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }
        
        if ($request->filled('usuario_id')) {
            $query->where('causer_id', $request->usuario_id);
        }
        
        if ($request->filled('evento')) {
            $query->where('event', $request->evento);
        }
        
        $atividades = $query->paginate(50);
        $usuarios = User::orderBy('name')->get();
        $logNames = Activity::distinct()->pluck('log_name')->filter();
        $eventos = Activity::distinct()->pluck('event')->filter();
        
        return view('relatorios.auditoria', compact('atividades', 'usuarios', 'logNames', 'eventos'));
    }

    /**
     * Obter detalhes de uma atividade específica via AJAX
     */
    public function detalhes($id)
    {
        $atividade = Activity::with(['causer', 'subject'])->findOrFail($id);
        
        return response()->json([
            'id' => $atividade->id,
            'evento' => $atividade->event,
            'log_name' => $atividade->log_name,
            'description' => $atividade->description,
            'created_at' => $atividade->created_at->format('d/m/Y H:i:s'),
            'causer' => [
                'id' => $atividade->causer?->id,
                'name' => $atividade->causer?->name ?? 'Sistema',
                'email' => $atividade->causer?->email
            ],
            'subject' => [
                'type' => $atividade->subject_type,
                'id' => $atividade->subject_id,
                'name' => $this->getSubjectName($atividade)
            ],
            'properties' => $atividade->properties,
            'changes' => $this->formatChanges($atividade)
        ]);
    }

    /**
     * Obter nome do subject baseado no tipo
     */
    private function getSubjectName($atividade)
    {
        if (!$atividade->subject) {
            return 'Registro removido';
        }

        // Para CorretoraSeguradora, montar nome descritivo
        if ($atividade->subject_type === 'App\\Models\\CorretoraSeguradora') {
            $corretora = $atividade->subject->corretora->nome ?? 'N/A';
            $seguradora = $atividade->subject->seguradora->nome ?? 'N/A';
            return "Relacionamento: {$corretora} ↔ {$seguradora}";
        }

        // Para outros modelos, tentar usar campo 'nome'
        return $atividade->subject->nome ?? $atividade->subject->name ?? "ID: {$atividade->subject->id}";
    }

    /**
     * Formatar mudanças de forma legível
     */
    private function formatChanges($atividade)
    {
        $properties = $atividade->properties;
        $changes = [];

        if (isset($properties['attributes']) && isset($properties['old'])) {
            $attributes = $properties['attributes'];
            $old = $properties['old'];

            foreach ($attributes as $key => $newValue) {
                $oldValue = $old[$key] ?? null;
                
                // Ocultar campos sensíveis
                if (in_array($key, ['password', 'remember_token', 'api_token'])) {
                    continue;
                }
                
                if ($oldValue !== $newValue) {
                    $changes[] = [
                        'field' => $this->formatFieldName($key),
                        'old' => $this->formatFieldValue($key, $oldValue),
                        'new' => $this->formatFieldValue($key, $newValue)
                    ];
                }
            }
        } elseif (isset($properties['attributes'])) {
            // Para criações, mostrar apenas valores novos
            foreach ($properties['attributes'] as $key => $value) {
                // Ocultar campos sensíveis
                if (in_array($key, ['password', 'remember_token', 'api_token'])) {
                    continue;
                }
                
                $changes[] = [
                    'field' => $this->formatFieldName($key),
                    'old' => null,
                    'new' => $this->formatFieldValue($key, $value)
                ];
            }
        }

        return $changes;
    }

    /**
     * Formatar nomes dos campos para exibição
     */
    private function formatFieldName($field)
    {
        $fieldNames = [
            'corretora_id' => 'Corretora',
            'seguradora_id' => 'Seguradora',
            'produto_id' => 'Produto',
            'usuario_id' => 'Usuário Responsável',
            'user_id' => 'Usuário',
            'causer_id' => 'Usuário que Alterou',
            'nome' => 'Nome',
            'name' => 'Nome',
            'email' => 'Email',
            'telefone' => 'Telefone',
            'cpf_cnpj' => 'CPF/CNPJ',
            'endereco' => 'Endereço',
            'cidade' => 'Cidade',
            'estado' => 'Estado',
            'cep' => 'CEP',
            'susep' => 'SUSEP',
            'suc_cpd' => 'SUC-CPD',
            'documento' => 'Documento',
            'observacoes' => 'Observações',
            'descricao' => 'Descrição',
            'linha' => 'Linha',
            'site' => 'Site',
            'status' => 'Status',
            'password_changed' => 'Senha Alterada',
            'password_changed_at' => 'Data da Alteração de Senha',
            'data_envio' => 'Data de Envio',
            'data_retorno' => 'Data de Retorno',
            'data_hora' => 'Data/Hora',
            'valor_premio' => 'Valor do Prêmio',
            'valor_is' => 'Valor IS',
            'email2' => 'Email Secundário',
            'email3' => 'Email Terciário',
            'created_at' => 'Data de Criação',
            'updated_at' => 'Data de Atualização'
        ];

        return $fieldNames[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Formatar valores dos campos para exibição amigável
     */
    private function formatFieldValue($field, $value)
    {
        // Valor nulo ou vazio
        if ($value === null || $value === '') {
            return '<em class="text-muted">vazio</em>';
        }

        // Campos de ID de usuário - buscar nome
        if (in_array($field, ['usuario_id', 'user_id', 'causer_id']) && is_numeric($value)) {
            try {
                $user = User::find($value);
                if ($user) {
                    return "{$user->name} (ID: {$value})";
                }
            } catch (\Exception $e) {
                // Em caso de erro, retornar apenas o ID
            }
            return "ID: {$value}";
        }

        // Campos de data/timestamp
        if (str_ends_with($field, '_at') || str_ends_with($field, '_date') || $field === 'data_hora') {
            try {
                if (is_string($value) && !empty($value)) {
                    return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
                }
            } catch (\Exception $e) {
                // Se não conseguir parsear, retornar valor original
            }
        }

        // Campos booleanos
        if (is_bool($value) || in_array($value, [0, 1, '0', '1', true, false], true)) {
            if (is_string($value)) {
                return $value === '1' ? 'Sim' : 'Não';
            }
            return $value ? 'Sim' : 'Não';
        }

        // Campos monetários
        if (in_array($field, ['valor_premio', 'valor_is']) && is_numeric($value)) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        }

        // Campo de senha sempre ocultar
        if ($field === 'password') {
            return '<em class="text-warning">Oculto por segurança</em>';
        }

        // Para strings longas, truncar se necessário
        if (is_string($value) && strlen($value) > 100) {
            return substr($value, 0, 100) . '...';
        }

        // Valor padrão
        return htmlspecialchars((string) $value);
    }
}
