@extends('layouts.app')

@section('title', 'Corretores AKAD')

@section('content')
<div class="container-fluid">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-primary mb-1">Corretores AKAD</h4>
            <p class="text-muted mb-0">Gerenciar cadastros de corretores parceiros</p>
        </div>
        <a href="/corretor-akad/cadastro" target="_blank" class="btn btn-outline-primary">
            <i class="bi bi-link-45deg"></i> Link Público de Cadastro
        </a>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-people fs-2 text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock-history fs-2 text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pendentes</h6>
                            <h3 class="mb-0">{{ $stats['pendente'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-envelope-check fs-2 text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Doc. Enviado</h6>
                            <h3 class="mb-0">{{ $stats['documento_enviado'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle fs-2 text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Assinados</h6>
                            <h3 class="mb-0">{{ $stats['assinado'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Buscar por nome, email, CNPJ..."
                           value="{{ request('search') }}">
                </div>
                
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Todos os status</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="documento_enviado" {{ request('status') == 'documento_enviado' ? 'selected' : '' }}>Documento Enviado</option>
                        <option value="assinado" {{ request('status') == 'assinado' ? 'selected' : '' }}>Assinado</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
                
                <div class="col-md-2">
                    <a href="{{ route('admin.corretores-akad-gestao.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Razão Social</th>
                            <th>Email</th>
                            <th>CNPJ</th>
                            <th>SUSEP</th>
                            <th>Status</th>
                            <th>Cadastro</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($corretores as $corretor)
                        <tr>
                            <td class="fw-bold">#{{ $corretor->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $corretor->razao_social }}</div>
                                <small class="text-muted">Resp: {{ $corretor->nome }}</small>
                            </td>
                            <td>{{ $corretor->email }}</td>
                            <td>{{ $corretor->cnpj ?: '-' }}</td>
                            <td>{{ $corretor->codigo_susep ?: '-' }}</td>
                            <td>
                                @if($corretor->status === 'assinado')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Assinado
                                    </span>
                                @elseif($corretor->status === 'documento_enviado')
                                    <span class="badge bg-info">
                                        <i class="bi bi-envelope-check"></i> Doc. Enviado
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock-history"></i> Pendente
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $corretor->created_at->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.corretores-akad-gestao.show', $corretor->id) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="Ver detalhes">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Nenhum corretor cadastrado
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($corretores->hasPages())
        <div class="card-footer bg-white">
            {{ $corretores->links() }}
        </div>
        @endif
    </div>
</div>
@endsection