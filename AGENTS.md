# AGENTS.md - Acordo Mestre de Atuação (Codex)

## 0. Postura de Estado (Prioridade Máxima)
> **PROJETO:** Plataforma Visão 360 - Módulo Integra+  
> **STACK:** Laravel 12 | Livewire 4 | PostgreSQL  
> **CONTEXTO DE GOVERNO:** Este projeto é uma ação de Estado voltada à população necessitada. Falhas, regressões ou designs infantis não são apenas erros técnicos; são desrespeitos ao cidadão.
>
> 1. **Seriedade Absoluta:** padrão visual e lógico Corporate High-Fidelity (sóbrio, seguro e acessível).
> 2. **Integridade Sagrada:** nunca remover conteúdo validado (guias, editais, regras) para simplificar.
> 3. **Excelência Técnica:** cada linha impacta política pública real; se não for robusto, não serve.

---

## 1. Filosofia de Trabalho e Metodologia

### 1.1 Análise meticulosa
- Fazer análise profunda de contexto, dependências e restrições de ambiente antes de intervir.
- Priorizar soluções robustas e arquiteturalmente sólidas em vez de correções apressadas.
- Declarar explicitamente quando a análise for parcial.

### 1.2 Planejamento e documentação (mandatório)
- Para toda atividade (bugfix, feature ou análise), criar roadmap em `documentacao` com: Contexto, Diagnóstico, Plano de Execução e Rollback.
- Após cada modificação, registrar intervenção em `gemini/interventions.txt`.
- Commits devem referenciar roadmaps e explicar o porquê da mudança.

---

## 2. Premissas de Execução e Segurança

### 2.1 Fazer somente o pedido
- Não alterar código, remover métodos ou realizar limpezas fora do pedido explícito.
- Sugestões de melhoria devem ser apresentadas para aprovação prévia.

### 2.2 Não-regressão e integridade
- **Precisão cirúrgica (mandatório):** alterar apenas o local e escopo solicitados.
- **Persistência atômica e imediata (mandatório):** toda intervenção bem-sucedida deve ser seguida de commit detalhado.
- Blocos marcados com `[VALIDATED]` (Upload, CNPJ, Mapa etc.) são intocáveis.
- É proibido deletar/modificar código, rotas ou configurações sem relação direta com a tarefa.
- Salvar backup preventivo do estado original de qualquer arquivo antes de modificar.
- Nunca executar `git reset --hard`, `git clean` ou rollback agressivo sem aprovação explícita e rastreável do usuário.

### 2.3 Segurança de dados e backend
- Em testes, é proibido usar `migrate:fresh` ou `RefreshDatabase` para evitar limpeza indesejada do banco.

### 2.4 Leis de Robótica do Projeto (10/02/2026)
> **RETROACTIVE GUARDIAN:** Não tocar, exceto com extremo cuidado e backups manuais, nas áreas:
> 1. Cadastro do Proponente
> 2. Cadastro do Financiador
> 3. Validação de Cadastro
> 4. Validação de Acesso

### 2.5 Humanized Validation & Real-Time Protocol (mandatório)
- Nunca retornar mensagens genéricas como “The field is required”.
- Sempre implementar `messages()` em Livewire/FormRequests com frases humanizadas, objetivas e educadas.
- Sempre implementar `updated($propertyName)` em Livewire com `$this->validateOnly($propertyName)` para validação em tempo real.
- Toda interação do usuário (cliques, navegação, filtros, salvamento) deve disparar feedback visual imediato (spinner, loading bar ou mudança de estado).
- No frontend, usar `wire:model.blur` para performance + UX.
- Exibir feedback positivo (`is-valid` + ícone de check) quando o campo estiver correto.
- Referência padrão: `RegisterProponente.php`.

### 2.6 Modo Strict & Preservação Estrutural (DBA e Frontend)
1. **Dump prévio obrigatório (DBA):** antes de propor/criar/rodar DDL, migration ou alteração de tabela, ler e espelhar a estrutura atual exata (PK, FK e índices).
2. **Preservação estrutural (Frontend):** antes de alterar Blade/Livewire/SCSS, ler classes, componentes e lógicas validadas já existentes para não degradar a UI/UX.
3. **Aprovação explícita para ações sensíveis:** qualquer comando com potencial de impacto em ambiente, build, dependências, estrutura ou dados deve ser previamente aprovado pelo usuário.

### 2.7 Fail-Safe Protocol
Se a instrução de fail-safe for ativada (“Apenas leia e planeje... aguarde PROSSIGA”), o agente deve interromper imediatamente ações de execução e atuar somente como leitor/planejador até receber `PROSSIGA`.

---

## 3. Diretrizes de UI/UX e Frontend

### 3.1 Padrão premium (modern & high-density)
- Proibido usar placeholders `[...]`.
- Interfaces devem ser ricas, informativas e com feedback visual imediato.
- Uso obrigatório de classes adaptativas quando aplicável (ex.: `section-subtle`, `icon-adaptive`).

### 3.2 Padrão educativo
- Formulários Livewire devem seguir o guia de referência, incluindo “Antes vs Depois” e orientações estratégicas ao usuário.

### 3.3 Transparência radical (UI)
- Informar claramente alterações dinâmicas no fluxo de formulários (campos condicionais) via alertas ou animações.

### 3.4 Rigor CSS/SCSS
- Proibido inline CSS; estilos devem residir no SCSS global.
- Evitar classes genéricas (`.btn`) em contextos onde a especificidade do projeto exige isolamento.

---

## 4. Protocolo de Comunicação e Ferramentas

### 4.1 Idioma e tom
- Língua: Português do Brasil (pt-BR).
- Tom: profissional, direto e assertivo.
- Explicar estratégia e intenção em uma frase antes de executar qualquer ferramenta.

### 4.2 Fluxo Git (protocolo de fechamento)
1. Analisar status.
2. Criar branch contextual.
3. Adicionar arquivos.
4. Commit detalhado com referência aos roadmaps.
5. Push para remoto.

---

## 5. Especificidades Técnicas (PostgreSQL/Laravel)
- Em PostgreSQL/Laravel, para `VARCHAR` ilimitado, usar `->text()` ou `->specificType('column', 'VARCHAR')`.  
  `->string()` sem parâmetro resulta em `VARCHAR(255)`.
- Uso obrigatório de `<x-breadcrumb :items="...">` em páginas com navegação hierárquica.

---

**Compromisso:** atuação com máxima integridade, rastreabilidade e segurança operacional.
