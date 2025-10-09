@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Importar Apólices</h1>
            <p class="text-muted mb-0">Faça upload de arquivo Excel com dados das apólices</p>
        </div>
        <a href="{{ route('apolices.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
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
        <!-- Card de Upload -->
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-upload me-2"></i>
                        Upload do Arquivo
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('apolices.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf

                        <div class="mb-4">
                            <label for="arquivo" class="form-label">
                                Arquivo Excel <span class="text-danger">*</span>
                            </label>
                            <div class="upload-area" onclick="document.getElementById('arquivo').click()">
                                <div class="upload-content">
                                    <i class="bi bi-cloud-upload display-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">Clique para selecionar o arquivo</h5>
                                    <p class="text-muted mb-0">
                                        Formatos aceitos: .xlsx, .xls, .csv<br>
                                        Tamanho máximo: 10MB
                                    </p>
                                </div>
                                <input type="file" 
                                       class="form-control d-none @error('arquivo') is-invalid @enderror" 
                                       id="arquivo" 
                                       name="arquivo" 
                                       accept=".xlsx,.xls,.csv"
                                       required>
                            </div>
                            <div class="file-info mt-2" id="fileInfo" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                                    <span id="fileName"></span>
                                    <span class="text-muted" id="fileSize"></span>
                                </div>
                            </div>
                            @error('arquivo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="metodo_matching" class="form-label">Método de Matching</label>
                            <select class="form-select @error('metodo_matching') is-invalid @enderror" 
                                    id="metodo_matching" 
                                    name="metodo_matching">
                                <option value="importacao_manual" {{ old('metodo_matching') == 'importacao_manual' ? 'selected' : '' }}>
                                    Importação Manual
                                </option>
                                <option value="cnpj_segurado" {{ old('metodo_matching') == 'cnpj_segurado' ? 'selected' : '' }}>
                                    Por CNPJ do Segurado
                                </option>
                                <option value="cnpj_corretor" {{ old('metodo_matching') == 'cnpj_corretor' ? 'selected' : '' }}>
                                    Por CNPJ do Corretor
                                </option>
                                <option value="numero_apolice" {{ old('metodo_matching') == 'numero_apolice' ? 'selected' : '' }}>
                                    Por Número da Apólice
                                </option>
                            </select>
                            <div class="form-text">
                                Método usado para tentar associar dados importados com registros existentes
                            </div>
                            @error('metodo_matching')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('apolices.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-upload me-2"></i>Importar Arquivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card de Instruções -->
        <div class="col-lg-4">
            <div class="modern-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Formato do Arquivo
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Colunas Esperadas:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>A:</strong> Número da Apólice
                        </li>
                        <li class="mb-2">
                            <strong>B:</strong> Nome do Segurado
                        </li>
                        <li class="mb-2">
                            <strong>C:</strong> CNPJ do Segurado
                        </li>
                        <li class="mb-2">
                            <strong>D:</strong> Nome do Corretor
                        </li>
                        <li class="mb-2">
                            <strong>E:</strong> CNPJ do Corretor
                        </li>
                        <li class="mb-2">
                            <strong>F:</strong> Prêmio Líquido
                        </li>
                        <li class="mb-2">
                            <strong>G:</strong> Data de Emissão
                        </li>
                        <li class="mb-2">
                            <strong>H:</strong> Início da Vigência
                        </li>
                        <li class="mb-2">
                            <strong>I:</strong> Fim da Vigência
                        </li>
                        <li class="mb-2">
                            <strong>J:</strong> Ramo
                        </li>
                        <li class="mb-2">
                            <strong>K:</strong> Endosso
                        </li>
                    </ul>
                </div>
            </div>

            <div class="modern-card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Observações Importantes
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            A primeira linha deve conter os cabeçalhos
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Datas no formato dd/mm/aaaa
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Valores com vírgula como separador decimal
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            CNPJs podem ter ou não formatação
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-exclamation-circle text-warning me-2"></i>
                            Registros duplicados serão ignorados
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-exclamation-circle text-warning me-2"></i>
                            Dados inválidos serão reportados ao final
                        </li>
                    </ul>
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
}

.card-header {
    border-radius: 1rem 1rem 0 0 !important;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 1rem;
    padding: 3rem 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
}

.upload-area:hover {
    border-color: #0d6efd;
    background-color: #f0f7ff;
}

.upload-area.dragover {
    border-color: #0d6efd;
    background-color: #e7f3ff;
}

.upload-content {
    pointer-events: none;
}

.loading-spinner {
    display: none;
}

.loading .loading-spinner {
    display: inline-block;
}

.loading .btn-text {
    display: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('arquivo');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const uploadArea = document.querySelector('.upload-area');
    const form = document.getElementById('importForm');
    const submitBtn = document.getElementById('submitBtn');

    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            showFileInfo(file);
        }
    });

    // Handle drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            
            // Check file type
            const allowedTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'application/vnd.ms-excel', // .xls
                'text/csv' // .csv
            ];
            
            if (allowedTypes.includes(file.type) || file.name.match(/\.(xlsx|xls|csv)$/i)) {
                fileInput.files = files;
                showFileInfo(file);
            } else {
                alert('Por favor, selecione um arquivo Excel (.xlsx, .xls) ou CSV.');
            }
        }
    });

    function showFileInfo(file) {
        fileName.textContent = file.name;
        fileSize.textContent = '(' + formatBytes(file.size) + ')';
        fileInfo.style.display = 'block';
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Handle form submission
    form.addEventListener('submit', function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('Por favor, selecione um arquivo para importar.');
            return;
        }

        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2 loading-spinner" role="status"></span>
            <span class="btn-text">Importando...</span>
        `;
        submitBtn.disabled = true;
    });
});
</script>
@endsection