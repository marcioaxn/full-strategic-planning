# Resumo das correções, por tela — 03/10/2026

Na ordem do menu. Cada linha diz o que alguém navegando o sistema via **antes** e o que vê **agora**.
O detalhe técnico está em `RELATORIO-DE-TESTES-E-CORRECOES.md`.

| Tela | Antes (o que o gestor pode ter visto) | Agora |
|---|---|---|
| **Login / topo / portal** | Nome "Laravel" ou siglas fixas ("SEAE", "SPS"); mensagens de erro em inglês ou como "validation.required" | Nome do sistema em todo lugar; mensagens em português |
| **Dashboard** | Gráfico mensal despencava para 0% em mês sem lançamento; indicador "menor é melhor" contado ao contrário | Mês sem dado fica em branco; polaridade respeitada; cor por perspectiva |
| **Ciclos do PEI** | "Planos Estratégicos Institucionais"; "Editar PEI" não fazia nada; atalhos abriam outro ciclo | "Planejamento Estratégico Integrado"; edição funciona (só Super Admin); atalhos abrem o ciclo certo |
| **Inaugurar e Integrar** | Intensidade "Média" não salvava na edição e aparecia com a cor errada | Salva e exibe corretamente |
| **Valores / Perspectivas / Graus de Satisfação (detalhe)** | "Em breve", contadores zerados fixos, botão Editar sem ação | Dados reais e Editar funcionando |
| **Objetivos (detalhe)** | 0% para objetivo sem indicador; ODS não apareciam; Editar visível a quem não estava logado | "Sem indicador"; ODS na ficha; Editar só para quem pode |
| **Mapa Estratégico** | 0% onde não havia medição | "Sem indicador" / "—" |
| **Agenda 2030 (ODS)** | Dizia "17 ODS" com 18 cadastrados; contava vínculos como se fossem objetivos; 0% sem indicador; ignorava a aderência declarada; nenhuma explicação da tela | Total real; objetivos e vínculos separados; estrela no ODS declarado; alerta "declarado sem objetivo"; quadro "Para que serve esta tela"; seção própria no manual |
| **Indicadores** | Lista misturava ciclos; "Sugerir com IA" devolvia sempre as mesmas 2 sugestões; meta inexistente aparecia como 0,00; "Registrado!" ao editar | Filtra pelo ciclo; IA real; "—" quando não há meta; mensagens corretas |
| **Iniciativas** | Ficha não mostrava os gestores atribuídos; histórico com nomes de servidores aberto ao público | Gestores e responsáveis na ficha; histórico só para quem está logado |
| **Entregas** | Mensagens de sucesso trocadas; detalhe de entrega excluída quebrava | Corrigido |
| **Gestão de Riscos** | **Plano de mitigação e ocorrência não salvavam (erro de banco)**; matriz com rótulos desalinhados; riscos de outros ciclos | Salva (migration nova); matriz alinhada; filtra pelo ciclo |
| **Relatórios** | PDF e Word baixavam com tipo errado; exportava dados de qualquer unidade; "Objetivos Táticos"; textos "Institucional" nos documentos | Download correto; respeita permissão e unidade; nomes corrigidos |
| **Usuários / Perfis / Auditoria** | Status "Ativo" invisível; "1 Organizações"; matriz de perfis desenhada à mão (podia mentir); auditoria com nomes de classe do código | Status visível; plural certo; matriz lida da regra real; "Iniciativa", "Risco" etc. |
| **Portal da Transparência** | **Visitante sem login conseguia gravar iniciativa e usar a IA**; percentual diferente do Mapa (0% contra 10%); "51.8%"; logotipo levava para fora do sistema | Somente leitura de verdade; mesmo número da área interna; "49,3%"; logotipo volta ao início |

**Por trás das telas:** cerca de 40 ações de tela que gravavam dados não verificavam permissão. Todas
passaram a verificar, e cada uma tem teste que tenta a chamada direta.

**O que eu não consigo afirmar:** não tenho a lista do que o gestor da Presidência apontou. Esta tabela
é tudo o que encontrei percorrendo as telas. Se algum item dele não estiver aqui, é um ponto que não vi.
