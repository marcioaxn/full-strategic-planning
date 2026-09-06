# Levantamento de pendências — lista original de 15 pedidos

**Data:** 05/09/2026 · **Método:** verificação contra o repositório, arquivo por arquivo.

> Cada linha traz **a evidência**, para você conferir por amostragem sem depender da
> minha palavra. Onde eu **não** verifiquei, está escrito que não verifiquei.

---

## Resumo

| Situação | Itens |
|---|---|
| ✅ Fechado, com guarda automática | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11 |
| 🟡 Parcial | 12, 13, 15 |
| 🔴 Falta um passo seu | 14 |

**Fora da lista, aberto:** um achado de segurança — registrado em `.claude/seguranca-em-aberto.md`, não aqui.

---

## ✅ Fechados

| # | Pedido | Evidência para conferir |
|---|---|---|
| 1 | "Plano de Ação" → "Iniciativas" | `tests/Feature/ActionPlan/VocabularioIniciativasTest.php` — 7 testes, varre `resources/views`, `app` e `routes` procurando o rótulo antigo no texto que chega ao navegador |
| 2 | Tipos de Iniciativa por seeder + serviço para quem já instalou | `database/seeders/TipoExecucaoSeeder.php`, `app/Services/Seeding/VocabularioControlado.php`, `app/Console/Commands/RemanejarTipoIniciativa.php`, `tests/Feature/Seeding/TipoExecucaoSeederTest.php` |
| 3 | Modal "Nova Atividade" mais largo + tipo `[Valores públicos]` | `resources/views/livewire/p-e-i/cadeia-de-valor.blade.php:73` (`modal-lg`); `app/Models/StrategicPlanning/AtividadeCadeiaValor.php:61` (`TIPOS`); `tests/Feature/StrategicPlanning/CadeiaDeValorTiposTest.php` |
| 4 | Ordem das perspectivas no BSC (bottom-up) | `resources/views/livewire/p-e-i/listar-perspectivas.blade.php:577` — a "Visualização no Mapa" passou a desenhar a posição real; `tests/Feature/StrategicPlanning/OrdemDasPerspectivasTest.php`. Em `/objetivos`, 5 usos do nível hierárquico |
| 5 | Vincular Agenda 2030 ao criar Objetivo | `app/Livewire/StrategicPlanning/ListarObjetivos.php:240` (`toggleOds`) e `:471` (`todosOds`) |
| 6 | Grau de Satisfação antes dos Indicadores + texto humanizado | `app/Services/PeiGuidanceService.php:127` aponta para `graus-satisfacao.index`; a permissão do módulo saiu do bloco exclusivo do Super Admin; `tests/Feature/StrategicPlanning/GrauSatisfacaoTest.php` (12 testes) |
| 7 | Mapa Estratégico público em `/`, navegável, sem escrita | `routes/web.php` (grupo `transparencia`), `app/Http/Middleware/TransparenciaPublica.php`, `tests/Feature/Transparencia/` — 18 testes |
| 8 | Quem gerencia/lança evolução de Indicadores, Iniciativas, Entregas | `app/Livewire/Ajuda/PapeisResponsabilidades.php` + `/ajuda/papeis`, derivada da MATRIZ do `CapacidadeResolver` (não envelhece) |
| 9 | Reconstrução da UI dos relatórios | `app/Services/Reports/AcabamentoPdf.php` (renderizador único), `resources/views/relatorios/partials/estilos.blade.php` (sistema de design único), `tests/Feature/Reports/PadronizacaoDosRelatoriosTest.php` — 7 testes |
| 10 | Histórico de relatórios + agendamento | `app/Support/AgendadorDeRelatorios.php`, migration `2026_09_05_223000_add_rota_to_pei_tab_relatorios_gerados.php`, `tests/Feature/Reports/HistoricoEAgendamentoHonestosTest.php` — 7 testes |
| 11 | Erro `[pei]` ao salvar risco + Vínculo Estratégico enterrado | `app/Livewire/RiskManagement/ListarRiscos.php` (regra `exists:users,id`), `tests/Feature/RiskManagement/` — 5 testes, um deles guarda a ORDEM das seções do modal |

---

## 🔴 Item 14 — falta um passo, e é seu

**`CLAUDE.md` está no `.gitignore` (linha 60) e a remoção está no índice do git — mas NÃO
foi commitada.** Enquanto não houver commit e push, o arquivo continua no repositório
remoto, que é público.

```
git status --porcelain CLAUDE.md   →   D  CLAUDE.md
```

**Esforço:** um commit. **É a pendência mais barata e a de maior risco em aberto.**

---

## 🟡 Item 12 — visão holística: estudo feito, 2 dos 7 achados em aberto

O estudo está em `12-visao-holistica-achados-transversais.md`. Estado conferido hoje:

| Achado | Situação hoje | Como conferi |
|---|---|---|
| A1 — Models sem schema qualificado (eram 26 de 51) | ✅ **2 restantes**, e um deles é `app/Models/__teste/Teste.php`, que não deveria existir | `grep -rL "protected \$table = '[a-z_]*\."` em `app/Models` |
| A2 — Farol misturando critérios de PEIs diferentes | ✅ Fechado, com guarda que deriva da assinatura dos métodos | `GrauSatisfacaoTest.php` |
| A5 — `exists:` com schema | ✅ Fechado, com guarda no hook e teste | `tests/Feature/RiskManagement/` |
| A6 — Query dentro de Blade | ✅ Nenhuma ocorrência | varredura em `resources/views` |
| A7 — Arquivo de backup em pasta de produção | ✅ Nenhum `.bak`/`.old` | `find` em `app`, `resources`, `routes` |
| **A3 — Exceção engolida (`catch` sem log)** | 🟡 **ABERTO.** Não consegui medir com confiança neste levantamento — a contagem por `grep` atravessa linhas e devolve número não confiável. **Não vou lhe dar um número que não sei sustentar.** | — |
| **A4 — Cobertura de teste** | 🟡 **ABERTO.** 59 componentes Livewire para 46 arquivos de teste no total (o estudo media 10 componentes citados em teste; hoje é mais, mas não recontei um a um) | `ls` em `app/Livewire` e `tests/Feature` |

**Sobra também:** `app/Models/__teste/Teste.php` — model de teste dentro de `app/Models`.
Esforço: minutos, depois de confirmar que nada o referencia.

---

## 🟡 Item 13 — auditoria de UI navegada: a menor parte foi feita

**Feito:** landing page e mapa público — auditados no Chrome, em 4 tamanhos de tela, e
corrigidos (navbar, hero, seção duplicada, contraste, alinhamento, números que não batiam
com o próprio rótulo).

**Não feito:** as telas autenticadas. São **70 rotas GET** em `routes/web.php`. Dashboard,
indicadores, iniciativas, entregas (Kanban), riscos, usuários, perfis, auditoria,
configurações, relatórios — **nenhuma foi aberta e percorrida.**

É, de longe, o maior item aberto da lista. A ferramenta já existe
(`ferramentas/auditoria-ui/auditoria.mjs`), mas ela mede contraste, alvo de toque e
hierarquia — **não** vê "objeto mal colocado". Isso exige olhar tela por tela.

---

## 🟡 Item 15 — Relatório de Gestão: documento pronto, captura de dados não

**Feito:** PDF e DOCX, duas variantes (modelo oficial completo × só o que está
preenchido), no grid do modelo — A4 paisagem, capa sangrada, cabeçalho com o capítulo
corrente, tabelas 2.2.1 e 2.2.2 com os cabeçalhos do documento da Presidência.
Guarda: `tests/Feature/Reports/RelatorioGestaoTest.php` — 18 testes.

**Não feito**, e é a parte que você pediu sobre autoridades:

- **Cadastro de autoridades** (`tab_autoridade`): dirigente, foto, mensagem de abertura.
  Não existe nenhum arquivo — conferi Model, Command e migration.
- **Captura por URL** dos dados e fotos das autoridades, para quando a chefia muda.

> ⚠️ Quando formos fazer: a captura **não pode** rodar no momento de gerar o relatório.
> Um endereço vindo de formulário, buscado no servidor durante a geração, é SSRF acionável
> por quem clica em "gerar". Tem de ser comando manual, com lista de domínios permitidos e
> confirmação humana antes de gravar.

---

## 🔴 Fora da lista, mas aberto: um achado de segurança

Há um achado de segurança em aberto no módulo de relatórios, com correção já definida.

**Ele não está descrito aqui de propósito.** Este repositório é público, e publicar o
detalhe de uma falha ainda não corrigida é entregar o mapa a quem quiser usá-la. O
registro completo — onde, o que é, a consequência e a correção proposta — está em
`.claude/seguranca-em-aberto.md`, que o `.gitignore` mantém fora do versionamento.

**Esforço:** uma linha por ação. **Prioridade:** alta.

## Se o limite só der para poucos, minha recomendação de ordem

1. **Item 14** — commit da remoção do `CLAUDE.md`. Minutos, e o repositório é público.
2. **O achado de segurança em aberto** (ver `.claude/seguranca-em-aberto.md`). Uma
   linha por ação, e fecha o problema.
3. **Item 13, nas telas de maior uso** — dashboard, indicadores e entregas. Não as 70.
4. **Item 15**, cadastro de autoridades — a captura por URL fica para depois, porque é a
   parte que exige cuidado com SSRF e não cabe em sobra de limite.
5. **Item 12 / A3 e A4** — dívida técnica real, mas nenhum cliente sente hoje.
