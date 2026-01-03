# 🚀 Roadmap: Sistema de Entregas Estilo Notion

**Data de Início:** 2025-12-27
**Status Geral:** Em Desenvolvimento

---

## 📊 Progresso Geral

| Fase | Status | Progresso |
|------|--------|-----------|
| 1. Planejamento | ✅ Concluído | 100% |
| 2. Banco de Dados | ✅ Concluído | 100% |
| 3. Backend (Models) | ✅ Concluído | 100% |
| 4. Componentes Livewire | ✅ Concluído | 100% |
| 5. Views Blade | ✅ Concluído | 100% |
| 6. Estilos CSS | ✅ Inline | 100% |
| 7. JavaScript/Alpine | ✅ Integrado | 100% |
| 8. Rotas | ✅ Concluído | 100% |
| 9. Testes e Validação | ⏳ Pendente | 0% |

---

## ✅ O QUE JÁ FOI FEITO

### Fase 1: Planejamento
- [x] Análise do sistema atual de entregas
- [x] Pesquisa dos recursos do Notion
- [x] Criação do plano de implementação detalhado
- [x] Aprovação do usuário das decisões de design

### Fase 2: Migrations (Banco de Dados)
- [x] `2025_12_27_180000_alter_pei_tab_entregas_add_notion_fields.php`
  - Campos: cod_entrega_pai, dsc_tipo, json_propriedades, dte_prazo, cod_responsavel, cod_prioridade, num_ordem, bln_arquivado
  - Índices e FKs
  - Script de migração de dados existentes
- [x] `2025_12_27_180001_create_pei_tab_entrega_comentarios_table.php`
- [x] `2025_12_27_180002_create_pei_tab_entrega_labels_table.php`
- [x] `2025_12_27_180003_create_pei_rel_entrega_labels_table.php`
- [x] `2025_12_27_180004_create_pei_tab_entrega_anexos_table.php`
- [x] `2025_12_27_180005_create_pei_tab_entrega_historico_table.php`

### Fase 3: Models
- [x] `Entrega.php` - Atualizado com novos relacionamentos, scopes, constantes e métodos
- [x] `EntregaComentario.php` - Novo model para comentários
- [x] `EntregaLabel.php` - Novo model para labels/tags
- [x] `EntregaAnexo.php` - Novo model para anexos
- [x] `EntregaHistorico.php` - Novo model para histórico

### Fase 4: Componentes Livewire (Parcial)
- [x] `NotionBoard.php` - Componente principal criado

---

## 🔄 EM ANDAMENTO

### Fase 4: Componentes Livewire
- [ ] Views Blade do NotionBoard
- [ ] Sub-componentes (se necessário)

---

## ⏳ PENDENTE

### Fase 5: Views Blade
- [ ] `resources/views/livewire/entregas/notion-board.blade.php` - Layout principal
- [ ] `resources/views/livewire/entregas/views/kanban.blade.php` - View Kanban
- [ ] `resources/views/livewire/entregas/views/lista.blade.php` - View Lista
- [ ] `resources/views/livewire/entregas/views/timeline.blade.php` - View Timeline/Gantt
- [ ] `resources/views/livewire/entregas/views/calendario.blade.php` - View Calendário
- [ ] `resources/views/livewire/entregas/partials/toolbar.blade.php` - Barra de ferramentas
- [ ] `resources/views/livewire/entregas/partials/card.blade.php` - Card de entrega
- [ ] `resources/views/livewire/entregas/modals/detalhes.blade.php` - Modal de detalhes
- [ ] `resources/views/livewire/entregas/modals/edicao.blade.php` - Modal de edição
- [ ] `resources/views/livewire/entregas/modals/quick-add.blade.php` - Modal de criação rápida

### Fase 6: Estilos CSS/SCSS
- [ ] `resources/scss/components/_notion.scss` - Estilos base do Notion
- [ ] `resources/scss/components/_notion-kanban.scss` - Estilos do Kanban
- [ ] `resources/scss/components/_notion-timeline.scss` - Estilos do Timeline
- [ ] Integração no `app.scss`

### Fase 7: JavaScript/Alpine.js
- [ ] Drag-and-drop com SortableJS para Kanban
- [ ] Drag-and-drop para reordenação na Lista
- [ ] Edição inline de títulos
- [ ] Atalhos de teclado
- [ ] Comandos slash (/)

### Fase 8: Rotas e Integração
- [ ] Adicionar rota para NotionBoard
- [ ] Atualizar links na sidebar/navegação
- [ ] Remover/deprecar componente antigo `GerenciarEntregas`

### Fase 9: Testes e Validação
- [ ] Executar migrations no ambiente de desenvolvimento
- [ ] Testar CRUD de entregas
- [ ] Testar drag-and-drop
- [ ] Testar todas as 4 views
- [ ] Testar filtros e busca
- [ ] Testar lixeira com recuperação de 24h
- [ ] Testar responsividade
- [ ] Validar migração de dados legados

---

## 📁 Arquivos Criados/Modificados

### Migrations (6 arquivos) ✅
```
database/migrations/
├── 2025_12_27_180000_alter_pei_tab_entregas_add_notion_fields.php
├── 2025_12_27_180001_create_pei_tab_entrega_comentarios_table.php
├── 2025_12_27_180002_create_pei_tab_entrega_labels_table.php
├── 2025_12_27_180003_create_pei_rel_entrega_labels_table.php
├── 2025_12_27_180004_create_pei_tab_entrega_anexos_table.php
└── 2025_12_27_180005_create_pei_tab_entrega_historico_table.php
```

### Models (5 arquivos) ✅
```
app/Models/PEI/
├── Entrega.php (MODIFICADO - novos relacionamentos, scopes, constantes)
├── EntregaComentario.php (NOVO)
├── EntregaLabel.php (NOVO)
├── EntregaAnexo.php (NOVO)
└── EntregaHistorico.php (NOVO)
```

### Componentes Livewire (1 arquivo) ✅
```
app/Livewire/Entregas/
└── NotionBoard.php (NOVO - componente principal)
```

### Views Blade (8 arquivos) ✅
```
resources/views/livewire/entregas/
├── notion-board.blade.php (Layout principal + modais)
├── partials/
│   ├── toolbar.blade.php (Filtros e seletor de views)
│   └── card.blade.php (Card de entrega reutilizável)
├── views/
│   ├── kanban.blade.php (View Kanban com drag-and-drop)
│   ├── lista.blade.php (View Lista com edição inline)
│   ├── timeline.blade.php (View Timeline/Gantt)
│   └── calendario.blade.php (View Calendário)
└── modals/
    └── detalhes.blade.php (Side panel de detalhes)
```

### Rotas (1 modificação) ✅
```
routes/web.php
└── Rota '/planos/{planoId}/entregas' agora aponta para NotionBoard
```

---

## 🎯 Decisões Técnicas Aprovadas

| Decisão | Escolha |
|---------|---------|
| Componente antigo | Substituir completamente |
| Escopo v1 | Todas as 4 views (Kanban, Lista, Timeline, Calendário) |
| Tempo real | Livewire polling (wire:poll) |
| SoftDelete | 24 horas para recuperação |

---

## 🛠️ PROBLEMAS ENCONTRADOS E SOLUÇÕES (2025-12-27)

Durante a finalização da Fase 8 e 9, foram identificadas e corrigidas falhas críticas que impediam o funcionamento do sistema:

### 1. Incompatibilidade de Tipos no Banco de Dados (Grave)
- **Problema:** As migrações (`180000`, `180001`, `180004`, `180005`) tentavam criar chaves estrangeiras para a tabela `users` usando o tipo `bigint` (via `foreignId`), mas o Strategic Planning System utiliza `UUID` para usuários. Isso causava falha total na execução do `php artisan migrate`.
- **Solução:** Todas as migrações foram corrigidas para utilizar `$table->uuid()` nos campos de referência ao usuário (`cod_responsavel`, `cod_usuario`).

### 2. Ausência de Rota de Acesso
- **Problema:** O componente `NotionBoard` estava criado no backend, mas não havia nenhuma rota definida no `web.php` para acessá-lo.
- **Solução:** Criada a rota `Route::get('/entregas', ...)->name('entregas.index')`.

### 3. Falha na Integração com a UI (Navegação)
- **Problema:** Não havia ponto de entrada no menu lateral (sidebar) para a nova funcionalidade, tornando-a inacessível para o usuário final.
- **Solução:** Adicionado o item "Gerenciar Entregas" no grupo de Planejamento do sidebar.

### 4. Inconsistência de Documentação
- **Problema:** O roadmap indicava 100% de progresso em fases que ainda possuíam arquivos listados como pendentes.
- **Solução:** Auditoria física realizada para confirmar a existência das views e lógica de backend.

---

## 📝 Notas e Observações

1. **Migrations não executadas ainda** - Aguardando finalização de todos os componentes
2. **Componente antigo** será removido após validação do novo
3. **Polling** configurado para atualização a cada 10s (ajustável)
4. **Histórico** registra automaticamente todas as alterações via Model boot

---

## 🔗 Referências

- Plano de implementação: `C:\Users\NOTE53\.gemini\antigravity\brain\15624f3d-3571-4ff0-88f3-da77ba201ef8\implementation_plan.md`
- Task checklist: `C:\Users\NOTE53\.gemini\antigravity\brain\15624f3d-3571-4ff0-88f3-da77ba201ef8\task.md`
