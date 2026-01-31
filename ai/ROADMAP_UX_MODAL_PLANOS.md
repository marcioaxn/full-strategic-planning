# Roadmap: Melhoria de UX/UI do Modal de Planos de Ação

## Objetivo
Melhorar em 95% a usabilidade e estética do modal de cadastro de planos, focando em acessibilidade, clareza e feedback visual.

## Contexto
- **Arquivo View:** `resources/views/livewire/plano-acao/listar-planos.blade.php`
- **Arquivo Componente:** `app/Livewire/ActionPlan/ListarPlanos.php`
- **Libs Disponíveis:** Bootstrap 5, AlpineJS + Mask Plugin.

## Plano de Execução

### Fase 1: Estrutura e Layout
- [x] Alterar tamanho do modal para `modal-xl`.
- [x] Refatorar layout interno para usar Grid System mais espaçado.
- [x] Adicionar seção colapsável para campos governamentais (PPA/LOA) para limpar a interface para empresas privadas.

### Fase 2: Inputs Inteligentes
- [x] **Objetivos:** Agrupar opções por "Perspectiva" usando `<optgroup>` ou separadores visuais.
- [x] **Datas:** Criar componente visual "Período de Vigência" unindo Início e Fim com validação visual imediata (AlpineJS).
- [x] **Status:** Colorir as opções do `<select>` ou criar um componente customizado com `badgies` coloridas.
- [x] **Moeda:** Implementar máscara BRL usando `@alpinejs/mask` (`x-mask:money`) ou formatador customizado.

### Fase 3: Funcionalidade IA
- [x] Corrigir botão "Sugerir com IA" para dar feedback de "Gerando..." e expandir a área de sugestão corretamente.
- [x] Garantir que o erro "Selecione um objetivo" seja visível se o usuário clicar sem contexto.

### Fase 4: Feedback e Validação
- [x] Adicionar mensagens de erro (`@error`) abaixo de *todos* os campos.
- [x] Melhorar mensagem de sucesso/erro ao salvar.

## Status de Execução
- [x] Concluído

## Resumo das Alterações
- Modal expandido para `modal-xl` com layout mais limpo e moderno.
- Agrupamento de Objetivos por Perspectiva no Select.
- Implementação de máscara de moeda BRL usando AlpineJS.
- Componente de vigência visual com cálculo de duração.
- Indicadores visuais de cor para o Status.
- Seção colapsável para dados governamentais (PPA/LOA).
- Feedback visual aprimorado para validação e ações da IA.

## Arquivos Alterados
- `resources/views/livewire/plano-acao/listar-planos.blade.php`
- `app/Livewire/ActionPlan/ListarPlanos.php`