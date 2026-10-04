# Controle de testes pelo navegador

Registro ação por ação. **Regra:** só vale como testado o que está escrito aqui, com data, tela, ação,
o que deveria acontecer e o que aconteceu. O que não está aqui **não foi testado** — inclusive tudo do
"teste completo" da manhã de 03/10/2026, que não deixou registro e por isso não conta como prova.

Lista do que existe para testar: `INVENTARIO-DE-ACOES.md` (380 ações tiradas do código; 228 sem nenhum
teste automático, das quais 155 são botões na tela).

Legenda: ✅ funciona como prometido · ❌ defeito · 🔧 defeito corrigido · ⚠️ ressalva · ⏳ pendente de conferência

## 03/10/2026 — sessão da tarde (Super Administrador, ambiente de desenvolvimento)

| # | Tela | Ação | Esperado | Resultado |
|---|---|---|---|---|
| 1 | `/auditoria/52/detalhes` | Abrir detalhe de auditoria (alteração) | Leitura compreensível | 🔧 Ilegível (colunas técnicas, UUIDs, "null"). Tela refeita; conferida depois nos registros 52, 49 e 43 |
| 2 | `/auditoria` | Abrir lista | Registro com nome legível | 🔧 Mostrava "ID: …xxxx"; agora nome do registro e origem do acesso |
| 3 | `/auditoria/43/detalhes` | Abrir "Detalhes técnicos" | Dados brutos sem segredo | 🔧 Senha aparecia no JSON bruto (achado no teste automático); mascarada |
| 4 | 29 telas logadas | Abrir cada uma (só carregamento, não botões) | Abre sem erro | ✅ Todas 200 — **não prova que os botões funcionam** |
| 5 | 7 telas públicas, sem login | Abrir cada uma | Abre sem erro | ✅ Todas 200 |
| 6 | `/indicadores` | Novo Indicador → botão "Iniciativa" | Mostra "Selecionar iniciativa" | ✅ (antes enviava "Plano" e o servidor não conferia — corrigido na auditoria) |
| 7 | `/indicadores` | Fechar modal (Esc) | Fecha sem gravar | ✅ |
| 8 | `/dashboard` | Esperar o atualizar automático (30 s) | Atualiza sem erro | ✅ POST /livewire/update 200 |
| 9 | `/entregas` (busca do menu) | Abrir sem iniciativa e cadastrar | Pedir a iniciativa | 🔧 Escolhia sozinho a iniciativa mais recente (sua entrega foi para "Rotas de Integração 2026"). Agora lista as iniciativas para escolher; banco recusa entrega sem iniciativa |
| 10 | `/entregas` | Clicar numa iniciativa da lista | Abre o quadro dela | ✅ Cabeçalho mostra "Iniciativa" e "Nova Entrega nesta iniciativa" |
| 11 | `/organizacoes` | Nova Organização → sigla + nome → Confirmar e Criar | Cria e confirma | ✅ "[TESTE] Órgão Central" criada (contador 7 → 8) |
| 12 | `/organizacoes` | Fechar aviso de sucesso (Entendido) | Fecha | ✅ |
| 13 | `/organizacoes` | Buscar "TESTE" | Filtra | ✅ 1 resultado |
| 14 | `/organizacoes` | Limpar Filtros | Volta a lista completa | ⏳ Logo após o clique ainda havia 1 linha; segundos depois a lista estava completa. Não fechado se é atraso ou defeito |
| 15 | `/organizacoes` | Abrir seção educativa | Expande o texto | ✅ Falso alarme: a aba da automação fica em segundo plano e o navegador pausa a animação de abrir; com a aba visível abre (mesmo componente conferido em Valores, Temas, Objetivos e Identidade — linhas 68 e 72) |

| 16 | migration | `entrega_exige_iniciativa` no dev (autorizada pelo gestor) | Coluna obrigatória | ✅ DONE; `cod_plano_de_acao` agora NOT NULL |
| 17 | `/pei/ciclos` | Novo PEI → descrição → Salvar | Cria ciclo | ✅ "[TESTE] Ciclo 2026-2030", 0 perspectivas, Vigente |
| 18 | `/pei/ciclos` | Continuar (aviso de sucesso) | Fecha | ✅ |
| 19 | `/pei/ciclos` | Buscar "TESTE" | Filtra | ✅ 1 linha (resposta leva ~2 s) |
| 20 | `/pei/ciclos` | Limpar Filtros | Volta tudo e esvazia o campo | ✅ 3 linhas, campo vazio |
| 21 | `/pei/ciclos` | Filtro Status: Futuro / Vigente / Todos | Filtra | ✅ 1 / 2 / 3 |
| 22 | `/pei/ciclos` | Editar → abre com dados → Cancelar | Abre preenchido e fecha sem gravar | ✅ |

| 23 | `/pei/{id}/detalhes` | Editar PEI | Abre a edição deste ciclo | ✅ Abre `/pei/ciclos?editar=…` com o modal preenchido |
| 24 | `/pei/{id}/detalhes` | Definir Missão e Visão | Abre Identidade no ciclo do detalhe | ✅ `/pei`, ciclo do topo trocado para 2026-2030 |
| 25 | `/pei/{id}/detalhes` | Gerenciar Valores | Abre Valores no ciclo | ✅ `/pei/valores` |
| 26 | `/pei/{id}/detalhes` | Gerenciar Perspectivas | Abre Perspectivas no ciclo | ✅ `/pei/perspectivas` |
| 27 | `/pei/{id}/detalhes` | Gerenciar Objetivos | Abre Objetivos no ciclo | ✅ `/objetivos` |
| 28 | `/pei/{id}/detalhes` | Gerenciar Indicadores | Abre Indicadores no ciclo | ✅ `/indicadores` |
| 29 | `/pei/{id}/detalhes` | Análise SWOT (clique de mouse) | Abre SWOT no ciclo | ✅ `/pei/swot` |
| 30 | `/pei/{id}/detalhes` | Tempo de resposta dessas 6 ações | Resposta perceptível | 🔧 ~1 s no servidor + carga da tela, sem nenhum sinal na tela — parecia não funcionar. Adicionado aviso "Abrindo no ciclo selecionado…" e link esmaecido durante a espera |

| 31 | topo | Seletor de unidade → [TESTE] Órgão Central | Troca a unidade | ✅ topo mostra TST |
| 32 | `/pei/inaugurar` | Preencher → equipe, diretrizes, datas, aprovado → Salvar | Grava | ✅ conferido no banco (`tab_inaugurar_pei`) |
| 33 | `/pei/inaugurar` | Aba Integração → Adicionar → preencher → Salvar | Cria | ✅ aparece na lista |
| 34 | `/pei/inaugurar` | Integração → Editar → alterar → Salvar | Abre preenchido e grava | ✅ |
| 35 | `/pei/inaugurar` | Integração → Excluir → confirmar | Exclui | ✅ `deleted_at` preenchido no banco |
| 36 | `/pei/inaugurar` | Aba Agenda 2030 → marcar ODS 1 e 18 → Salvar | Grava aderência | ✅ 18 ODS na tela; banco com 1 e 18 |
| 37 | `/pei/inaugurar` | Desmarcar ODS 1 → Salvar | Remove | ✅ banco só com 18 |
| 38 | `/pei/inaugurar` | Aba Calendário → Novo evento → Salvar | Cria | ✅ |
| 39 | `/pei/inaugurar` | Evento → Editar → alterar → Salvar | Abre preenchido e grava | ✅ |
| 40 | `/pei/inaugurar` | Evento → Excluir → confirmar | Exclui | ✅ `deleted_at` no banco |
| 41 | `/pei/inaugurar` | "Continuar" dos avisos de sucesso | Fecha | ✅ |

| 42 | `/pei` | Editar Missão/Visão → Cancelar | Fecha sem gravar | ✅ |
| 43 | `/pei` | Editar Missão/Visão → preencher → Salvar | Grava na unidade do topo | ✅ gravada em TST (conferido no banco) |
| 44 | `/pei` | Sugerir Missão e Visão (IA) | Mostra sugestão | ✅ em 5 s |
| 45 | `/pei/ciclos` | Excluir ciclo (verificado pelo banco) | Exclui o ciclo e tudo o que é dele | 🔧 **Defeito:** só o ciclo era excluído; identidade, perspectivas e objetivos de 3 ciclos [TESTE] da manhã continuavam vivos. Agora a exclusão leva tudo (modelo PEI) + migration limpou os órfãos no dev + teste com mutação |
| 46 | `/organizacoes` | Excluir unidade (verificado pelo banco) | Nada da unidade fica vivo | ✅ 8 unidades excluídas, nenhum dado vivo |

| 47 | `/pei` | Aplicar sugestão da IA | Grava a missão sugerida | ✅ conferido no banco |
| 48 | `/pei/valores` | Novo → digitar → Cancelar → Novo | Reabre limpo | ✅ |
| 49 | `/pei/valores` | Novo → preencher → Salvar | Cria | ✅ |
| 50 | `/pei/valores` | Editar (menu do card) → alterar → Salvar | Abre preenchido e grava | ✅ |
| 51 | `/pei/valores` | Detalhar | Abre o detalhe do valor | ✅ 200, mostra o valor |
| 52 | `/pei/valores` | Excluir | — | ⏭️ usa a caixa de confirmação nativa do navegador (trava a automação); coberto por teste automático |
| 53 | `/temas-norteadores` | Novo → digitar → Cancelar → Novo | Reabre limpo | ✅ |
| 54 | `/temas-norteadores` | Novo → preencher → Salvar | Cria | ✅ |
| 55 | `/temas-norteadores` | Busca (inexistente / existente) | Filtra | ✅ |
| 56 | `/temas-norteadores` | Sugerir com IA → Adicionar uma sugestão | Mostra 3 sugestões; cria a escolhida | ✅ lista passou de 1 para 2 |
| 57 | `/temas-norteadores` | Editar → alterar → Atualizar | Abre preenchido e grava | ✅ |
| 58 | `/pei/pestel` | Botão "+" de cada uma das 6 dimensões | Existe nas 6 | ✅ Político, Econômico, Social, Tecnológico, Ambiental, Legal |
| 59 | `/pei/pestel` | Novo → digitar → Cancelar → Novo | Reabre limpo | ✅ |
| 60 | `/pei/pestel` | Novo item (Político) → Salvar | Cria na dimensão e unidade certas | ✅ banco: PESTEL/Político/TST |
| 61 | `/pei/pestel` | Editar → alterar → Salvar | Grava | ✅ |
| 62 | `/pei/pestel` | Sugerir com IA → Adicionar uma sugestão | Cria e tira da lista de sugestões | ✅ 12 sugestões em 5 s; item criado (Político/TST) |
| 63 | `/pei/swot` | Novo item em cada quadrante (Força, Fraqueza, Oportunidade, Ameaça) → Salvar | Cria no quadrante certo | ✅ os 4 (Ameaça só depois de esperar a animação do modal) |
| 64 | `/pei/swot` | Alternar modo de visualização | Troca a forma de exibir | ✅ |
| 65 | `/pei/swot` | Editar item → alterar → Salvar | Grava | ✅ |
| 66 | `/pei/swot` | Sugerir com IA → Adicionar sugestão | Cria | ✅ 12 sugestões; quadrante de 4 para 5 itens |
| 67 | `/pei/swot` | Partes interessadas: criar, editar, excluir | CRUD | ✅ ⚠️ excluir parte interessada apaga na hora, **sem nenhuma confirmação** |
| 69 | `/pei/swot` | Cenários: novo → Salvar; editar (nome + tipo Otimista) → Salvar; excluir | CRUD na unidade do topo | ✅ banco: Otimista/TST; excluído com `deleted_at` |
| 70 | `/pei/swot` | TOWS: "+" do quadrante WO → Salvar; editar → Salvar; excluir | CRUD | ✅ abre já no tipo WO; banco: WO/TST, excluída |
| 71 | `/pei/swot` | Excluir parte interessada / cenário / estratégia TOWS | Pedir confirmação, como os itens SWOT | 🔧 Apagavam na hora. Agora pedem confirmação (`wire:confirm`); conferido no DOM em cenário e TOWS; parte interessada pela mesma alteração |
| 68 | `/pei/valores` | Seção educativa | Só sobre Valores (pedido do gestor) | 🔧 Falava também de Temas Norteadores e Missão/Visão; reescrita só sobre Valores (`x-secao-educativa`), conferida aberta na tela com clique real |
| 72 | Seções educativas de todas as telas (varredura das 38 views) | Cada uma só sobre o tema da tela | 🔧 `/pei`: tirado o card de Valores; `/temas-norteadores`: tirada a comparação com Valores/Objetivos e os "Níveis de Planejamento"; `/objetivos`: tirada a aula de BSC/4 perspectivas (é o tema de `/pei/perspectivas`), entrou "O que é um Objetivo Estratégico". As três conferidas abertas na tela; HTML conferido sem o texto removido |
| 73 | `/entregas` (escolha da iniciativa) | Ter seção educativa | 🔧 Faltava; criada "O que são Entregas?" (conferida no HTML da tela, 200) |

## 04/10/2026 — continuação (Super Administrador, localhost, unidade TST, ciclo [TESTE])

Em paralelo: quatro revisões de código por módulo (Indicadores; Iniciativas/Entregas; Riscos/RAE/outros; Relatórios/Usuários), relatórios em scratchpad da sessão.

| # | Tela | Ação | Esperado | Resultado |
|---|---|---|---|---|
| 74 | `/pei/perspectivas` | Nova → (abre limpo, nível sugerido 1) → Salvar → Continuar | Cria | ✅ |
| 75 | `/pei/perspectivas` | Nova de novo | Sugere o próximo nível | ✅ sugeriu 2 |
| 76 | `/pei/perspectivas` | Editar → alterar → Salvar | Abre preenchido e grava | ✅ |
| 77 | `/pei/perspectivas` | Pedir ajuda à IA → Aplicar uma sugestão | Cria a perspectiva sugerida | 🔧 4 sugestões; a aplicada ("Aprendizado e Crescimento", ordem 1) foi gravada **no nível 1, já ocupado** — duas perspectivas no mesmo degrau do mapa. O cadastro manual também aceitava. Agora o nível é único no ciclo (mensagem "Já existe uma perspectiva neste nível…") e a sugestão da IA vai para o próximo nível livre. Teste `PerspectivaNivelUnicoTest` (falhava antes). Conferido na tela: a mensagem aparece; com nível 2, grava |
| 79 | `/objetivos` | "Adicionar o primeiro" da perspectiva → título, descrição, ODS 3 → Salvar | Cria na perspectiva do botão, com o ODS | ✅ perspectiva já vinha escolhida; banco: ODS 3 |
| 80 | `/objetivos` | Editar → contribuição ao ODS + título → Salvar | Grava | ✅ banco: "3:Saúde digital", título (rev) |
| 81 | `/objetivos` | Novo objetivo **sem ODS**, desdobrado de outro (Hoshin Kanri) | Aceita sem ODS; grava o pai | ✅ ODS opcional confirmado; pai gravado |
| 82 | Indicadores (revisão de código) | 5 defeitos graves + ~15 médios/baixos | — | ❌ Relatório em scratchpad (`revisao-indicadores.md`); correção entregue a um agente dedicado, com teste por defeito. Conferência na tela depois da correção |
| 83 | `/planos` | Nova Iniciativa → objetivo, descrição, unidade TST, tipo Projeto, início 2025 | Recusa início antes do ciclo | ✅ "A data de início deve ser igual ou posterior ao início do PEI (2026)" |
| 84 | `/planos` | Mesma, 01/01/2026 a 31/12/2028 → Salvar | Cria | ✅ |
| 85 | `/planos` | Filtro de ano 2026 / **2027** / 2028 | Iniciativa vigente nos três anos aparece nos três | ❌ Aparece em 2026 e 2028; **some em 2027** (filtro só olha o ano de início e o de fim) |
| 86 | `/planos/{id}/entregas` | Criação rápida de 3 entregas | Cria na iniciativa | ✅ |
| 87 | quadro | Mover A→Concluído, B→Cancelado, C→Em Andamento (método do arrasto) | Muda e registra no histórico | ❌ Status muda, mas **o histórico não registra** (banco: 1 linha de histórico em cada, só a criação) |
| 88 | quadro × detalhe | Progresso da mesma iniciativa | Mesmo número nas duas telas | ❌ Quadro **33,3%**, detalhe **75,0%** |
| 89 | quadro | Filtro Responsável com uma pessoa | Filtra | ❌ **404** (id da pessoa convertido em número); filtro fica na URL e recarregar repete o erro |
| 90 | quadro, visão Lista | Seletor Responsável | Grava | ❌ chama `atualizarResponsavel`, que não existe (lista de pessoas também vazia nesta unidade) |
| 91 | Riscos/RAE/outros e Relatórios/Usuários (revisão de código) | — | — | ❌ 23 e 20 achados; relatórios em scratchpad (`revisao-riscos-rae-outros.md`, `revisao-relatorios-usuarios.md`) |
| 92 | migration | `2026_10_04_000001_amplia_precisao_valores_indicador` no dev | 4 casas nas 4 colunas | ✅ DONE; conferido em information_schema: numeric(19,4) |
| 93 | `/indicadores` | Novo indicador (Objetivo, Monetário, TST) → Salvar | Cria; sem lançamento aparece "Sem medição" cinza | ✅ |
| 94 | `/indicadores` | Metas → digitar **20000000000,00** pelo teclado → Adicionar | Máscara R$ e grava R$ 20 bi | 🔧✅ Antes era campo numérico que recusava; agora mostra "20.000.000.000,00", lista "R$ 20.000.000.000,00", banco 20000000000.0000. Lixeira da meta pede confirmação |
| 95 | Lançar Evolução | Previsto 1.000.000,00, **Realizado vazio**, switch desligado → Salvar | Grava NULL e "Não"; sem farol | 🔧✅ banco: realizado NULL, "Não"; histórico "Sem medição" (antes: 0 e farol verde na polaridade Negativa; o switch impedia salvar) |
| 96 | Lançar Evolução | Recarregar o mês | Switch volta desligado, Realizado vazio | 🔧✅ |
| 97 | `/indicadores` | Indicador Índice (0-1), não acumulado → meta **0,875** | Grava 4 casas | 🔧✅ "0,8750" na lista |
| 98 | Lançar Evolução | Realizado 0,875 com Previsto vazio | Atingimento ≈ 100% (antes ≈ 1.133% pela meta ÷ 12) | 🔧✅ banco 0.8750; histórico "0,8750 — 100,0%" |
| 99 | Lançar Evolução | Alterar Realizado para 0,9 → Salvar | Histórico atualiza sem recarregar | ✅ "0,9000 — 102,9%" |
| 100 | Lançar Evolução | Salvar com os dois valores vazios | — | ⚠️ grava um mês "Sem medição" sem nenhum valor (aceitável se houver comentário/evidência; anotado) |
| 101 | `/planos` (após correção) | Filtro 2027 / 2028 / 2029 na iniciativa 2026–2028 | Aparece enquanto vigente | 🔧✅ 2027 sim, 2028 sim, 2029 não. ⚠️ `?filtroAno=2027` na URL é trocado pelo ano de referência ao abrir |
| 102 | quadro × detalhe (após correção) | Progresso | Mesmo número | 🔧✅ quadro 75,0% = detalhe 75,0%; com C concluída, 100% (cancelada fora da conta) |
| 103 | quadro (após correção) | Mover entrega para Concluído (como o arrasto chama) | Grava e entra no histórico | 🔧✅ banco: 2 linhas de histórico (criação + mudança) |
| 104 | quadro (após correção) | Filtro Responsável | Filtra sem erro | 🔧✅ 200 (0 cards: ninguém atribuído nesta iniciativa) |
| 105 | Graus de satisfação (código) | Régua do ano × geral; valor no buraco entre faixas | Régua do ano substitui a geral; buraco recebe a faixa de baixo | 🔧 testes novos em `GrauSatisfacaoTest` (falhavam antes); 19 passam |
| 106 | `/riscos` | Novo risco na TST (unidade sem usuários) → Salvar | Exige responsável | ✅ "O campo responsável pelo monitoramento é obrigatório." — lista vazia porque a TST não tem usuário vinculado. Próximo: criar usuário [TESTE] na TST pela tela de Usuários |
| 107 | **incidente** | Corretor de Relatórios/Usuários incluiu `tests/Seeders` numa lista de testes; a seed rodou no banco de **dev** às 10:47 | — | ❌ Organização raiz renomeada para "ORG / Organização Padrão"; criada conta Super Admin `admin@pei.gov.br` (senha desconhecida) com 1 vínculo; os 5 perfis só tiveram `updated_at` alterado (texto igual). Restauração **autorizada pelo gestor e feita** (1 UPDATE + 1 DELETE em transação): conferido no banco — MIDR com o nome original, conta e vínculo removidos. Trava criada: `SeederTestCase` só roda com `SEED_TEST_ALLOW=true` (conferido: 25 pulados, banco intocado) |
| 108 | `/usuarios` | Novo usuário "[TESTE] Gestora de Riscos" (link por e-mail) + vínculo Admin da Unidade na TST | Cria com vínculo | ✅ |
| 109 | `/riscos` | Novo risco P3×I3 com a Gestora como responsável | Cria; prévia "Médio" | ✅ |
| 110 | `/riscos/{id}/mitigacao` | Nova mitigação com **custo vazio** → Salvar | Grava custo nulo (antes: 404) | 🔧✅ banco: custo NULL |
| 111 | mitigação | Excluir → **Cancelar** / Excluir → Confirmar | Cancelar mantém; confirmar exclui | 🔧✅ (antes o Cancelar excluía mesmo assim) |
| 112 | `/riscos/matriz` | Clicar no risco | Abre a lista já filtrada | 🔧✅ busca preenchida, 1 linha |
| 113 | `/usuarios` | Excluir a Gestora (tem histórico) | Recusa, mostra o histórico, oferece Desativar | 🔧✅ "não pode ser excluído… Riscos que monitora: 1" + Desativar. Corrigido "1 riscos" → "Riscos que monitora: 1" |
| 114 | `/admin/perfis` | Assumir a Gestora (troca de senha pendente) → editar risco → Encerrar Impersonação | Não fica preso em /trocar-senha; volta ao admin; auditoria com o autor real | 🔧✅ entrou no dashboard; voltou como Usuário Administrador; `pei.audits`: autor user_adm + tag `impersonando:<Gestora>` |
| 115 | `/relatorios` | Os 15 links da página (Gestão PDF/Word ×2, Integrado, Executivo, Identidade, Objetivos PDF/Excel, Indicadores PDF/Excel, Iniciativas PDF/Excel, Riscos PDF/Excel) | Geram sem erro | ✅ todos 200 (PDF 0,9–1,3 MB; Excel/Word 6–14 KB). Conteúdo de cada arquivo conferido só pelos testes automáticos dos corretores, não aberto um a um |
| 116 | suíte completa | 2ª rodada, sozinha (sem Seeders) | Tudo passa | ⚠️ 523 passaram, 4 pulados, **1 falhou**: minha mudança na régua usava as faixas de outro ano quando o ano não tinha régua própria nem geral. Corrigido (ano sem régua = sem farol); os 28 testes de régua e atingimento passam. 3ª rodada após a revisão final |
| 117 | revisão hostil do conjunto | Conflitos entre os 4 corretores | — | 🔧 7 achados: (1) régua de outro ano — já corrigido na linha 116; (2) legenda/PDF de indicadores com régua diferente da do farol; (3) gráfico do Dashboard ignorava "acumulado" (100% × 8,3%); (4) detalhe da faixa punha indicador "sem medição" na pior faixa; (5) indicador automático sem iniciativa sem forma de receber valor; (7) selo de nível de risco com cor diferente da matriz e PDF/Excel de riscos sem as subordinadas; S4 nome de evidência com barra → 500. Todos corrigidos; `RevisaoFinalReguaETelasTest` (5 testes) **validado por mutação**: as 5 correções desfeitas → 5 falhas; restauradas → 5 passam. (6) `.htaccess` fica fora do commit |
| 118 | ressalva da linha 101 | `?filtroAno=2027` no endereço de `/planos` | Vale o ano do endereço | 🔧 corrigido; `RessalvasDoTesteFuncionalTest` validado por mutação |
| 119 | ressalva da linha 100 | Salvar mês sem valor, análise ou evidência | Recusar com mensagem | 🔧 "Informe o previsto, o realizado, a análise ou uma evidência…"; mês já existente pode ser salvo; validado por mutação |
| 120 | ressalva (zeros antigos) | Lançamentos antigos com 0 vindo de campo em branco | — | ⏭️ Não há como distinguir no banco o 0 medido do 0 que era branco; fica como está (afeta só lançamentos anteriores a 04/10/2026) |
| 121 | quadro de entregas | Editar entrega: prazo 2035 (fora da iniciativa 2026–2028) | Recusa | ✅ "O prazo precisa estar dentro do período da iniciativa (01/01/2026 a 31/12/2028)." |
| 122 | quadro de entregas | Editar: título, prazo válido e os 7 campos do 5W2H → Salvar; depois limpar os 7 → Salvar | Grava; limpar apaga | ✅ banco: 5W2H gravado; após limpar, `json_propriedades` NULL |
| 123 | **pedido do gestor** | Desligar o autocadastro | Só quem tem permissão cria contas | 🔧 `config/fortify.php` sem `Features::registration()`; `SemAutocadastroTest` (falhava antes). Conferido como visitante: login sem "Criar conta", `/register` redireciona, página inicial sem link. Ajuda e chamado atualizados |
| 124 | quadro | Etiquetas: criar "[TESTE] Urgente" → aplicar na entrega | Cria e vincula | ✅ banco: 1 vínculo |
| 125 | quadro | Comentário → resposta → excluir | Resposta não some; exclusão pede confirmação | ✅ comentário com resposta não oferece "Excluir"; os demais pedem "Excluir este comentário?" |
| 126 | quadro | Arquivar → ver arquivados → desarquivar | Some, aparece em arquivados, volta | ✅ |
| 127 | quadro | Excluir → lixeira → abrir detalhe → restaurar | Lixeira honesta; detalhe só com Restaurar/Excluir definitivo; volta | ✅ aviso "ficam aqui até serem restauradas ou excluídas definitivamente"; histórico com created/arquivar×2/deleted/restored |
| 128 | calendário | Clicar no dia 20 → criar | Entrega com prazo 20/10 | ✅ banco: 2026-10-20 |
| 129 | calendário | Próximo / Hoje | Navega | ✅ mês 10 → 11 → 10 |
| 130 | linha do tempo | Anterior, próximo, zoom +/−, hoje | Sem erro | ✅ |
| 131 | visão Lista | Status, prioridade e prazo 2035 pelos seletores | Grava; prazo fora da iniciativa recusado | ✅ Suspenso/alta gravados; prazo recusado com a mensagem do período |
| 132 | `/planos/{id}/responsaveis` | Adicionar Gestora como Responsável | Grava | ✅ |
| 133 | responsáveis | Adicionar a mesma pessoa como Substituta | Recusar | 🔧 Era aceita (Responsável e Substituta ao mesmo tempo). Agora: "Esta pessoa já é Gestor(a) Responsável desta iniciativa…"; `GestorComUmPapelPorIniciativaTest` validado por mutação |
| 134 | responsáveis | Remover gestor | Pede confirmação | ✅ "Remover … da gestão desta iniciativa?" |
| 135 | responsáveis | RACI: novo (R) → editar para A → excluir pede confirmação | CRUD | ✅ banco: papel A; "Excluir este papel RACI?" |
| 136 | responsáveis | Comunicação: responsável com 120 caracteres | Mensagem, não 500 | ✅ "O nome do responsável aceita até 100 caracteres." (resposta 200) |
| 137 | responsáveis | Comunicação: criar, editar, excluir com confirmação | CRUD | ✅ |
| 138 | `/monitoramento/rae` | Nova RAE (tipo RAE, progresso 0, participantes) → Salvar | Grava na TST; 0 fica 0; mês em português | ✅ "Set/2026"; progresso 0.00 |
| 139 | RAE | Participantes "Fulana; Beltrano" | Dois nomes | 🔧 Virava um nome só (a tela pede vírgula). Agora aceita vírgula, ponto e vírgula ou linha; teste novo |
| 140 | RAE | Novo encaminhamento tipo **"Nova Iniciativa"** → Salvar | Grava | 🔧 **Erro 500**: o código renomeou "Novo Plano" → "Nova Iniciativa" em 05/09 e a regra CHECK do banco não acompanhou. Migration `2026_10_04_120000_tipo_de_encaminhamento_nova_iniciativa` (rodada no dev) + teste pela tela com todos os tipos + `ListasDoCodigoBatemComOBancoTest` (confere toda lista do código contra os CHECK do banco) |
| 141 | RAE | Encaminhamento: mudar status, editar | Grava | ✅ "Em Execução"; descrição (rev) |
| 142 | RAE | Causa raiz: problema, Ishikawa, 5 porquês, causa, vínculo ao encaminhamento | Grava e reabre preenchido | ✅; excluir pede confirmação |
| 143 | RAE | Excluir o encaminhamento vinculado → editar a causa | Sem 403; vínculo vira "nenhum" | ✅ |
| 144 | RAE | Editar a RAE | Grava | ✅ |
| 145 | `/pei/cadeia-valor` | Nova atividade com ordem 99999 → recusa; ordem 1 → grava; processo novo; editar os dois | CRUD com validação | ✅ "Informe a ordem como um número de 0 a 9999." |
| 146 | cadeia de valor | Excluir processo (modal) | Exclui | ✅ |
| 147 | cadeia de valor | Excluir atividade com processos | Processos vão junto | 🔧 Processos ficavam vivos. `AtividadeCadeiaValor` exclui os processos (lógica); `ExcluirAtividadeLevaProcessosTest` (falhava antes). Dev: 0 órfãos antigos |
| 148 | `/objetivos/{id}/futuro` | Novo item, editar meta 90→95, excluir → Cancelar | CRUD; Cancelar mantém | ✅ banco: 95.0000, horizonte 2030-12-31 |
| 149 | `/graus-satisfacao` | Faixas "Todo o Ciclo" 0–59,99 / 60–89,99 / 90–100 | Grava | ✅ (decimal com vírgula, como a máscara exige) |
| 150 | graus | Faixa 50–65 cruzando outra | Recusa | ✅ "Esta faixa se sobrepõe a … um mesmo resultado teria duas cores." |
| 151 | graus | Excluir faixa (modal) | Exclui | ✅ |
| 152 | `/indicadores` | Farol com a régua nova | 102,9% verde da faixa "No alvo"; sem medição cinza | ✅ #1e8449 e #6b7280 |
| 153 | `/licoes-aprendidas` | Nova lição (iniciativa, tipo, categoria) → editar → filtrar por iniciativa → excluir | CRUD | ✅ 🔧 texto "Selecione o plano…" → "Selecione a iniciativa…"; mensagens de validação de Iniciativas que diziam "plano" também ajustadas |
| 154 | `/acervo-documentos` | Novo documento só com link | Exige PDF | ✅ "Selecione o arquivo PDF." |
| 155 | documentos | Enviar PDF pela tela → abrir → editar → busca → limpar filtros | Funciona | ✅ PDF abre (200, application/pdf) |
| 156 | Lançar Evolução | Anexar evidência (PDF) → salvar → abrir; sem login | Abre logado; sem login não | ✅ 200 application/pdf; sem login redireciona; "Excluir a evidência…? O arquivo será apagado do servidor." |
| 157 | quadro de entregas | Anexar PDF à entrega → abrir; sem login | Idem | ✅ 200 application/pdf; sem login redireciona; excluir pede confirmação |
| 158 | `/relatorios` | Agendar com a tarefa do servidor parada | Não promete envio | ✅ "Geração automática indisponível — a tarefa agendada do servidor não está em execução." |
| 159 | relatórios | Rodar uma vez `reports:process-scheduled` (o que o cron faz) → Agendar geração (semanal) | Grava com ciclo e unidade | ✅ banco: filtros com `cod_pei` e `organizacao_id` |
| 160 | relatórios | Pausar / reativar / excluir agendamento | Funciona; pausado não mostra "próxima execução" | 🔧 Pausado continuava mostrando "Próx: 10/10"; agora "Pausado". Excluir pede "Cancelar este agendamento?" |
| 161 | `/dashboard` | Gerar análise (IA) | Resumo direto | 🔧 Começava com "Entendido. Como CSO especialista em PEI…" (eco do prompt). Instruções da IA unificadas em `InstrucoesDaIa` (Gemini, OpenAI, Vertex): sem preâmbulo, linguagem da administração pública. Conferido: resposta começa direto pelo conteúdo |
| 162 | dashboard | Atualizar gráficos; sino "marcar todas como lidas" | Funciona | ✅ contador 7 → zerado |
| 163 | `/pei/mapa` | Modo agrupado/individual; memória de cálculo | Abre | ✅ 🔧 índice inválido vindo do navegador dava 500; agora ignora |
| 164 | mapa | Perspectiva sem indicador nenhum | "Sem medição", cinza | ❌ Mostra **0% vermelho (Crítico)** — indicador sem medição conta como 0 nos agregados. Correção entregue a um agente (Mapa, Dashboard, objetivo, página pública, relatórios) |
| 165 | `/agenda2030` | Selecionar ODS 3, ODS inválido, ODS 1 | Lista objetivos; inválido sem erro; aviso de aderência sem objetivo | ✅ |
| 166 | `/configuracoes` | Testar conexão da IA | Sucesso sem expor chave | ✅ "Conexão com Vertex AI estabelecida com sucesso!"; nenhuma chave no HTML |
| 167 | `/organizacoes` | Editar TST (abre preenchido); nova unidade com sugestão de sigla da IA → aplicar → filha da TST → Salvar | Funciona | ✅ sigla sugerida "SEAC" aplicada; "[TESTE] Secretaria de Atendimento ao Cidadão" criada sob a TST |
| 168 | **perfil Consulta** (impersonação) | 26 telas: o que abre e que ações de escrita aparecem | Lê tudo da unidade; nenhuma escrita; telas de administração fechadas | ✅ nenhuma ação de escrita; Usuários, Auditoria, Configurações, Perfis redirecionam; Ciclos 403; seletor de unidade só TST e a subordinada |
| 169 | Consulta | Chamar direto `create` em Valores, Riscos, Indicadores e Iniciativas | Servidor recusa | ✅ 403 nos quatro |
| 170 | **perfil Administrador da Unidade** (Gestora) | Mesmas telas | Escreve na própria unidade; sem telas do sistema | ✅ ações de escrita presentes; Usuários só com contas da TST; Auditoria, Configurações, Perfis fechados |
| 171 | Administrador da Unidade | Trocar a unidade do topo para o MIDR (chamada direta) | Ignorado | ✅ continua na TST |
| 172 | **perfil Gestor Substituto** | Telas da iniciativa e do restante | Escreve só na iniciativa; não designa gestores | ✅ entregas, RACI e comunicação; Indicadores/Objetivos/Valores/Riscos sem escrita; Usuários fechado |
| 173 | Gestor Substituto | Criar entrega (tela) / criar indicador / designar gestor (chamadas diretas) | Entrega sim; resto 403 | ✅ entrega criada; 403 e 403 |
| 174 | `/pei/mapa` (após correção) | Perspectiva sem indicador | Sem número, cinza | 🔧✅ "Aprendizado e Crescimento": atingimento nulo, #6b7280; a outra 102,9% verde. Mesma regra no Dashboard/IQG, detalhe de objetivo e perspectiva, portal público, ODS e relatórios (`AgregadoSemMedicaoTest`, 7 testes, mutação) |
| 175 | migrations | `2026_10_04_150000_adiciona_unidade_em_pei_audits` e `2026_10_04_150100_create_pei_tab_atividade_leitura_table` no dev | Aplicadas | ✅ DONE |
| 176 | **sino – aba Atividade** (melhoria pedida) | Admin altera risco da TST → Consulta da TST abre o sino | Vê a alteração com texto humano e nível | ✅ "Usuário Administrador alterou 3 campos do risco «…» (título, probabilidade, nível do risco) — há 43 segundos"; auditoria gravou a unidade TST. 🔧 mudança de probabilidade/nível de risco passou de "Informativo" para "Atenção" (teste ampliado) |
| 177 | sino | Contador e "Marcar como lido" | Badge 1 → 0 | ✅ "0 alertas não lidos e 0 atividades novas" |
| 178 | sino | Atualização automática | 1 poll de 60 s só com a aba visível | ✅ `wire:poll.60s.visible="atualizarContadores"` (o de 30 s é do painel, já existia) |
| 179 | sino | Largura de 360 px | Cabe | ⏳ não conferido: a janela do navegador está maximizada e não redimensionou |
| 78 | suíte completa | 1ª rodada | — | ⚠️ 11 falhas "tabela não existe": colisão — rodei outro teste no mesmo banco de teste durante a suíte. Refazer sozinha |

## Pedidos do gestor durante o teste (obrigatórios)

- Seção educativa de cada tela restrita ao tema da tela; conferir as telas onde ela falta.
- Indicadores: os campos de **meta prevista** e **realizado** precisam aplicar a máscara certa para cada **unidade de medida** escolhida (testar cada unidade).

## Pendências criadas por este teste

- [TESTE] Órgão Central e o ciclo [TESTE] ficam no banco: o gestor informou em 04/10/2026 que este ambiente é de teste.
- ~~Migration `2026_10_03_230000_entrega_exige_iniciativa` precisa rodar no dev~~ — rodada (linha 16).
- Seções educativas mantidas de propósito: `/pei/ciclos` (o roteiro de montagem do ciclo é o tema do ciclo). Sem seção: as 11 telas de detalhe (`/…/{id}/detalhes`, a explicação está na tela da lista), `/relatorios/historico` (tem aviso "O que é esta tela") e `/ajuda/papeis` (a tela inteira é ajuda).
- ~~SWOT ainda sem teste:~~ testado (linhas 69–71). Cenários (novo/editar/excluir) e TOWS (novo/editar/salvar/excluir).
