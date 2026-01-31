# Roadmap: Refinamento Premium UX do Modal de Planos de Ação

## Objetivo
Elevar o nível da interface para "Premium Profissional", corrigindo bugs e implementando padrões de UX de alta qualidade (estilo "Agência de Viagens").

## Contexto
- **Arquivo View:** `resources/views/livewire/plano-acao/listar-planos.blade.php`
- **Requisitos:** Flatpickr (Range), Máscara Manual, Reordenação, Paleta de Cores Invertida.

## Plano de Execução

### 1. Reestruturação e Cores
- [x] **Backgrounds:** Inverter a lógica atual.
    - Modal Body: `bg-white`.
    - Cards Internos: `bg-light` (Cinza suave #F8F9FA).
    - Inputs: `bg-white` com borda suave.
- [x] **Reordenação:** Mover "Objetivo Estratégico Vinculado" para o topo do formulário (antes da descrição).

### 2. Funcionalidade IA (Dependência)
- [x] Adicionar aviso visual de que a IA depende do Objetivo.
- [x] Desabilitar botão ou mostrar tooltip se nenhum objetivo estiver selecionado.
- [x] Mover botão para próximo do campo de Objetivo para reforçar o contexto.

### 3. Correção: Input de Moeda
- [x] Remover `x-mask:money` (que causou o glitch `[{ decim]`).
- [x] Implementar função AlpineJS `formatCurrency` nativa para manipulação de `input`.

### 4. Upgrade: Vigência (Estilo "Passagem Aérea")
- [x] Importar **Flatpickr** (CDN) para garantir a funcionalidade de range.
- [x] Criar componente AlpineJS wrapper para o Flatpickr.
- [x] Configurar modo `range` (Seleção de Ida e Volta).
- [x] Sincronizar o range selecionado com as variáveis Livewire `$dte_inicio` e `$dte_fim`.

## Status de Execução
- [x] Concluído

## Resumo
O modal foi transformado para o nível Premium Profissional com as seguintes características:
- Paleta de cores moderna (Fundo branco / Cards cinzas).
- DatePicker estilo "Agência de Viagem" usando Flatpickr Range.
- Input de Moeda manual (robusto e sem dependências extras).
- UX aprimorada para a Inteligência Artificial (contextual e dependente do Objetivo).