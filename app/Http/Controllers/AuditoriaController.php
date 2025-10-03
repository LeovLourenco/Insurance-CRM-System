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
                
                if ($oldValue !== $newValue) {
                    $changes[] = [
                        'field' => $this->formatFieldName($key),
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }
        } elseif (isset($properties['attributes'])) {
            // Para criações, mostrar apenas valores novos
            foreach ($properties['attributes'] as $key => $value) {
                $changes[] = [
                    'field' => $this->formatFieldName($key),
                    'old' => null,
                    'new' => $value
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
            'nome' => 'Nome',
            'email' => 'Email',
            'telefone' => 'Telefone',
            'created_at' => 'Data de Criação',
            'updated_at' => 'Data de Atualização'
        ];

        return $fieldNames[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}
