{{-- 
    Partial para exibir status de cotação
    Localização: resources/views/cotacoes/partials/status.blade.php
    
    Uso: @include('cotacoes.partials.status', ['cotacao' => $cotacao, 'tipo' => 'completo'])
    
    Parâmetros:
    - $cotacao: Instância do model Cotacao
    - $tipo: 'simples', 'completo' ou 'detalhado' (padrão: 'simples')
--}}

@php
    $tipo = $tipo ?? 'simples';
    
    // Mapear status para as classes do Bootstrap que vocês usam
    $statusClasses = [
        'em_andamento' => 'primary',
        'finalizada' => 'success', 
        'cancelada' => 'danger',
        'aguardando' => 'warning',
        'em_analise' => 'info',
        'aprovada' => 'success',
        'rejeitada' => 'danger',
        'repique' => 'warning'
    ];
    
    $statusTextos = [
        'em_andamento' => 'Em Andamento',
        'finalizada' => 'Finalizada',
        'cancelada' => 'Cancelada', 
        'aguardando' => 'Aguardando envio',
        'em_analise' => 'Em Análise',
        'aprovada' => 'Aprovada',
        'rejeitada' => 'Rejeitada',
        'repique' => 'Repique'
    ];
@endphp

<div class="status-cotacao">
    @if($tipo === 'simples')
        {{-- Status simples para listagens --}}
        <span class="badge bg-{{ $statusClasses[$cotacao->status_exibicao] ?? 'secondary' }}">
            {{ $statusTextos[$cotacao->status_exibicao] ?? ucfirst(str_replace('_', ' ', $cotacao->status_exibicao)) }}
        </span>
        
    @elseif($tipo === 'completo')
        {{-- Status completo com informação adicional --}}
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-{{ $cotacao->status_exibicao_classe }}">
                {{ $cotacao->status_exibicao_formatado }}
            </span>
            
            @if($cotacao->status === 'em_andamento')
                <small class="text-muted">
                    ({{ $cotacao->getSeguradoras()['total'] }} seguradora{{ $cotacao->getSeguradoras()['total'] != 1 ? 's' : '' }})
                </small>
            @endif
        </div>
        
    @elseif($tipo === 'detalhado')
        {{-- Status detalhado para página de visualização --}}
        <div class="status-detalhado">
            <div class="row">
                <div class="col-md-6">
                    <strong>Status Geral:</strong>
                    <span class="badge bg-{{ $cotacao->status_tabela_classe }} ms-2">
                        {{ $cotacao->status_tabela_formatado }}
                    </span>
                </div>
                
                @if($cotacao->status === 'em_andamento')
                    <div class="col-md-6">
                        <strong>Status das Seguradoras:</strong>
                        <span class="badge bg-{{ $cotacao->status_consolidado_classe }} ms-2">
                            {{ $cotacao->status_consolidado_formatado }}
                        </span>
                    </div>
                @endif
            </div>
            
            @if($cotacao->status === 'em_andamento' && $cotacao->cotacaoSeguradoras->isNotEmpty())
                <div class="mt-3">
                    <div class="row text-center">
                        @php $counts = $cotacao->getSeguradoras(); @endphp
                        
                        <div class="col">
                            <div class="text-muted small">Total</div>
                            <div class="fw-bold">{{ $counts['total'] }}</div>
                        </div>
                        
                        @if($counts['aguardando'] > 0)
                            <div class="col">
                                <div class="text-muted small">Aguardando</div>
                                <div class="fw-bold text-warning">{{ $counts['aguardando'] }}</div>
                            </div>
                        @endif
                        
                        @if($counts['em_analise'] > 0)
                            <div class="col">
                                <div class="text-muted small">Em Análise</div>
                                <div class="fw-bold text-info">{{ $counts['em_analise'] }}</div>
                            </div>
                        @endif
                        
                        @if($counts['aprovadas'] > 0)
                            <div class="col">
                                <div class="text-muted small">Aprovadas</div>
                                <div class="fw-bold text-success">{{ $counts['aprovadas'] }}</div>
                            </div>
                        @endif
                        
                        @if($counts['rejeitadas'] > 0)
                            <div class="col">
                                <div class="text-muted small">Rejeitadas</div>
                                <div class="fw-bold text-danger">{{ $counts['rejeitadas'] }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
