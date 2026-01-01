# Roadmap: CRUD de Objetivo Estratégico (Nova Entidade)

## Missão
Construir um CRUD completo para a **nova** entidade **Objetivo Estratégico**. 
**Nota Crítica:** Esta é uma nova tabela (`pei.tab_objetivo_estrategico`), que não se confunde com a tabela de "Objetivos" (`pei.tab_objetivo`) já existente no sistema.

## Status das Atividades

### 1. Banco de Dados & Modelagem
- [x] Criar Migration para a NOVA tabela `pei.tab_objetivo_estrategico` - ✅
- [x] Criar Model `App\Models\PEI\ObjetivoEstrategico` apontando para a nova tabela - ✅
- [x] Criar Seeder `ObjetivoEstrategicoSeeder` para dados iniciais - ✅

### 2. Backend (Lógica Livewire)
- [x] Criar Componente Livewire `App\Livewire\PEI\GerenciarObjetivosEstrategicos` - ✅
- [x] Implementar Listagem com filtros por PEI e Organização - ✅
- [x] Implementar Modal de Criação/Edição com as validações pertinentes - ✅
- [x] Implementar exclusão lógica (Soft Delete) - ✅

### 3. Frontend (Interface & UX)
- [x] Criar View Blade seguindo rigorosamente o padrão Material Design/Bootstrap do projeto - ✅
- [x] Adicionar item "Objetivos Estratégicos" no Sidebar - ✅
- [x] Implementar Toasts de confirmação e alertas de erro - ✅

### 4. Rotas & Permissões
- [x] Registrar rota em `routes/web.php` - ✅
- [ ] Criar `ObjetivoEstrategicoPolicy` para controle de acesso - ⏳ (Opcional, seguindo padrão de outros CRUDs se necessário)

---
**STATUS FINAL: CONCLUÍDO**
