@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Detalhes da Apólice</h1>
            <p class="text-muted mb-0">
                {{ $apolice->numero_apolice ?? 'Apólice #' . $apolice->id }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @can('update', $apolice)
                <a href="{{ route('apolices.edit', $apolice) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-2"></i>Editar
                </a>
            @endcan
            <a href="{{ route('apolices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Card Principal -->
        <div class="col-lg-8">
            <div class="modern-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>
                        Informações Principais
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Identificação</h6>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Número da Apólice</label>
                                <div class="fw-medium">{{ $apolice->numero_apolice ?? 'Não informado' }}</div>
                            </div>
                            @if($apolice->endosso)
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Endosso</label>
                                    <div class="fw-medium">{{ $apolice->endosso }}</div>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label text-muted small">Status</label>
                                <div>
                                    @php
                                        $statusClasses = [
                                            'pendente_emissao' => 'bg-warning',
                                            'ativa' => 'bg-success',
                                            'renovacao' => 'bg-info',
                                            'cancelada' => 'bg-danger'
                                        ];
                                        $statusClass = $statusClasses[$apolice->status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $statusClass }} fs-6">
                                        {{ $apolice->status_formatado }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Origem</label>
                                <div>
                                    @if($apolice->origem === 'cotacao')
                                        <span class="badge bg-primary bg-opacity-10 text-primary fs-6">
                                            <i class="bi bi-file-earmark-text me-1"></i>Cotação
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6">
                                            <i class="bi bi-upload me-1"></i>Importada
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Relacionamentos</h6>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Seguradora</label>
                                <div class="fw-medium">{{ $apolice->seguradora->nome ?? 'Não vinculada' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Produto</label>
                                <div class="fw-medium">{{ $apolice->produto->nome ?? 'Não vinculado' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Corretora</label>
                                <div class="fw-medium">{{ $apolice->corretora->nome ?? 'Não vinculada' }}</div>
                            </div>
                            @if($apolice->cotacao)
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Cotação de Origem</label>
                                    <div>
                                        <a href="{{ route('cotacoes.show', $apolice->cotacao) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-earmark-text me-1"></i>
                                            Ver Cotação #{{ $apolice->cotacao->id }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Segurado -->
            <div class="modern-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-check me-2"></i>
                        Dados do Segurado
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Nome</label>
                                <div class="fw-medium">
                                    {{ $apolice->segurado->nome ?? $apolice->nome_segurado ?? 'Não informado' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">CNPJ</label>
                                <div class="fw-medium">
                                    @if($apolice->segurado && $apolice->segurado->documento)
                                        {{ $apolice->segurado->documento_formatado }}
                                    @elseif($apolice->cnpj_segurado)
                                        {{ $apolice->cnpj_segurado_formatado }}
                                    @else
                                        Não informado
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Corretor -->
            @if($apolice->corretora || $apolice->nome_corretor)
                <div class="modern-card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge me-2"></i>
                            Dados do Corretor
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Nome</label>
                                    <div class="fw-medium">
                                        {{ $apolice->corretora->nome ?? $apolice->nome_corretor ?? 'Não informado' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small">CNPJ</label>
                                    <div class="fw-medium">
                                        @if($apolice->corretora && $apolice->corretora->cpf_cnpj)
                                            {{ $apolice->corretora->cpf_cnpj }}
                                        @elseif($apolice->cnpj_corretor)
                                            {{ $apolice->cnpj_corretor_formatado }}
                                        @else
                                            Não informado
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Card Vigência -->
            <div class="modern-card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-range me-2"></i>
                        Vigência
                    </h5>
                </div>
                <div class="card-body">
                    @if($apolice->data_emissao)
                        <div class="mb-3">
                            <label class="form-label text-muted small">Data de Emissão</label>
                            <div class="fw-medium">{{ $apolice->data_emissao->format('d/m/Y') }}</div>
                        </div>
                    @endif
                    @if($apolice->inicio_vigencia)
                        <div class="mb-3">
                            <label class="form-label text-muted small">Início da Vigência</label>
                            <div class="fw-medium">{{ $apolice->inicio_vigencia->format('d/m/Y') }}</div>
                        </div>
                    @endif
                    @if($apolice->fim_vigencia)
                        <div class="mb-3">
                            <label class="form-label text-muted small">Fim da Vigência</label>
                            <div class="fw-medium">{{ $apolice->fim_vigencia->format('d/m/Y') }}</div>
                        </div>
                        @if($apolice->status === 'ativa')
                            @php
                                $diasRestantes = $apolice->diasRestantesVigencia();
                            @endphp
                            @if($diasRestantes !== null)
                                <div class="mb-3">
                                    <label class="form-label text-muted small">Dias Restantes</label>
                                    <div>
                                        @if($diasRestantes > 30)
                                            <span class="badge bg-success">{{ $diasRestantes }} dias</span>
                                        @elseif($diasRestantes > 0)
                                            <span class="badge bg-warning">{{ $diasRestantes }} dias</span>
                                        @else
                                            <span class="badge bg-danger">Vencida</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endif
                    @if($apolice->data_pagamento)
                        <div class="mb-3">
                            <label class="form-label text-muted small">Data de Pagamento</label>
                            <div class="fw-medium">{{ $apolice->data_pagamento->format('d/m/Y') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card Valores -->
            @if($apolice->premio_liquido || $apolice->parcela || $apolice->total_parcelas)
                <div class="modern-card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-currency-dollar me-2"></i>
                            Valores
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($apolice->premio_liquido)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Prêmio Líquido</label>
                                <div class="fw-medium fs-5 text-success">
                                    R$ {{ number_format($apolice->premio_liquido, 2, ',', '.') }}
                                </div>
                            </div>
                        @endif
                        @if($apolice->parcela || $apolice->total_parcelas)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Parcelamento</label>
                                <div class="fw-medium">
                                    Parcela {{ $apolice->parcela ?? 0 }} de {{ $apolice->total_parcelas ?? 0 }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Card Informações Técnicas -->
            @if($apolice->ramo || $apolice->linha_produto)
                <div class="modern-card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-gear me-2"></i>
                            Informações Técnicas
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($apolice->ramo)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Ramo</label>
                                <div class="fw-medium">{{ $apolice->ramo }}</div>
                            </div>
                        @endif
                        @if($apolice->linha_produto)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Linha do Produto</label>
                                <div class="fw-medium">{{ $apolice->linha_produto }}</div>
                            </div>
                        @endif
                        @if($apolice->usuario)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Responsável</label>
                                <div class="fw-medium">{{ $apolice->usuario->name }}</div>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label text-muted small">Criada em</label>
                            <div class="fw-medium">{{ $apolice->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        @if($apolice->updated_at != $apolice->created_at)
                            <div class="mb-3">
                                <label class="form-label text-muted small">Última atualização</label>
                                <div class="fw-medium">{{ $apolice->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Card Observações -->
    @if($apolice->observacoes_endosso)
        <div class="modern-card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-chat-text me-2"></i>
                    Observações
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $apolice->observacoes_endosso }}</p>
            </div>
        </div>
    @endif
</div>

<style>
.modern-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.card-header {
    border-radius: 1rem 1rem 0 0 !important;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}
</style>
@endsection