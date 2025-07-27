@extends('layouts.app')

@section('title', 'Cotação #' . $cotacao->id)

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-file-earmark-text"></i> Cotação #{{ $cotacao->id }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('cotacoes.index') }}">Cotações</a></li>
                    <li class="breadcrumb-item active">Visualizar</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('cotacoes.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            @if($cotacao->pode_editar)
                <a href="{{ route('cotacoes.edit', $cotacao->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            @endif
            @if($cotacao->pode_enviar)
                <button class="btn btn-success" onclick="enviarTodasSeguradoras()">
                    <i class="bi bi-send"></i> Enviar Todas
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- Informações Gerais --}}
        <div class="col-lg-8">
            <div class="modern-card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-info-circle"></i> Informações Gerais
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Segurado:</strong><br>
                            <span class="text-gray-800">{{ $cotacao->segurado->nome ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Corretora:</strong><br>
                            <span class="text-gray-800">{{ $cotacao->corretora->nome ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-6 mt-3">
                            <strong>Produto:</strong><br>
                            <span class="text-gray-800">{{ $cotacao->produto->nome ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-6 mt-3">
                            <strong>Criado em:</strong><br>
                            <span class="text-gray-800">{{ $cotacao->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    
                    @if($cotacao->observacoes)
                        <div class="mt-3">
                            <strong>Observações:</strong><br>
                            <div class="bg-light p-3 rounded">
                                {{ $cotacao->observacoes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Seguradoras com Cards/Accordion --}}
            <div class="modern-card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-building"></i> Seguradoras ({{ $cotacao->cotacaoSeguradoras->count() }})
                    </h6>
                    
                    {{-- Progress Bar de Respostas --}}
                    <div class="d-flex align-items-center">
                        <small class="text-muted me-2">Progresso:</small>
                        <div class="progress me-2" style="width: 100px; height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $cotacao->percentual_resposta }}%;" 
                                 aria-valuenow="{{ $cotacao->percentual_resposta }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small class="text-muted">{{ $cotacao->quantidade_respondida }}/{{ $cotacao->cotacaoSeguradoras->count() }}</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($cotacao->cotacaoSeguradoras->count() > 0)
                        <div class="accordion" id="accordionSeguradoras">
                            @foreach($cotacao->cotacaoSeguradoras as $index => $cotacaoSeguradora)
                                @php
                                    $statusClasses = [
                                        'aguardando' => 'warning',
                                        'em_analise' => 'info',
                                        'aprovada' => 'success',
                                        'rejeitada' => 'danger',
                                        'repique' => 'warning'
                                    ];
                                    $statusTextos = [
                                        'aguardando' => 'Aguardando',
                                        'em_analise' => 'Em Análise',
                                        'aprovada' => 'Aprovada',
                                        'rejeitada' => 'Rejeitada',
                                        'repique' => 'Repique'
                                    ];
                                @endphp
                                
                                <div class="accordion-item border rounded mb-2">
                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" 
                                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                                aria-controls="collapse{{ $index }}">
                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-building text-primary me-2"></i>
                                                    <strong>{{ $cotacaoSeguradora->seguradora->nome }}</strong>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($cotacaoSeguradora->valor_premio)
                                                        <span class="badge bg-success">
                                                            R$ {{ number_format($cotacaoSeguradora->valor_premio, 2, ',', '.') }}
                                                        </span>
                                                    @endif
                                                    <span class="badge bg-{{ $statusClasses[$cotacaoSeguradora->status] ?? 'secondary' }}">
                                                        {{ $statusTextos[$cotacaoSeguradora->status] ?? ucfirst($cotacaoSeguradora->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}" 
                                         class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                         aria-labelledby="heading{{ $index }}" 
                                         data-bs-parent="#accordionSeguradoras">
                                        <div class="accordion-body p-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="info-group mb-3">
                                                        <label class="text-muted small">Status:</label>
                                                        <div>
                                                            <span class="badge bg-{{ $statusClasses[$cotacaoSeguradora->status] ?? 'secondary' }}">
                                                                {{ $statusTextos[$cotacaoSeguradora->status] ?? ucfirst($cotacaoSeguradora->status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    @if($cotacaoSeguradora->data_envio)
                                                        <div class="info-group mb-3">
                                                            <label class="text-muted small">Data de Envio:</label>
                                                            <div>{{ $cotacaoSeguradora->data_envio->format('d/m/Y H:i') }}</div>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($cotacaoSeguradora->data_retorno)
                                                        <div class="info-group mb-3">
                                                            <label class="text-muted small">Data de Retorno:</label>
                                                            <div>{{ $cotacaoSeguradora->data_retorno->format('d/m/Y H:i') }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    @if($cotacaoSeguradora->valor_premio)
                                                        <div class="info-group mb-3">
                                                            <label class="text-muted small">Valor do Prêmio:</label>
                                                            <div class="h5 text-success mb-0">
                                                                R$ {{ number_format($cotacaoSeguradora->valor_premio, 2, ',', '.') }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($cotacaoSeguradora->valor_is)
                                                        <div class="info-group mb-3">
                                                            <label class="text-muted small">Valor IS:</label>
                                                            <div class="h6 text-info mb-0">
                                                                R$ {{ number_format($cotacaoSeguradora->valor_is, 2, ',', '.') }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if($cotacaoSeguradora->observacoes)
                                                <div class="info-group mb-3">
                                                    <label class="text-muted small">Observações:</label>
                                                    <div class="bg-light p-2 rounded">{{ $cotacaoSeguradora->observacoes }}</div>
                                                </div>
                                            @endif
                                            
                                            @if($cotacao->pode_editar)
                                                <div class="d-flex justify-content-end">
                                                    <button class="btn btn-outline-primary btn-sm" 
                                                            onclick="abrirModalStatus({{ $cotacaoSeguradora->seguradora_id }}, '{{ $cotacaoSeguradora->status }}', '{{ $cotacaoSeguradora->observacoes }}', {{ $cotacaoSeguradora->valor_premio ?? 0 }}, {{ $cotacaoSeguradora->valor_is ?? 0 }})">
                                                        <i class="bi bi-pencil"></i> Atualizar Status
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-building fa-2x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Nenhuma seguradora selecionada para esta cotação.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Status Card --}}
            <div class="modern-card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart"></i> Status da Cotação
                    </h6>
                </div>
                <div class="card-body p-4">
                    @include('cotacoes.partials.status', [
                        'cotacao' => $cotacao, 
                        'tipo' => 'detalhado'
                    ])
                </div>
            </div>

            {{-- Timeline de Atividades --}}
            <div class="modern-card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history"></i> Timeline de Atividades
                    </h6>
                </div>
                <div class="card-body">
                    @if($cotacao->atividades->count() > 0)
                        <div class="timeline">
                            @foreach($cotacao->atividades->sortByDesc('created_at') as $atividade)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ $atividade->tipo === 'geral' ? 'primary' : 'info' }}"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <small class="text-muted">
                                                {{ $atividade->created_at->format('d/m/Y H:i') }}
                                                @if($atividade->user)
                                                    - {{ $atividade->user->name }}
                                                @endif
                                            </small>
                                        </div>
                                        <div class="timeline-body">
                                            {{ $atividade->descricao }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-clock fs-1 text-muted mb-2"></i>
                            <p class="text-muted mb-0">Nenhuma atividade registrada ainda.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para Atualizar Status da Seguradora --}}
<div class="modal fade" id="modalStatusSeguradora" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atualizar Status da Seguradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formStatusSeguradora">
                    @csrf
                    @method('PUT')
                    
                    <input type="hidden" id="seguradoraId" name="seguradora_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="statusSeguradora" name="status" required>
                            <option value="aguardando">Aguardando Resposta</option>
                            <option value="em_analise">Em Análise</option>
                            <option value="aprovada">Aprovada</option>
                            <option value="rejeitada">Rejeitada</option>
                            <option value="repique">Repique Solicitado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Valor do Prêmio</label>
                        <input type="number" class="form-control" id="valorPremio" name="valor_premio" 
                               step="0.01" min="0" placeholder="0,00">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Valor IS</label>
                        <input type="number" class="form-control" id="valorIs" name="valor_is" 
                               step="0.01" min="0" placeholder="0,00">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoesSeguradora" name="observacoes" 
                                  rows="3" maxlength="500"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarStatusSeguradora()">
                    <i class="bi bi-check-circle"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Timeline styles */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -36px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -31px;
    top: 12px;
    bottom: -20px;
    width: 2px;
    background-color: #e3e6f0;
}

.timeline-content {
    background: #f8f9fc;
    padding: 10px;
    border-radius: 5px;
    border-left: 3px solid #5a5c69;
}

.timeline-header {
    margin-bottom: 5px;
}

.timeline-body {
    font-size: 14px;
    color: #5a5c69;
}

/* Modern card styles */
.modern-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    border: none;
}

/* Info group styles */
.info-group label {
    font-weight: 600;
    margin-bottom: 2px;
    display: block;
}

/* Accordion customization */
.accordion-button:not(.collapsed) {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    border-color: rgba(var(--bs-primary-rgb), 0.2);
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
    border-color: rgba(var(--bs-primary-rgb), 0.5);
}

.accordion-item {
    border: 1px solid rgba(0,0,0,0.125);
}

/* Progress bar customization */
.progress {
    background-color: #e9ecef;
    border-radius: 0.375rem;
}

.progress-bar {
    transition: width 0.6s ease;
}

/* Badge improvements */
.badge {
    font-size: 0.75em;
    font-weight: 500;
}

/* Button improvements */
.btn-group .btn + .btn {
    margin-left: -1px;
}

/* Text utilities */
.text-gray-800 {
    color: #5a5c69 !important;
}

.text-muted {
    color: #6c757d !important;
}

/* Card improvements */
.card-body {
    padding: 1.5rem !important;
}

.card-header {
    background-color: rgba(0,0,0,0.02);
    border-bottom: 1px solid rgba(0,0,0,0.125);
    padding: 1rem 1.5rem;
}

/* Accordion body específico */
.accordion-body {
    padding: 1.5rem !important;
}

/* Font weight utility */
.font-weight-bold {
    font-weight: 600 !important;
}

/* Spacing improvements */
.py-3 {
    padding-top: 1rem !important;
    padding-bottom: 1rem !important;
}

.mb-0 {
    margin-bottom: 0 !important;
}

.mb-2 {
    margin-bottom: 0.5rem !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.me-2 {
    margin-right: 0.5rem !important;
}

.me-3 {
    margin-right: 1rem !important;
}

.ms-2 {
    margin-right: 0.5rem !important;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-group .btn {
        margin-left: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
let seguradoraAtual = null;

// Função para mostrar toast (melhor que alert)
function showToast(message, type = 'success') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
    }
    
    const toastElement = document.createElement('div');
    toastElement.innerHTML = toastHtml;
    toastContainer.appendChild(toastElement.firstElementChild);
    
    const toast = new bootstrap.Toast(toastContainer.lastElementChild);
    toast.show();
    
    toastContainer.lastElementChild.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

function abrirModalStatus(seguradoraId, status, observacoes, valorPremio, valorIs) {
    seguradoraAtual = seguradoraId;
    
    document.getElementById('seguradoraId').value = seguradoraId;
    document.getElementById('statusSeguradora').value = status;
    document.getElementById('observacoesSeguradora').value = observacoes || '';
    document.getElementById('valorPremio').value = valorPremio || '';
    document.getElementById('valorIs').value = valorIs || '';
    
    new bootstrap.Modal(document.getElementById('modalStatusSeguradora')).show();
}

function salvarStatusSeguradora() {
    if (!seguradoraAtual) return;
    
    const formData = new FormData(document.getElementById('formStatusSeguradora'));
    
    fetch(`/cotacoes/{{ $cotacao->id }}/seguradoras/${seguradoraAtual}/status`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalStatusSeguradora')).hide();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao salvar. Tente novamente.', 'danger');
    });
}

function enviarTodasSeguradoras() {
    if (!confirm('Deseja enviar esta cotação para todas as seguradoras pendentes?')) {
        return;
    }
    
    fetch(`/cotacoes/{{ $cotacao->id }}/enviar-todas`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao enviar. Tente novamente.', 'danger');
    });
}
</script>
@endpush
@endsection