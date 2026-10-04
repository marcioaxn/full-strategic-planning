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

## Pedidos do gestor durante o teste (obrigatórios)

- Seção educativa de cada tela restrita ao tema da tela; conferir as telas onde ela falta.
- Indicadores: os campos de **meta prevista** e **realizado** precisam aplicar a máscara certa para cada **unidade de medida** escolhida (testar cada unidade).

## Pendências criadas por este teste

- [TESTE] Órgão Central existe no banco de dev e **deve ser excluída** ao final.
- ~~Migration `2026_10_03_230000_entrega_exige_iniciativa` precisa rodar no dev~~ — rodada (linha 16).
- Seções educativas mantidas de propósito: `/pei/ciclos` (o roteiro de montagem do ciclo é o tema do ciclo). Sem seção: as 11 telas de detalhe (`/…/{id}/detalhes`, a explicação está na tela da lista), `/relatorios/historico` (tem aviso "O que é esta tela") e `/ajuda/papeis` (a tela inteira é ajuda).
- ~~SWOT ainda sem teste:~~ testado (linhas 69–71). Cenários (novo/editar/excluir) e TOWS (novo/editar/salvar/excluir).
