# Resumo das correções, por tela — 03/10/2026

Na ordem do menu. Cada linha diz o que alguém navegando o sistema via **antes** e o que vê **agora**.
O detalhe técnico e o resultado dos testes por perfil estão em `RELATORIO-DE-TESTES-E-CORRECOES.md`.

## Os três pontos que o gestor da Presidência levantou na reunião

| O que ele viu | Causa | Agora |
|---|---|---|
| **"Vazamentos de condições dentro de determinados perfis"** | Confirmado. O perfil valia em todas as unidades da pessoa. O Substituto podia se promover a Responsável. Qualquer conta logada (inclusive do autocadastro) lia o diretório de usuários. "Sem unidade" virava "todas as unidades". | Cada perfil vale só onde foi dado. Testado no navegador com 6 usuários, um por situação (tabela no relatório). Novo perfil **Consulta**, só leitura. |
| **Grau de Satisfação: percentual máximo travado em 100%, não aceitava 29,99** | O campo aceitava no máximo 5 dígitos e jogava o cursor para o fim. Com "100,00" preenchido, todo dígito novo era cortado. | O campo aceita 29,99 digitado e editado normalmente. Testado digitando no navegador e gravado no banco. Faixas não podem mais se sobrepor. |
| **Telas da Agenda 2030 sem sentido claro** | Números errados (total de ODS fixo, vínculos contados como objetivos, 0% sem indicador) e nenhuma explicação. | Números corretos, legenda, alerta "declarado sem objetivo", quadro "Para que serve esta tela" e seção própria no manual. |

## Tela a tela

| Tela | Antes | Agora |
|---|---|---|
| **Login / topo / portal** | Nome "Laravel" ou siglas fixas ("SEAE", "SPS"); mensagens em inglês ou como "validation.required"; conta do autocadastro presa num laço de redirecionamento | Nome do sistema em todo lugar; mensagens em português; conta sem perfil vai para "Acesso aguardando liberação" |
| **Menu** | Mostrava telas que davam erro para o perfil | Mostra só o que o perfil abre |
| **Seletor de unidade** | Podia cair em unidade sem perfil, ou em "todas" | Oferece só as unidades do perfil (e as subordinadas, para Administrador e Consulta) |
| **Dashboard** | Gráfico despencava para 0% em mês sem lançamento; "menor é melhor" ao contrário; índice da instituição inteira ao lado do nome da unidade, como se fosse dela | Mês sem dado em branco; polaridade respeitada; o índice diz que é do ciclo inteiro |
| **Ciclos do PEI** | "Planos Estratégicos Institucionais"; "Editar" sem ação; atalhos abriam outro ciclo | "Planejamento Estratégico Integrado"; edição só para o Super Admin; atalhos certos |
| **Inaugurar e Integrar** | "Média" não salvava; calendário não mostrava evento vencido | Salva; evento vencido marcado "Atrasado" |
| **Missão, Valores, PESTEL, SWOT, Temas, RAE** | Gestor reescrevia a unidade inteira; Administrador de uma unidade alterava a de outra | Administrador da unidade escreve; Gestor e Consulta leem |
| **Perspectivas, Objetivos, Graus, Cadeia de Valor** | Qualquer unidade alterava o que vale para a instituição inteira | Só o Super Admin ou o Administrador da unidade raiz |
| **Detalhes (perspectiva, valor, grau, objetivo)** | "Em breve", contadores fixos, "Editar" sem ação, 0% sem indicador | Dados reais; "Sem indicador"; ODS na ficha |
| **Mapa Estratégico / Portal** | 0% sem medição; portal com número diferente do Mapa (0% contra 10%); visitante gravava iniciativa | "Sem indicador"; mesmo número; somente leitura |
| **Agenda 2030** | Ver os três pontos acima | Ver acima |
| **Indicadores** | Ciclos misturados; IA de mentira; meta inexistente como 0,00; indicador podia ser ligado a outra unidade | Filtra o ciclo; IA real; "—"; vínculo só no próprio escopo |
| **Iniciativas** | Ficha sem os gestores; Gestor criava iniciativa e não conseguia editá-la; excluir deixava entregas e vínculos órfãos | Gestores na ficha; Administrador cria e designa; excluir leva os dependentes junto |
| **Gestores e Responsáveis** | Substituto se promovia a Responsável; dava para designar gente de qualquer unidade | Só o Administrador designa, e só gente da unidade |
| **Entregas** | Substituto excluía entregas, inclusive definitivamente; anexo aceitava `.html` | Substituto não exclui; anexo só em formatos de documento e imagem |
| **Gestão de Riscos** | Mitigação e ocorrência não salvavam; **"Cancelar" na confirmação excluía o risco mesmo assim**; riscos de outras unidades visíveis | Salva; Cancelar cancela; só os riscos do escopo |
| **Relatórios** | Tipo de arquivo errado; exportava qualquer unidade; "Objetivos Táticos"; "de 18 ODS" fixo; PDF da RAE com erro para o Substituto | Download correto; só o escopo de quem exporta; nomes e totais corretos |
| **Organizações** | Cadastrar a unidade raiz dava erro de banco com SQL na tela; uma unidade podia virar filha da própria subordinada (o sistema travava); detalhe mostrava e-mails de qualquer unidade | Raiz cadastra; ciclo bloqueado; só o escopo |
| **Usuários** | Qualquer conta logada lia o diretório inteiro; status "Ativo" invisível; editar apagava o vínculo do Gestor com a iniciativa | Só o Administrador, e só a própria unidade; status visível; vínculo preservado |
| **Perfis / Auditoria / Ajuda** | Matriz desenhada à mão; nomes de classe do código; ajuda prometia "ver dados restritos" que não existia | Matriz lida da regra real, com o perfil Consulta; nomes legíveis; ajuda com as regras em vigor |
| **Troca de ciclo no topo** | Às vezes "voltava" sozinha | Corrigido (2 perdas em 6 antes; 0 em 6 depois) |
| **Horários** | 3 horas adiantados | Horário de Brasília |

**O que eu não consigo afirmar:** não tenho a lista completa do que o gestor da Presidência apontou. Os
três pontos acima são os que você me relatou. O resto é tudo o que encontrei percorrendo as telas com
cada perfil.
