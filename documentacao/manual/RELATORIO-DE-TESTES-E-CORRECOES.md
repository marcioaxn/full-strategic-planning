# Relatório de testes e correções — 03/10/2026

Teste feito tela a tela no navegador, com o perfil Super Administrador. Para inserir, editar e excluir foi
usado um ciclo de teste, "[TESTE] Ciclo 2040-2043". Ele foi apagado pela própria tela no final, e o banco
foi conferido: não sobrou registro de teste. Os PDFs e DOCX gerados foram abertos e o conteúdo conferido.

Suíte automatizada no fim: **265 testes passando, 7 pulados, 0 falhas**.

## 🔴 Pendências para implantar (fora do código)

| # | O quê | Quem |
|---|---|---|
| 1 | Rodar a migration nova `database/migrations/RiskManagement/2026_10_03_120000_alinhar_colunas_mitigacao_e_ocorrencia_de_risco.php` (com `--path`). Sem ela, salvar plano de mitigação e ocorrência de risco **falha**. | Infra |
| 2 | `.env`: `APP_NAME="Sistema PEI"` (hoje "Laravel", que aparece no topo, no login e no portal) e `APP_LOCALE=pt_BR` / `APP_FALLBACK_LOCALE=pt_BR` (hoje "en": mensagens de validação em inglês). | Infra / gestor |
| 3 | Fuso: `app.timezone` é UTC, então os horários exibidos ficam 3 h adiantados. A troca afeta dados já gravados em UTC. | Decisão do gestor |
| 4 | Envio automático de relatórios agendados depende do cron `schedule:run` (a tela já avisa). | Infra |

## O que estava quebrado ou enganoso e foi corrigido

### Segurança e permissões

- **~40 métodos de tela sem verificação de permissão.** Um visitante anônimo conseguia gravar iniciativa pelo Portal da Transparência. Todos os métodos de escrita agora verificam perfil e unidade. Há testes que tentam a chamada direta.
- **Relatórios exportavam dados de qualquer unidade.** Agora valem permissão e escopo da organização.
- **Ciclos PEI** podiam ser alterados por qualquer perfil. Agora só pelo Super Administrador.
- **Visitante via** botões de Lançar, Metas e Exportar, o histórico com nomes de servidores, e podia acionar a IA. Agora não.

### Números que não batiam

- **Portal da Transparência** calculava o atingimento por conta própria, só com indicadores: publicava 0% onde o Mapa e o Dashboard mostravam 10%. Agora usa o mesmo cálculo.
- **Dashboard**: mês sem lançamento entrava como 0%, criando uma queda falsa no gráfico, e a polaridade "menor é melhor" era ignorada.
- **Agenda 2030**: dizia "17 ODS" com 18 cadastrados e chamava de "objetivos" o que eram vínculos. Mostrava 0% para objetivo sem indicador. Ignorava a aderência declarada.
- Objetivo, perspectiva e mapa mostravam **0% onde não há medição**. Agora mostram "Sem indicador".
- Detalhes de perspectiva, valor, grau de satisfação e usuário tinham **contadores fixos e "em breve"**. Agora mostram dados reais.
- **Indicadores, riscos e relatórios** misturavam ciclos. Agora filtram pelo ciclo selecionado.
- Percentuais com ponto ("51.8%") e milhares sem separador. Agora estão no padrão brasileiro.

### Botões que não faziam o que diziam

- **Planos de mitigação e ocorrências de risco não salvavam**: as colunas do banco não batiam com o código. Corrigido com migration nova e teste pela tela.
- "Editar" em detalhes de ciclo, perspectiva, objetivo, organização, usuário e grau de satisfação não tinha ação. Agora abre a edição.
- "Sugerir indicadores com IA" devolvia **2 sugestões fixas**. Agora consulta a IA de verdade.
- Sugestão da IA com apóstrofo quebrava o botão em 7 telas.
- Inaugurar e Integrar: intensidade "Média" não salvava na edição.
- Downloads de PDF e DOCX chegavam com tipo de arquivo errado.
- A ficha da iniciativa não mostrava os gestores atribuídos na tela "Gestores e Responsáveis".
- O logotipo do portal levava para fora do sistema (raiz do servidor).

### Textos e identidade

- "Planejamento Estratégico **Institucional**" corrigido para **Integrado** em cerca de 25 pontos (telas, PDFs, Word, portal). Ficou "Institucional" só no título oficial do guia do MGI.
- A marca vinha fixa e diferente em cada tela ("SEAE" no login, "SPS" no portal). Agora sai do nome configurado do sistema.
- O status "Ativo" ficava invisível na lista de usuários.
- A auditoria mostrava nomes de classe ("ActionPlan\PlanoDeAcao"). Agora mostra "Iniciativa", "Risco" etc.
- O catálogo trazia "Objetivos **Táticos**". Agora diz "Objetivos Estratégicos".
- Mensagens de erro do sistema estavam sem tradução (lang/pt_BR criado), e havia acentos e plurais errados em várias telas.

## Pontos abertos (não corrigidos, sem impacto em dado)

- `app.js` imprime milhares de mensagens de depuração no console do navegador.
- Em dev (Windows/XAMPP), requisições simultâneas às vezes dão erro de chave da aplicação. É ambiente, não código.
- A troca de ciclo no topo às vezes se perde quando duas abas gravam a sessão ao mesmo tempo (sessão em banco sem trava).
- O calendário do Inaugurar não sinaliza eventos vencidos.
- Decisões tomadas e que o gestor pode rever:
  - Gestor Substituto não gera o PDF da RAE.
  - Agendar relatório "sem organização" é só para Super Administrador.
- Os prints do manual têm dados reais do ambiente de desenvolvimento (nomes de unidades e de usuários). Avaliar antes de publicar no repositório, que é público.
