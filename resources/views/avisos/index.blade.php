@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Central de Avisos</h1>
            <p class="text-muted mb-0">Reporte problemas ou solicite alterações no sistema</p>
        </div>
    </div>

    <div class="row mb-5">
        <!-- Card Único Centralizado -->
        <div class="col-md-6 offset-md-3">
            <div class="modern-card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-headset me-2"></i>
                        Abrir Chamado de Suporte
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-headset display-1 text-primary mb-3"></i>
                        <h6 class="fw-bold mb-3">Reporte problemas, solicite alterações de dados ou sugira melhorias</h6>
                        <p class="text-muted mb-4">
                            Nossa equipe de suporte está pronta para ajudar você com qualquer necessidade relacionada ao sistema.
                        </p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Bugs e erros técnicos
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Alteração de dados (corretoras, produtos, vínculos)
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Sugestões de melhorias
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Outras solicitações
                            </li>
                        </ul>
                    </div>
                    <a href="https://digitalinova.atlassian.net/jira/software/form/3ff92af6-313a-46f5-b00b-1bdd765e3034" 
                       target="_blank" 
                       class="btn btn-primary btn-lg">
                        <i class="bi bi-headset me-2"></i>Abrir Formulário de Suporte
                        <i class="bi bi-box-arrow-up-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Instruções -->
    <div class="modern-card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Como Preencher o Formulário
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">📝 Informações Importantes</h6>
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <strong>Tipo de Solicitação:</strong>
                            <div class="text-muted small">
                                Selecione o tipo adequado no formulário: "Correção de bug", "Melhoria", "Alteração de dados" ou "Outros"
                            </div>
                        </li>
                        <li class="mb-3">
                            <strong>Título Descritivo:</strong>
                            <div class="text-muted small">
                                Use um título claro e objetivo (ex: "Erro ao salvar cotação" ou "Adicionar filtro por data")
                            </div>
                        </li>
                        <li class="mb-3">
                            <strong>Descrição Detalhada:</strong>
                            <div class="text-muted small">
                                Explique o problema ou solicitação com o máximo de detalhes possível
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">🔍 Para Problemas (Bugs)</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-primary me-2"></i>
                            Descreva o que estava tentando fazer
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-primary me-2"></i>
                            Informe o que aconteceu (erro ou comportamento inesperado)
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-primary me-2"></i>
                            Inclua screenshots se possível
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-primary me-2"></i>
                            Informe qual navegador está usando
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-primary me-2"></i>
                            Mencione se o problema é recorrente
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="alert alert-light border-start border-primary border-4" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="bi bi-lightbulb text-primary me-3 fs-4"></i>
                    </div>
                    <div>
                        <h6 class="alert-heading fw-bold">Dica Importante</h6>
                        <p class="mb-2">
                            Quanto mais informações você fornecer, mais rápido conseguiremos resolver seu problema ou implementar sua sugestão.
                        </p>
                        <p class="mb-0 small text-muted">
                            Nossa equipe técnica analisará sua solicitação e entrará em contato quando necessário.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.modern-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.card-header {
    border-radius: 1rem 1rem 0 0 !important;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1.1rem;
    border-radius: 0.75rem;
    font-weight: 600;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.display-1 {
    font-size: 4rem;
    opacity: 0.8;
}

.alert {
    border-radius: 0.75rem;
}

.list-unstyled li {
    padding: 0.25rem 0;
}
</style>
@endsection