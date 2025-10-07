# Documentação de Arquitetura - Sistema de Gestão de Seguros

**Versão:** 2.0  
**Tipo:** Documentação Pública

---

## 🔧 Stack Tecnológica

### Backend
- **Framework:** Laravel 8.75+
- **PHP:** 8.x
- **Banco de Dados:** MySQL/MariaDB
- **ORM:** Eloquent
- **Migrations:** Versionamento de schema

### Frontend
- **Template Engine:** Blade Templates
- **CSS Framework:** Bootstrap 5
- **Ícones:** Bootstrap Icons
- **JavaScript:** Vanilla JS
- **Design:** Interface responsiva moderna

### Autenticação & Autorização
- **Auth:** Laravel Breeze/UI
- **Permissões:** Spatie Laravel Permission
- **Middleware:** Custom middleware para validações
- **Policies:** Laravel Policy-based Authorization

### Auditoria & Logs
- **Biblioteca:** Spatie Laravel ActivityLog
- **Trait:** LogsActivity
- **Retenção:** Configurável

### Ambiente & Deployment
- **Containerização:** Docker
- **Orquestração:** Docker Compose
- **Dev Environment:** Laravel Sail

### Dependências Principais
```json
{
  "spatie/laravel-activitylog": "auditoria completa",
  "spatie/laravel-permission": "roles e permissões",
  "laravel/sail": "ambiente Docker",
  "doctrine/dbal": "manipulação de schema",
  "guzzlehttp/guzzle": "HTTP client"
}
```

---

## 🏗️ Arquitetura

### Padrões Implementados
- **MVC Pattern** (Model-View-Controller)
- **Repository Pattern** (via Eloquent)
- **Policy-based Authorization** (isolamento por roles)
- **RESTful Routes** (padrão de rotas)
- **Multi-tenant by Roles**

### Estrutura de Pastas

```
app/
├── Console/Commands/        # Comandos artisan customizados
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Controllers administrativos
│   │   ├── [Controllers principais]
│   ├── Middleware/         # Middlewares customizados
│   └── Policies/           # Políticas de autorização
├── Models/                 # Modelos Eloquent
config/                     # Configurações
database/
├── migrations/             # Versionamento do banco
└── seeders/               # Dados iniciais
resources/
└── views/                  # Views Blade
docker/                     # Configuração Docker
```

---

## 🔐 Sistema de Permissões

### Roles Disponíveis

| Role | Nível | Descrição |
|------|-------|-----------|
| **Admin** | Total | Acesso completo ao sistema |
| **Diretor** | Supervisão | Visualização ampla, edição restrita |
| **Comercial** | Operacional | Acesso limitado aos próprios recursos |

### Middleware de Rotas

```php
// Exemplo de proteção de rotas
Route::middleware(['auth'])->group(function () {
    // Rotas autenticadas
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Rotas administrativas
});
```

---

## 📊 Models e Relacionamentos

### Estrutura Principal

- **User** → Gerencia autenticação e permissões
- **Corretora** → Entidade de negócio principal
- **Seguradora** → Parceiros do sistema
- **Produto** → Produtos oferecidos
- **Cotacao** → Core business do sistema
- **Segurado** → Clientes finais

### Tabelas Pivot
- Relacionamentos many-to-many com auditoria
- Models pivot customizados para lógica adicional

---

## 📝 Sistema de Auditoria

### Configuração
- **Biblioteca:** Spatie Laravel ActivityLog
- **Funcionalidades:**
  - Rastreamento de todas as alterações
  - Identificação de usuário responsável
  - Timestamps de todas as ações
  - Visualização formatada

### Implementação

```php
use Spatie\Activitylog\Traits\LogsActivity;

class Model extends Model
{
    use LogsActivity;
    
    // Configuração de auditoria
}
```

---

## 🎯 Padrões de Código

### Nomenclatura

| Contexto | Padrão | Exemplo |
|----------|--------|---------|
| **Tabelas BD** | snake_case plural | users, products |
| **Classes PHP** | PascalCase | UserController |
| **Métodos PHP** | camelCase | getUserData() |
| **Arquivos Blade** | kebab-case | index.blade.php |

### Segurança em Camadas

1. **Route Level** - Middleware de autenticação
2. **Controller Level** - Autorização de recursos
3. **Policy Level** - Regras de negócio
4. **Model Level** - Proteção de atribuição em massa

### ⚠️ Paginação com Filtros

```php
// Sempre usar withQueryString() para manter filtros
$items = $query->paginate(10)->withQueryString();
```

---

## ✨ Funcionalidades

### Core Business
- Sistema de cotações multi-parceiro
- Workflow de aprovação
- Timeline de atividades
- Exportação de relatórios

### Gestão
- CRUD completo de entidades
- Sistema de atribuições hierárquicas
- Filtros avançados com persistência

### Segurança
- Multi-tenant por roles
- Policies granulares
- Isolamento de dados
- Auditoria completa

---

## 🚀 Guia de Desenvolvimento

### Criando Nova Feature

1. **Model** - Definir entidade e relacionamentos
2. **Migration** - Criar estrutura no banco
3. **Policy** - Implementar regras de autorização
4. **Controller** - Lógica de negócio
5. **View** - Interface do usuário
6. **Rotas** - Definir endpoints com middleware

### Comandos Úteis

```bash
# Docker/Sail
./vendor/bin/sail up -d         # Iniciar containers
./vendor/bin/sail artisan tinker # Console interativo
./vendor/bin/sail test          # Rodar testes

# Artisan
php artisan migrate              # Rodar migrations
php artisan route:list          # Listar rotas
php artisan cache:clear         # Limpar cache

# Git
git add .
git commit -m "tipo: descrição"
git push origin branch-name
```

---

## 🌍 Ambientes

### Desenvolvimento (Docker/Sail)
- Container MySQL
- Container PHP
- Hot reload ativo

### Produção
- Servidor otimizado
- Cache habilitado
- Queue workers ativos

---

## 🔮 Roadmap

### Integrações Preparadas
- API REST com Sanctum
- Sistema de Queue
- Notificações
- Cache avançado
- Storage distribuído

### Features Planejadas
- [ ] API para integrações
- [ ] Dashboard analytics
- [ ] Notificações real-time
- [ ] Operações em lote
- [ ] Mobile app

---

## ⚠️ Boas Práticas

### SEMPRE
- ✅ Usar `withQueryString()` em paginações
- ✅ Implementar auditoria em dados críticos
- ✅ Testar antes de deploy
- ✅ Implementar Policy para novos recursos
- ✅ Documentar mudanças significativas

### NUNCA
- ❌ Commitar credenciais ou `.env`
- ❌ Expor dados sensíveis em logs
- ❌ Deploy sem testes
- ❌ Ignorar validações de segurança

---

## 📚 Referências

- [Laravel 8.x Documentation](https://laravel.com/docs/8.x)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Spatie Activity Log](https://spatie.be/docs/laravel-activitylog)
- [Bootstrap 5](https://getbootstrap.com/docs/5.0)
- [Laravel Sail](https://laravel.com/docs/8.x/sail)