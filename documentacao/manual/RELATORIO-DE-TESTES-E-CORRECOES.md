# Relatório de testes e correções — 03/10/2026

## Como foi testado

Teste em duas rodadas.

**Primeira rodada.** Tela a tela, com o Super Administrador e um ciclo de teste. Cada botão foi acionado:
inserir, editar, excluir, detalhar e gerar PDF. O conteúdo dos documentos foi conferido.

**Segunda rodada, de permissões.** Montei uma estrutura neutra, sem relação com nenhum cliente, para
reproduzir o cenário de um órgão com unidades:

- **[TESTE] Órgão Central** (raiz):
  - **Secretaria A**, que tem abaixo a **Diretoria A1**;
  - **Secretaria B**.
- Uma iniciativa, um risco e um item de análise em cada secretaria.
- Seis usuários, um por situação:
  - Administrador da Secretaria A;
  - Gestor Responsável da iniciativa A;
  - Gestor Substituto da iniciativa A;
  - Consulta da Secretaria A;
  - uma pessoa que é Administradora na A e Gestora Substituta na B;
  - uma conta criada pelo autocadastro.

Cada um entrou com login próprio. Em cada perfil, verifiquei pelo navegador:

1. o que abre e o que é negado, nas 32 telas;
2. quais botões de criação aparecem;
3. o que acontece quando se aciona diretamente, pelo navegador, uma ação que o perfil não tem — como faria
   quem conhece o sistema.

Toda a estrutura de teste foi excluída pelas próprias telas ao final, e o banco foi conferido.

Suíte automatizada: **314 testes passando, 0 falhas** (7 ignorados por dependerem de recurso externo).

## Permissões por perfil — resultado no navegador

| Perfil | Telas que abrem | O que foi negado ao tentar direto |
|---|---|---|
| **Super Administrador** | 32 de 32 | — |
| **Administrador da Secretaria A** | 28 de 32 (sem Ciclos, Perfis, Configurações, Auditoria) | Abrir as entregas e os gestores da iniciativa da Secretaria B; editar e excluir o risco da Secretaria B; trocar a unidade do topo para a Secretaria B; excluir item da PESTEL da Secretaria B. O seletor de unidade oferece só a Secretaria A e a Diretoria A1. |
| **Gestor Responsável** | 27 de 32 (lê todo o planejamento da unidade) | Criar iniciativa; designar gestores; abrir a iniciativa da Secretaria B; criar item na PESTEL. **Permitido:** criar e excluir entrega na própria iniciativa. |
| **Gestor Substituto** | 27 de 32 | Promover a si mesmo a Responsável; excluir entrega (normal e definitivo). Nenhum botão de criação aparece. |
| **Consulta** (novo) | 27 de 32 | Criar ou editar risco; criar entrega. **Permitido:** exportar o PDF de riscos — e o PDF traz **só** o risco da Secretaria A. |
| **Administrador na A e Substituto na B** | — | Na Secretaria B: o botão de excluir não aparece e a exclusão direta é recusada; o item continua no banco. Na Secretaria A: cria normalmente. |
| **Conta do autocadastro, sem perfil** | Nenhuma | Todas as telas restritas (painel, usuários, riscos, relatórios, PDF) levam à página "Acesso aguardando liberação". |

## O que estava errado e foi corrigido

### Permissões — a suspeita do gestor da Presidência se confirmou

- **O perfil valia em todas as unidades.** Quem era Administrador numa unidade e Gestor em outra tinha poder
  de Administrador nas duas. Agora cada perfil vale onde foi dado. Administrador e Consulta valem também nas
  unidades abaixo; Gestores, só na própria.
- **O Gestor Substituto podia se promover a Responsável**, ou dar o papel a qualquer pessoa. Agora só quem
  administra a unidade designa gestores, e só pessoas da própria unidade.
- **Qualquer conta logada lia o diretório de usuários**, inclusive a criada pelo autocadastro. Agora só o
  Administrador o lê, e apenas o da própria unidade. Conta sem perfil não entra na área restrita.
- **"Sem unidade" virava "todas as unidades"** em várias telas (riscos, PESTEL, SWOT, temas, lições e
  painel). Agora quem não é Super Admin sempre opera numa unidade do próprio escopo.
- **O Gestor Responsável reescrevia o planejamento da unidade inteira** (missão, PESTEL, SWOT, objetivos,
  RAE). A tela de ajuda dizia o contrário. Agora ele lê o planejamento e atua só nas iniciativas pelas quais
  responde.
- **Dados da instituição inteira podiam ser alterados por qualquer unidade**: perspectivas, objetivos,
  faixas do farol, cadeia de valor e abertura do ciclo. Agora só o Super Admin ou o Administrador da
  unidade raiz.
- **Ações sem conferência**: excluir entrega, vincular indicador a outra unidade, anexar arquivo `.html`,
  trocar o "pai" de uma unidade por uma subordinada (o sistema travava), relatório agendado de usuário
  desligado continuar sendo enviado, e outras de mesma natureza. Todas fechadas, cada uma com teste.
- **Botões que davam erro** apareciam para quem não podia usá-los. Agora cada botão aparece só para quem a
  ação vai funcionar. O menu também esconde o que o perfil não abre.
- **Na lista de riscos, "Cancelar" na confirmação de exclusão excluía o risco mesmo assim.**

### Encontrado nos testes com os perfis (também corrigido)

- **O Gestor ficava sem acesso a quase tudo** quando era retirado de uma iniciativa de outra unidade: o
  sistema o colocava naquela unidade, onde ele não tinha mais perfil. O escopo agora vem só dos vínculos de
  perfil.
- **Excluir uma iniciativa deixava vivos** as entregas, os indicadores e o vínculo de Gestor dela. Agora
  vão junto (exclusão lógica). Uma migration corrige o que já estava assim na base.
- **Cadastrar a unidade raiz** — primeiro passo de todo cliente novo — dava erro de banco e mostrava o SQL
  na tela.
- **A conta do autocadastro entrava em laço de redirecionamento** (troca de senha ↔ acesso pendente) e o
  navegador desistia.

### Números e textos que enganavam

- O **Portal da Transparência** publicava um percentual diferente do Mapa interno (0% contra 10%). Agora
  usa o mesmo cálculo.
- O **Dashboard**:
  - punha 0% em mês sem lançamento;
  - ignorava "menor é melhor";
  - exibia o índice da instituição inteira ao lado do nome da unidade, como se fosse dela. Agora diz que é
    do ciclo inteiro.
- **Agenda 2030**:
  - total de ODS escrito à mão;
  - vínculos contados como objetivos;
  - 0% onde não havia indicador;
  - aderência declarada ignorada;
  - tela sem explicação.

  O relatório integrado também trazia "de 18 ODS" fixo.
- Detalhes de perspectiva, valor, grau e usuário tinham contadores fixos e "em breve". Objetivos e mapa
  mostravam 0% sem medição; agora mostram "Sem indicador".
- "Planejamento Estratégico **Institucional**" corrigido para **Integrado** em cerca de 25 pontos.
- A marca aparecia diferente em cada tela ("SEAE", "SPS", "Laravel"). Agora é a mesma em todas.
- Status de usuário invisível, plurais e acentos errados, nomes técnicos na auditoria, "Objetivos Táticos".

### Funcionalidades que não funcionavam

- **Graus de Satisfação:**
  - o percentual máximo ficava travado em 100,00 e não aceitava 29,99, como o cliente mostrou na reunião;
  - faixas podiam se sobrepor;
  - a opção "Escala Global" não funcionava e foi retirada.
- **Mitigação e ocorrência de risco** não salvavam (erro de banco).
- **Botões sem efeito ou com erro:**
  - vários "Editar" não faziam nada;
  - a sugestão de indicadores por IA devolvia sempre as mesmas duas respostas;
  - a intensidade "Média" não salvava;
  - os downloads saíam com o tipo de arquivo errado.
- **Ficha e PDFs:**
  - a ficha da iniciativa não mostrava os gestores;
  - o PDF da RAE dava erro para o Gestor Substituto;
  - o PDF da Cadeia de Valor saía sem conferir permissão.
- **Navegação e eventos:**
  - o logotipo do portal levava para fora do sistema;
  - o calendário não indicava evento atrasado.

### Estabilidade e ambiente

- **A troca de ciclo no topo "se perdia"** quando o painel se atualizava ao mesmo tempo. Reproduzido no
  navegador: perdeu-se em 2 de 6 tentativas; com a correção, em 0 de 6.
- **Erro intermitente "chave da aplicação ausente"** no servidor Windows: 173 ocorrências registradas no
  dia. Causa: leitura do `.env` não isolada entre requisições simultâneas. Corrigido; nenhuma ocorrência
  depois.
- **Horários 3 horas adiantados.** A aplicação gravava em UTC e o banco em horário de Brasília. Tudo está
  em Brasília agora. Registros anteriores mantêm o horário com que foram gravados.
- **Idioma e nome do sistema não dependem mais do `.env`.** Mensagens sempre em português; um `.env` com
  "Laravel" mostra "Sistema PEI".
- Milhares de mensagens técnicas deixaram de ser impressas no console do navegador.

## Implantação

Chamado em `documentacao/chamados/20261003-chamado-implantacao-correcoes-pei.docx`, em 3 passos:

1. `git pull`;
2. dependências, `php artisan migrate --force` (2 migrations novas) e o seeder de perfis, que cria o perfil
   **Consulta**;
3. limpeza de caches e reinício da fila.

Nenhuma alteração no `.env`.
