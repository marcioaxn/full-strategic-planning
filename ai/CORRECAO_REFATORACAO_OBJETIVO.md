# Roadmap de Correção e Refatoração: Objetivo Estratégico -> Objetivo

## Contexto
O sistema apresenta erro `Undefined array key "nom_objetivo_estrategico"` na interface pública. Houve uma tentativa anterior de renomear "Objetivo Estratégico" para "Objetivo" que resultou em inconsistência entre Banco de Dados e Código.

## Diagnóstico Inicial
- [x] Verificar estado atual da tabela no banco de dados (via migrations existentes).
- [x] Identificar arquivos que ainda referenciam `nom_objetivo_estrategico`.
- [x] Verificar se o Model foi renomeado ou alterado.

## Plano de Ação

### 1. Banco de Dados
- [x] Criar migration para renomear coluna `nom_objetivo_estrategico` para `nom_objetivo` (se ainda não existir).
- [x] (Opcional/Confirmar) Renomear tabela `tab_objetivo_estrategico` para `tab_objetivo`.
- [x] Executar migration.

### 2. Backend (Models & Eloquent)
- [x] Atualizar Model `ObjetivoEstrategico.php` (provavelmente renomear para `Objetivo.php`).
- [x] Atualizar referências de `fillable` e atributos no Model.
- [x] Atualizar relacionamentos em outros Models (`Risco`, `Indicador`, etc.) que apontam para este model.

### 3. Frontend & Controllers
- [x] Buscar e substituir `nom_objetivo_estrategico` por `nom_objetivo` em:
    - [x] Views Blade (Mapa Estratégico, Listas, Relatórios, Contexto).
    - [x] Componentes Livewire (Mapa, ListarObjetivos, Riscos, Indicadores).
    - [x] Controllers/Exports (Riscos, Indicadores, Planos).
- [x] Verificar atribuições de array (causa provável do erro atual).

### 4. Testes
- [x] Verificar página pública (onde o erro foi reportado) - FIXADO.
- [x] Verificar CRUD de Objetivos no painel administrativo - FIXADO.
- [x] Verificar Relatórios e Listagens secundárias - FIXADO.

**STATUS: DONE**