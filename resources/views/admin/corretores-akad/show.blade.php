@extends('layouts.app')

@section('title', 'Detalhes Corretor AKAD')

@section('content')
<div class="container-fluid">
    
    <!-- Voltar -->
    <div class="mb-3">
        <a href="{{ route('admin.corretores-akad-gestao.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Dados Principais -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>
                        Dados da Corretora
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="text-muted small">Razão Social</label>
                            <div class="fw-bold">{{ $corretor->razao_social }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="text-muted small">CNPJ</label>
                            <div>{{ $corretor->cnpj ?: '-' }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="text-muted small">Código SUSEP</label>
                            <div>{{ $corretor->codigo_susep ?: '-' }}</div>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="text-muted small">Email Corporativo</label>
                            <div>
                                <a href="mailto:{{ $corretor->email }}">{{ $corretor->email }}</a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="text-muted small">Responsável Legal</label>
                            <div>{{ $corretor->nome }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="text-muted small">Telefone</label>
                            <div>{{ $corretor->telefone }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="text-muted small">IP do Cadastro</label>
                            <div>{{ $corretor->ip_address ?: '-' }}</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="text-muted small">User Agent</label>
                            <div class="small">{{ $corretor->user_agent ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Histórico de Eventos -->
            @if($corretor->logs && $corretor->logs->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Histórico de Eventos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($corretor->logs->sortByDesc('created_at') as $log)
                        <div class="timeline-item mb-3 border-bottom pb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    @if($log->evento === 'cadastro_criado')
                                        <i class="bi bi-plus-circle text-success"></i>
                                    @elseif($log->evento === 'documento_enviado')
                                        <i class="bi bi-envelope-check text-info"></i>
                                    @elseif($log->evento === 'erro_api_autentique')
                                        <i class="bi bi-exclamation-triangle text-danger"></i>
                                    @else
                                        <i class="bi bi-circle text-secondary"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $log->evento)) }}</div>
                                    @if($log->descricao)
                                        <div class="text-muted small">{{ $log->descricao }}</div>
                                    @endif
                                    @if($log->dados_extras && is_array($log->dados_extras))
                                        <details class="mt-2">
                                            <summary class="text-muted small" style="cursor: pointer;">Ver detalhes técnicos</summary>
                                            <pre class="mt-2 p-2 bg-light small">{{ json_encode($log->dados_extras, JSON_PRETTY_PRINT) }}</pre>
                                        </details>
                                    @endif
                                    <div class="text-muted small">
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                        @if($log->ip_address)
                                            - IP: {{ $log->ip_address }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($corretor->status === 'assinado')
                            <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                            <h5 class="mt-2 text-success">Assinado</h5>
                            @if($corretor->assinado_em)
                                <div class="text-muted small">Assinado em: {{ $corretor->assinado_em->format('d/m/Y H:i') }}</div>
                            @endif
                        @elseif($corretor->status === 'documento_enviado')
                            <i class="bi bi-envelope-check text-info" style="font-size: 4rem;"></i>
                            <h5 class="mt-2 text-info">Documento Enviado</h5>
                            @if($corretor->documento_enviado_em)
                                <div class="text-muted small">Enviado em: {{ $corretor->documento_enviado_em->format('d/m/Y H:i') }}</div>
                            @endif
                        @else
                            <i class="bi bi-clock-history text-warning" style="font-size: 4rem;"></i>
                            <h5 class="mt-2 text-warning">Pendente</h5>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <div class="text-muted small mb-2">Cadastrado em</div>
                    <div class="fw-bold">{{ $corretor->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
            
            <!-- Documentos -->
            @if($corretor->documentos && $corretor->documentos->count() > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Documentos
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($corretor->documentos->sortByDesc('created_at') as $doc)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="fw-semibold small">{{ $doc->documento_id }}</div>
                            <div class="text-muted small">
                                Status: {{ ucfirst($doc->status) }}
                                <br>
                                {{ $doc->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        @if($doc->documento_id)
                        <a href="https://app.autentique.com.br/documento/{{ $doc->documento_id }}" 
                           target="_blank"
                           class="btn btn-sm btn-outline-primary"
                           title="Ver no Autentique">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        @endif
                    </div>
                    @if(!$loop->last)<hr>@endif
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- Ações -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Ações</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.corretores-akad-gestao.reenviar', $corretor->id) }}" 
                          onsubmit="return confirm('Reenviar documento para {{ $corretor->email }}?')">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-arrow-repeat"></i> Reenviar Documento
                        </button>
                    </form>
                    
                    <a href="mailto:{{ $corretor->email }}?subject=Cadastro AKAD - {{ $corretor->razao_social }}" 
                       class="btn btn-outline-secondary w-100">
                        <i class="bi bi-envelope"></i> Enviar Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection