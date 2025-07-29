@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Produtos</h1>
        <p class="text-muted mb-0">Gerencie os produtos de seguro disponíveis</p>
    </div>
    <a href="{{ route('produtos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Novo Produto
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

<!-- Filtros -->
<div class="modern-card p-4 mb-4">
    <form method="GET" action="{{ route('produtos.index') }}" class="row g-3" id="filtroForm">
        <div class="col-md-4">
            <label for="search" class="form-label">Buscar produto</label>
            <input type="text" 
                   class="form-control" 
                   id="search" 
                   name="search" 
                   value="{{ request('search') }}" 
                   placeholder="Nome ou descrição...">
        </div>
        <div class="col-md-3">
            <label for="linha" class="form-label">Linha</label>
            <!-- Select com Busca Customizado -->
            <div class="select-search-container">
                <input type="hidden" name="linha" id="linha-hidden" value="{{ request('linha') }}">
                <div class="select-search" id="linha-select">
                    <input type="text" 
                           class="form-control select-search-input" 
                           placeholder="Selecionar linha..." 
                           readonly
                           value="{{ request('linha') ? request('linha') : '' }}">
                    <div class="select-search-arrow">
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="select-search-dropdown">
                        <div class="select-search-item" data-value="">
                            <span>Todas as linhas</span>
                        </div>
                        @foreach($linhas as $linha)
                            <div class="select-search-item" data-value="{{ $linha }}">
                                <span>{{ $linha }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search me-1"></i>Filtrar
            </button>
            <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Limpar
            </a>
        </div>
    </form>
</div>

<!-- Lista de Produtos -->
<div class="modern-card">
    @if($produtos->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produto</th>
                        <th>Linha</th>
                        <th>Seguradoras</th>
                        <th>Cotações</th>
                        <th>Criado em</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produtos as $produto)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="bi bi-box-seam text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $produto->nome }}</div>
                                        @if($produto->descricao)
                                            <small class="text-muted">
                                                {{ Str::limit($produto->descricao, 50) }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($produto->linha)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        {{ $produto->linha }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    {{ $produto->seguradoras->count() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    {{ $produto->cotacoes->count() }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $produto->created_at->format('d/m/Y') }}
                                </small>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('produtos.show', $produto) }}">
                                                <i class="bi bi-eye me-2"></i>Visualizar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('produtos.edit', $produto) }}">
                                                <i class="bi bi-pencil me-2"></i>Editar
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('produtos.destroy', $produto) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este produto?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Excluir
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($produtos->hasPages())
            <div class="p-4 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $produtos->firstItem() }} a {{ $produtos->lastItem() }} 
                        de {{ $produtos->total() }} produtos
                    </div>
                    {{ $produtos->links() }}
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h5 class="mt-3 text-muted">Nenhum produto encontrado</h5>
            <p class="text-muted">
                @if(request()->hasAny(['search', 'linha']))
                    Tente ajustar os filtros ou 
                    <a href="{{ route('produtos.index') }}">limpar a busca</a>
                @else
                    Comece cadastrando seu primeiro produto
                @endif
            </p>
            @if(!request()->hasAny(['search', 'linha']))
                <a href="{{ route('produtos.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Criar Primeiro Produto
                </a>
            @endif
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

.modern-card:hover {
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: #64748b;
    border-bottom: 2px solid #f1f5f9;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
    border-radius: 0.75rem;
}

/* Select Search Styles */
.select-search-container {
    position: relative;
}

.select-search {
    position: relative;
    cursor: pointer;
}

.select-search-input {
    cursor: pointer;
    background: white;
    padding-right: 40px;
}

.select-search-input:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    border-color: #86b7fe;
}

.select-search-arrow {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #6c757d;
    transition: transform 0.2s ease;
}

.select-search.active .select-search-arrow {
    transform: translateY(-50%) rotate(180deg);
}

.select-search-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    z-index: 1000;
    max-height: 250px;
    overflow-y: auto;
    display: none;
}

.select-search.active .select-search-dropdown {
    display: block;
}

.select-search-item {
    padding: 8px 12px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid #f8f9fa;
}

.select-search-item:last-child {
    border-bottom: none;
}

.select-search-item:hover {
    background-color: #f8f9fa;
}

.select-search-item.selected {
    background-color: #e7f3ff;
    color: #0066cc;
    font-weight: 500;
}

.select-search-item.hidden {
    display: none;
}

/* Super específico para sobrescrever Tailwind */
.pagination svg.w-5.h-5,
.pagination .w-5.h-5,
svg.w-5.h-5 {
    width: 16px !important;
    height: 16px !important;
}

/* Reset das classes Tailwind problemáticas */
.pagination .w-5 {
    width: 16px !important;
}

.pagination .h-5 {
    height: 16px !important;
}

/* Força bruta total */
.pagination svg[class*="w-"],
.pagination svg[class*="h-"] {
    width: 16px !important;
    height: 16px !important;
}

/* Responsivo */
@media (max-width: 768px) {
    .select-search-dropdown {
        position: fixed;
        left: 10px;
        right: 10px;
        top: auto;
        max-height: 200px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectSearch = document.getElementById('linha-select');
    const input = selectSearch.querySelector('.select-search-input');
    const dropdown = selectSearch.querySelector('.select-search-dropdown');
    const hiddenInput = document.getElementById('linha-hidden');
    const items = selectSearch.querySelectorAll('.select-search-item');
    
    let isSearchMode = false;
    
    // Toggle dropdown
    selectSearch.addEventListener('click', function(e) {
        if (e.target === input && !isSearchMode) {
            toggleDropdown();
        }
    });
    
    // Input events
    input.addEventListener('focus', function() {
        if (!selectSearch.classList.contains('active')) {
            toggleDropdown();
        }
    });
    
    input.addEventListener('input', function() {
        isSearchMode = true;
        input.style.cursor = 'text';
        filterItems(this.value);
    });
    
    input.addEventListener('blur', function() {
        // Delay maior para permitir seleção
        setTimeout(() => {
            if (!selectSearch.querySelector('.select-search-item:hover')) {
                closeDropdown();
            }
        }, 300);
    });
    
    // Item selection
    items.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const value = this.dataset.value;
            const text = this.querySelector('span').textContent;
            
            // Update inputs
            hiddenInput.value = value;
            input.value = value === '' ? '' : text;
            
            // Update visual selection
            items.forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            
            // Close dropdown
            closeDropdown();
            
            // Submit form automatically
            document.getElementById('filtroForm').submit();
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!selectSearch.contains(e.target)) {
            closeDropdown();
        }
    });
    
    function toggleDropdown() {
        selectSearch.classList.toggle('active');
        if (selectSearch.classList.contains('active')) {
            input.removeAttribute('readonly');
            input.style.cursor = 'text';
            isSearchMode = true;
            
            // Focus and select text for search
            setTimeout(() => {
                input.focus();
                if (input.value && input.value !== '') {
                    input.select();
                }
            }, 100);
        }
    }
    
    function closeDropdown() {
        selectSearch.classList.remove('active');
        input.setAttribute('readonly', 'readonly');
        input.style.cursor = 'pointer';
        isSearchMode = false;
        
        // Reset filter
        items.forEach(item => {
            item.classList.remove('hidden');
        });
        
        // If no selection was made, restore original value
        if (input.value === '' || input.value === 'Selecionar linha...') {
            const selectedValue = hiddenInput.value;
            if (selectedValue === '') {
                input.value = '';
            } else {
                const selectedItem = document.querySelector(`[data-value="${selectedValue}"]`);
                if (selectedItem) {
                    input.value = selectedItem.querySelector('span').textContent;
                }
            }
        }
    }
    
    function filterItems(searchTerm) {
        const term = searchTerm.toLowerCase();
        
        items.forEach(item => {
            const text = item.querySelector('span').textContent.toLowerCase();
            if (text.includes(term)) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });
    }
    
    // Initialize selected item
    const currentValue = hiddenInput.value;
    if (currentValue) {
        const selectedItem = document.querySelector(`[data-value="${currentValue}"]`);
        if (selectedItem) {
            selectedItem.classList.add('selected');
            input.value = selectedItem.querySelector('span').textContent;
        }
    }
});
</script>
@endsection