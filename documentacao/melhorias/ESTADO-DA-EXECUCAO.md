# Estado da execução — 05/09/2026

Cada linha diz o que foi **executado e verificado**, com a evidência.
Os estudos individuais continuam válidos como análise; este arquivo diz o que virou código.

**Suíte:** 145 testes passando, 13 pulados (destrutivos, por desenho), **0 falhas**.
Antes deste ciclo a suíte **não rodava** nesta máquina.

---

## Resumo por demanda

| # | Demanda | Estado | Evidência |
|---|---|---|---|
| 1 | Renomear "Plano de Ação" → Iniciativas | ✅ **Concluída** | 124 trocas em 54 arquivos + 27 correções de concordância; 6 testes |
| 2 | Tipos de Iniciativa populados no banco | ✅ **Concluída** | `TipoExecucaoSeeder` idempotente + comando de remanejamento; 8 testes |
| 3 | Modal Nova Atividade: largura + "Valores públicos" | ✅ **Concluída** | `modal-lg` + N grupos derivados da constante; 6 testes |
| 4 | Ordem das perspectivas no BSC | ✅ **Concluída** | Prévia dinâmica + tabela `DESC` + `/objetivos` alinhado; 4 testes |
| 5 | Vincular ODS ao criar objetivo | ✅ **Concluída** | Já existia; contador `n/3` no cabeçalho do modal + âncora |
| 6 | Grau de Satisfação antes do indicador | ✅ **Concluída** | Acesso destravado + farol escopado por ciclo em 15 pontos; 9 testes |
| 7 | Mapa Estratégico público | ⚠️ **Parcial** | Vazamento fechado; a área pública depende de decisão institucional |
| 8 | Quem gerencia o quê | ✅ **Concluída** | `/ajuda/papeis` derivada da MATRIZ; 6 testes |
| 9 | Reconstrução da UI dos relatórios | ⚠️ **Parcial** | Simetria, `@page` único, órfãos, "Dossiê", seção vazia; 6 testes |
| 10 | Histórico de relatórios | ✅ **Concluída** | Geração sob demanda passa a registrar; texto explicativo; 2 testes |
| 11 | Erro `[pei]` + modal de risco | ✅ **Correção** / ⚠️ modal | Bug corrigido em 2 pontos + 2º bug achado; 4 testes. Reordenação do modal não feita |
| 12 | Achados transversais | ✅ **Concluída** | 51 Models qualificados, 10 `catch` registrando, 2 queries fora de Blade, 3 guardas novas |
| 13 | Auditoria de UI/UX no Chrome | ✅ **Concluída** | Contraste 5→0, alvos 7→0, `<main>`, skip link, meta; auditor versionado |
| 14 | `CLAUDE.md` fora do repositório | ✅ **Concluída** | Fora do índice + `CONTRIBUTING.md` público |
| 15 | Relatório de Gestão (DOCX + PDF) | ❌ **Não executada** | Só a dependência instalada — ver seção própria |
| 16 | Padrão de seeder idempotente | ✅ **Concluída** | `VocabularioControlado` + 8 testes |

---

## O que apareceu durante a execução e não estava previsto

| Achado | Como apareceu | Estado |
|---|---|---|
| 🔴 **A suíte de testes nunca rodou neste projeto** | `phpunit.xml` apontava para a porta **5434**, instância que não existe nesta máquina. O banco `projeto_base_test` já estava na 5432 | ✅ Corrigido — a suíte roda |
| 🔴 **`gen_random_uuid()` derruba a primeira instalação em PostgreSQL 12** | `migrate` num banco novo falhava. A extensão `pgcrypto` instalada em `public` **não é vista** pelo `search_path` do projeto | ✅ `app:init-schemas` instala no schema certo, move se já existir, e imprime o SQL para o DBA se faltar privilégio |
| 🔴 **Segundo bug ao salvar risco** | O teste de regressão do bug relatado revelou outro: data vazia chega como `""` e o PostgreSQL recusa. **O cliente que preenchesse sem a data continuaria travado** | ✅ Corrigido + teste |
| 🔴 **31 advisories de segurança em 6 pacotes** | `composer audit` após instalar o PHPWord. Nenhum vinha dele | ✅ 31 → 0 |
| 🔴 **Pin exato bloqueava correção de segurança** | `"livewire/livewire": "4.0"` impedia qualquer patch do 4.x, incluindo um CVE de XSS | ✅ `^4.0`; 4.0.0 → 4.4.3, suíte verde |
| 🔴 **Segundo ponto de download sem verificação** | `ListarRelatorios` tinha o mesmo IDOR de `HistoricoRelatorios` | ✅ Unificados num trait com Policy |
| 🔴 **Escrita aberta nos Graus de Satisfação** | Ao destravar o acesso: só `mount()` autorizava; `save`, `edit`, `delete` não | ✅ Autorização por método |
| 🟠 **`User::factory()` cria usuário inativo** | O `Gate::before` nega tudo para conta inativa; todo teste de autorização falhava com 403 sem motivo aparente | ✅ Corrigido na factory |
| 🟠 **Segunda query dentro da Blade** | A guarda nova apontou uma que meu levantamento não viu | ✅ Movida para a relação do Model |
| 🟠 **Paleta duplicada na landing** | Corrigi o contraste numa cópia e a outra ficou fora | ✅ Unificada |
| 🟠 **Ocorrência perdida pelo `grep -i`** | `grep -i` não dobra maiúscula acentuada nesta locale: `PLANOS DE AÇÃO` escapou do inventário. **O teste pegou** | ✅ Corrigida |

---

## Travas novas — o que a máquina passou a cobrar sozinha

| Trava | O que impede |
|---|---|
| `exists`/`unique` com schema | Reintrodução do bug `Database connection [pei] not configured` |
| `$table` de Model sem schema | O próximo Model nascer dependendo do `search_path` |
| Consulta a Model dentro de Blade | N+1 invisível a teste de componente |
| `migrate`/`db:seed` deixaram de pedir confirmação | Ruído que ensina a clicar "sim" sem ler; o destrutivo continua proibido |

E as travas em forma de teste: coerência guia × tela, coerência mapa × tabela × objetivos,
seeder nas duas populações do cliente, farol por ciclo, seção vazia no relatório, simetria de
margem, vocabulário "Iniciativas" e a integridade dos identificadores de sistema.

---

## Demanda 15 — o que existe e o que falta

**Executado:** `phpoffice/phpword` instalado (`^1.4`). O projeto não tinha como gerar `.docx`.

**Não executado:** o relatório em si.

Não foi por falta de tempo — foi por proporção. O estudo
[15](15-relatorio-de-gestao-modelo-presidencia.md) mostra que o sistema cobre **cerca de 40 das
198 páginas** do modelo: as seções 2.1 (Estratégia), 2.2 (Resultados), 1.6 (Cadeia de Valor) e
4.1 (Riscos). O resto vem de Tesouro Gerencial, SIAPE e Comprasnet.

Antes da primeira linha de código é preciso a sua resposta à tabela da §3 daquele estudo. Entregar
um esqueleto agora produziria exatamente a frustração que você pediu para evitar.

O que já está pronto para quando começar: a estrutura completa do modelo extraída, a paleta medida
por amostragem de pixel (`#54B347`, `#2C2E35`, `#EDC009`, `#3550A0`), o grid de página e o estilo
de tabela das páginas 27 e 28.

---

## O que ficou de fora, e por quê

| Item | Por quê |
|---|---|
| **Área pública `/transparencia`** ([07](07-mapa-estrategico-publico.md)) | Publicar objetivos e desempenho de um órgão de Estado é decisão institucional. O que **fechei** foi o vazamento: a contagem de riscos críticos estava publicada sem chave e sem que ninguém tivesse decidido. Agora nasce desligada |
| **Reordenação do modal de Risco** ([11](11-erro-conexao-pei-e-modal-de-risco.md) §3) | O bug foi corrigido e travado. A reordenação é melhoria de UX numa Blade de 910 linhas — merece ser feita com a tela aberta, não às cegas |
| **Redesenho completo dos 11 relatórios** ([09](09-reconstrucao-ui-dos-relatorios.md)) | Feitas a fundação e as correções objetivas. Capa institucional, textos de abertura por relatório e a migração de todas as seções para o componente `<x-relatorio.secao>` são trabalho de acabamento, com o PDF na tela |
| **Auditoria de UI das telas autenticadas** ([13](13-auditoria-ui-ux-navegada.md) 13-B18) | O auditor está versionado em `ferramentas/auditoria-ui/` e roda em qualquer URL. Falta a passagem com sessão |
| **Permissões do harness** | O classificador do Claude Code impede o assistente de ampliar a própria lista. O arquivo pronto está em `anexos/settings-permissoes-proposto.json` |

---

## Como reproduzir a verificação

```bash
php artisan test                                    # 145 verdes
composer audit                                      # 0 advisories
node ferramentas/auditoria-ui/auditoria.mjs http://localhost/fs-v1/public/ agora
vendor/bin/pint --test                              # formatação
```

Para uma instalação nova de cliente:

```bash
php artisan app:init-schemas    # schemas + gen_random_uuid()
php artisan migrate
php artisan db:seed             # inclui os tipos de Iniciativa
```

---

## Nota de escopo — o Pint e a varredura de 97 arquivos

Rodei `vendor/bin/pint` sem caminho em determinado momento. Ele varreu o projeto inteiro e
reformatou **cerca de 130 arquivos que a demanda não pedia** — nenhuma mudança de comportamento,
mas mudança não pedida é risco não pedido, e o `git diff` chegou a 247 arquivos.

**Revertido.** Restaram 114 arquivos, todos com alteração real. A verificação foi comparar o
conjunto normalizado de linhas adicionadas e removidas de cada diff: iguais = só formatação.
Reverti inclusive 6 migrations já aplicadas e o `tests/Pest.php`, que o Pint tinha tocado.

Fica a ressalva honesta: nos arquivos que eu **de fato** alterei, a formatação do Pint está
misturada às minhas mudanças e não dá para separar sem perder o trabalho. É o caso de
`IndicadorCalculoService`, `PeiGuidanceService` e `ReportGenerationService`, cujos diffs parecem
maiores do que a alteração de lógica realmente foi.

Para o futuro: `vendor/bin/pint` **sempre com o caminho dos arquivos tocados**, nunca solto.
