@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Histórico de Auditoria</h1>
            <p class="text-muted">Relatório de atividades do sistema</p>
        </div>
    </div>

    <!-- Card de Filtros -->
    <div class="modern-card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>Filtros
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('relatorios.auditoria') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" 
                               value="{{ request('data_inicio') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" 
                               value="{{ request('data_fim') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Usuário</label>
                        <select name="usuario_id" class="form-select">
                            <option value="">Todos os usuários</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" 
                                    {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Evento</label>
                        <select name="evento" class="form-select">
                            <option value="">Todos os eventos</option>
                            @foreach($eventos as $evento)
                                <option value="{{ $evento }}" 
                                    {{ request('evento') == $evento ? 'selected' : '' }}>
                                    {{ ucfirst($evento) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Atividade</label>
                        <select name="log_name" class="form-select">
                            <option value="">Todos os tipos</option>
                            @foreach($logNames as $logName)
                                <option value="{{ $logName }}" 
                                    {{ request('log_name') == $logName ? 'selected' : '' }}>
                                    {{ ucfirst($logName) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('relatorios.auditoria') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Limpar Filtros
                        </a>
                        <span class="text-muted ms-3">
                            {{ $atividades->total() }} registro(s) encontrado(s)
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Atividades -->
    <div class="modern-card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>Atividades Registradas
            </h5>
        </div>
        <div class="card-body p-0">
            @if($atividades->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="130">Data/Hora</th>
                                <th width="150">Usuário</th>
                                <th width="100">Evento</th>
                                <th>Descrição</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($atividades as $atividade)
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            {{ $atividade->created_at->format('d/m/Y') }}<br>
                                            {{ $atividade->created_at->format('H:i:s') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($atividade->causer)
                                            <div>
                                                <strong>{{ $atividade->causer->name }}</strong><br>
                                                <small class="text-muted">{{ $atividade->causer->email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                <i class="bi bi-robot me-1"></i>Sistema
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ 
                                            $atividade->event == 'created' ? 'bg-success' : 
                                            ($atividade->event == 'updated' ? 'bg-primary' : 'bg-danger') 
                                        }}">
                                            @switch($atividade->event)
                                                @case('created')
                                                    <i class="bi bi-plus-circle me-1"></i>Criado
                                                    @break
                                                @case('updated')
                                                    <i class="bi bi-pencil me-1"></i>Atualizado
                                                    @break
                                                @case('deleted')
                                                    <i class="bi bi-trash me-1"></i>Excluído
                                                    @break
                                                @default
                                                    {{ ucfirst($atividade->event) }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $atividade->description }}</div>
                                        @if($atividade->log_name && $atividade->log_name !== 'default')
                                            <small class="text-muted">
                                                <i class="bi bi-tag me-1"></i>{{ ucfirst($atividade->log_name) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="verDetalhes({{ $atividade->id }})"
                                                title="Ver detalhes">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 border-top">
                    {{ $atividades->links() }}
                </div>
            @else
                <div class="p-5 text-center">
                    <i class="bi bi-search display-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhuma atividade encontrada</h5>
                    <p class="text-muted">Ajuste os filtros para encontrar registros de auditoria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="detalhesModal" tabindex="-1" aria-labelledby="detalhesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalhesModalLabel">
                    <i class="bi bi-info-circle me-2"></i>Detalhes da Atividade
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalhesContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalhes(id) {
    const modal = new bootstrap.Modal(document.getElementById('detalhesModal'));
    const content = document.getElementById('detalhesContent');
    
    // Mostrar loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Buscar detalhes via AJAX
    fetch(`/relatorios/auditoria/${id}/detalhes`)
        .then(response => response.json())
        .then(data => {
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Informações Gerais</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="100">ID:</th>
                                <td>${data.id}</td>
                            </tr>
                            <tr>
                                <th>Data:</th>
                                <td>${data.created_at}</td>
                            </tr>
                            <tr>
                                <th>Evento:</th>
                                <td>
                                    <span class="badge ${
                                        data.evento == 'created' ? 'bg-success' : 
                                        (data.evento == 'updated' ? 'bg-primary' : 'bg-danger')
                                    }">
                                        ${data.evento}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Usuário:</th>
                                <td>${data.causer.name} (${data.causer.email || 'Sistema'})</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Objeto Afetado</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="100">Tipo:</th>
                                <td>${data.subject.type.replace('App\\\\Models\\\\', '')}</td>
                            </tr>
                            <tr>
                                <th>ID:</th>
                                <td>${data.subject.id}</td>
                            </tr>
                            <tr>
                                <th>Nome:</th>
                                <td>${data.subject.name}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <h6 class="fw-bold mb-3">Descrição</h6>
                <p class="alert alert-info">${data.description}</p>
            `;
            
            // Mostrar mudanças se existirem
            if (data.changes && data.changes.length > 0) {
                html += `
                    <h6 class="fw-bold mb-3">Mudanças Realizadas</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Valor Anterior</th>
                                    <th>Novo Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.changes.forEach(change => {
                    html += `
                        <tr>
                            <td><strong>${change.field}</strong></td>
                            <td class="text-muted">${change.old || '<em>vazio</em>'}</td>
                            <td class="text-success">${change.new || '<em>vazio</em>'}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            content.innerHTML = html;
        })
        .catch(error => {
            console.error('Erro ao buscar detalhes:', error);
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Erro ao carregar os detalhes da atividade.
                </div>
            `;
        });
}
</script>
@endsection