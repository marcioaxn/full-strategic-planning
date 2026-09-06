# 10 — Histórico de Relatórios: a tela que ninguém consegue explicar

> **Tema:** F — Relatórios como produto · **Tipo:** Correção
> **Impacto:** médio · **Risco de regressão:** baixo
> **Depende de:** [09](09-reconstrucao-ui-dos-relatorios.md) para o vocabulário e o design
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "Alguns clientes tiveram dúvidas quanto ao item `/relatorios/historico` e para ser sincero eu não
> consegui esclarecer. Tudo nesse sistema precisa ser intuitivo e objetivo de forma que o cliente
> não tenha dúvida no que está trabalhando. Nesse caso e em todas partes onde pode gerar dúvidas é
> necessário um texto humanizado explicando. Além disso no caso do `/relatorios/historico` seria
> importante mostrar apenas quando for possível confirmar que a fila e todas as configurações
> necessárias foram feitas."

---

## 2. 🔴 Conclusão primeiro: o gestor não conseguiu explicar porque não há o que explicar

**A tabela do Histórico está vazia em praticamente toda instalação — e vai continuar vazia.**

Não é um problema de texto faltando. A tela funciona exatamente como foi escrita, e o que foi
escrito produz uma página em branco permanente.

### 2.1 A prova, em uma linha

`RelatorioGerado` — o modelo que alimenta o Histórico — é instanciado em **exatamente um lugar**
em todo o projeto:

```
$ grep -rn "RelatorioGerado::create\|new RelatorioGerado" app/
app/Console/Commands/ProcessScheduledReports.php:100
```

Ou seja: **só o processador de agendamentos registra histórico.** Os relatórios que o cliente
baixa clicando em `/relatorios` — que são todos, no uso real — **não são registrados**.

### 2.2 E o processador de agendamentos não roda

`bootstrap/app.php:22-28`:

```php
$schedule->command('reports:process-scheduled')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
```

O agendador do Laravel só existe se alguém chamar `php artisan schedule:run` **a cada minuto**,
pelo cron do sistema operacional. Este produto roda em **Apache no Windows** — o que significa
uma Tarefa Agendada do Windows, configurada à mão, em cada instalação de cliente.

Nada no sistema verifica se ela existe. Nada avisa quando ela falta.

### 2.3 O que o cliente vive

| Passo | O que acontece |
|---|---|
| 1 | Vai em `/relatorios`, gera o Dossiê, baixa o PDF ✅ |
| 2 | Clica em "Histórico de Relatórios Gerados" |
| 3 | Tabela **vazia** |
| 4 | *"Mas eu acabei de gerar um relatório."* |
| 5 | Pergunta ao gestor, que também não sabe |

O passo 4 é o relato exato do pedido. A tela promete "Histórico de Relatórios Gerados" e mostra
apenas os relatórios gerados por um agendador que não está rodando. **O rótulo é honesto sobre a
intenção e desonesto sobre o resultado** — mandamento nº 6c do `CLAUDE.md`.

---

## 3. Os quatro problemas, separados

### P1 — Geração sob demanda não registra histórico
`RelatorioController` devolve o download direto, sem gravar `RelatorioGerado`. **É a causa
principal da tela vazia.**

### P2 — Não há verificação de que o agendador está configurado
O sistema oferece agendamento (`AgendarRelatorio`, `GerenciarAgendamentos`, `RelatorioAgendado`)
sem nunca confirmar que a infraestrutura existe. O cliente agenda um relatório mensal, confia, e
ele nunca chega. **É pior que a tela vazia: é uma promessa que o sistema não cumpre e não avisa.**

### P3 — Não há texto explicando o que a tela é
Mesmo funcionando, "Histórico de Relatórios Gerados" não distingue: histórico de quê? Meus ou de
todos? Por quanto tempo ficam guardados? Quem mais pode baixar?

### P4 — 🔒 Achado de segurança no caminho de download
Registrado em `.claude/seguranca-em-aberto.md`, item 6. **Não descrito aqui: `documentacao/` é
versionada em repositório público, e publicar falha ainda aberta é entregar o mapa.**

Ele é **pré-requisito**: as correções abaixo aumentam o uso do Histórico, e aumentar o uso de um
caminho com falha aberta amplia a exposição. **Corrigir o item 6 vem antes de tudo nesta demanda.**

---

## 4. Soluções propostas

### 4.1 Registrar toda geração (resolve P1) ✅

Centralizar em `ReportGenerationService`: toda geração — sob demanda ou agendada — grava
`RelatorioGerado` com tipo, formato, filtros aplicados, tamanho e autor.

Duas decisões que precisam do gestor:

| Decisão | Opções | Recomendação |
|---|---|---|
| **Guardar o arquivo ou só o registro?** | (a) Só metadados; (b) arquivo por N dias | **(a) para sob demanda, (b) para agendado.** Guardar todo PDF gerado enche o disco do cliente sem que ninguém peça |
| **Retenção** | 30 / 90 / 180 dias | **90 dias**, configurável — e é **medida**, não escolhida: depende do tamanho médio do PDF e da frequência de geração |

Com a opção (a), a linha do histórico vira: *"Dossiê Estratégico · PDF · 12/03/2026 14:22 ·
gerado por Maria Silva · [Gerar novamente]"* — mais útil que um arquivo velho, porque regera com
dado atual.

### 4.2 Diagnóstico da infraestrutura (resolve P2) ✅ **o coração do pedido**

O gestor pediu: *"mostrar apenas quando for possível confirmar que a fila e todas as configurações
necessárias foram feitas."* Isso exige **verificar de fato**, não presumir.

Um `DiagnosticoAgendamentoService` que responde, com evidência:

| Verificação | Como se comprova | Se falhar |
|---|---|---|
| Agendador rodando | Um "heartbeat" gravado em `system_settings` a cada `schedule:run`; se o carimbo tem mais de 5 min, não está rodando | 🔴 Agendamento não funciona |
| Tabela `pei.jobs` existe | `Schema::hasTable('pei.jobs')` | 🟡 Só afeta se usar fila |
| `QUEUE_CONNECTION` | `config('queue.default')` | ℹ️ Informativo |
| Worker ativo | Só relevante se algo for enfileirado | ℹ️ Hoje nada é |
| Disco gravável | `Storage::disk(...)->put()` de teste | 🔴 Nada é gerado |

> ⚠️ **Correção de premissa:** o gestor falou em "fila", e é natural — mas **hoje o projeto não
> usa fila para relatórios**. Não existe `app/Jobs/`, e nada implementa `ShouldQueue` além de
> `WelcomeUserMail`. O agendamento é síncrono, dentro de um comando artisan disparado pelo
> agendador. **O que precisa ser verificado é o agendador do sistema operacional, não uma fila.**
> Chamar de "fila" o que é cron levaria a diagnosticar a coisa errada.

**Comportamento resultante:**
- Agendador confirmado → Histórico e Agendamentos aparecem normalmente
- Agendador não confirmado → o **agendamento** some do menu (não se oferece o que não funciona);
  o **Histórico** permanece, porque com 4.1 ele passa a ter conteúdo próprio
- Uma tela `/configuracoes/diagnostico` mostra o resultado e **o comando exato** para corrigir

### 4.3 Textos humanizados (resolve P3) ✅

Na tela de Histórico:

> **O que é esta tela.** Registro dos relatórios que **você** gerou, com a data, o formato e os
> filtros usados. Serve para você reencontrar um relatório que apresentou e refazê-lo com os
> mesmos critérios.
> Os arquivos ficam guardados por **90 dias**; depois disso, a linha continua aqui e você pode
> gerar de novo — com os dados atualizados.

E o padrão que o gestor pediu para todo o sistema: **um bloco "O que é esta tela" onde o nome não
se explica sozinho.** Candidatos imediatos, além deste: Graus de Satisfação
([06](06-grau-de-satisfacao-antes-do-indicador.md)), Cadeia de Valor, RAE, Cenários Prospectivos,
TOWS, Memória de Cálculo.

**Regra para não virar poluição:** só onde o nome não basta; sempre com um exemplo concreto;
recolhível, e a preferência é lembrada. Explicação que atrapalha quem já entendeu vira ruído.

---

## 5. Plano de ação

1. 🔒 **Corrigir o item 6 de `.claude/seguranca-em-aberto.md`.** Pré-requisito de tudo abaixo.
2. Registrar geração sob demanda no `ReportGenerationService` (P1).
3. Decidir com o gestor: guardar arquivo? retenção? (§4.1)
4. `DiagnosticoAgendamentoService` com heartbeat (P2).
5. Tela `/configuracoes/diagnostico`.
6. Esconder Agendamentos quando o agendador não estiver confirmado.
7. Aviso em `AgendarRelatorio`: não deixar agendar sem infraestrutura.
8. Texto humanizado no Histórico (P3).
9. Componente `<x-ajuda-tela>` reutilizável, recolhível, com preferência lembrada.
10. Aplicar às 6 telas candidatas.
11. Documentar a configuração da Tarefa Agendada no roteiro de instalação — **hoje isso não está
    documentado em lugar nenhum**, e é o que faltou em cada cliente.

---

## 6. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R10.0** 🔒 Segurança | Item 6 corrigido | Teste de acesso indevido falha | — |
| **R10.1** Decisões | Guardar arquivo? Retenção? | Registrado aqui | — |
| **R10.2** Registro | Toda geração grava `RelatorioGerado` | Gerar um relatório → linha no Histórico | R10.0 |
| **R10.3** Heartbeat | Carimbo a cada `schedule:run` | Carimbo atualiza com o agendador ligado | — |
| **R10.4** Diagnóstico | `DiagnosticoAgendamentoService` | Detecta agendador parado em até 5 min | R10.3 |
| **R10.5** Tela | `/configuracoes/diagnostico` com o comando de correção | Gestor corrige sem suporte | R10.4 |
| **R10.6** Menu condicional | Agendamentos some sem infraestrutura | Rota também protegida, não só o menu | R10.4 |
| **R10.7** Aviso no agendamento | Não deixa agendar sem infraestrutura | Tela | R10.4 |
| **R10.8** Texto do Histórico | Bloco "O que é esta tela" | Gestor aprova | R10.2 |
| **R10.9** `<x-ajuda-tela>` | Componente recolhível com preferência | Tela | R10.8 |
| **R10.10** 6 telas | Ajuda nas telas candidatas | Gestor aprova cada texto | R10.9 |
| **R10.11** Retenção | Limpeza automática | Arquivo além do prazo é removido; registro fica | R10.1 |
| **R10.12** Documentação | Tarefa Agendada no roteiro de instalação | Cliente configura sozinho | R10.5 |

---

## 7. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 10-B01 | 🔒 Corrigir o item 6 de segurança | M | — | Teste de acesso indevido |
| 10-B02 | 🔒 Teste de regressão do item 6 | M | B01 | Verde |
| 10-B03 | Decidir retenção e guarda de arquivo | P | — | Registrado |
| 10-B04 | Registrar geração sob demanda | M | B01 | Linha aparece no Histórico |
| 10-B05 | Teste: gerar relatório cria `RelatorioGerado` | M | B04 | Verde |
| 10-B06 | Heartbeat do agendador em `system_settings` | M | — | Carimbo atualiza |
| 10-B07 | `DiagnosticoAgendamentoService` | M | B06 | B08 |
| 10-B08 | Teste: agendador parado é detectado | M | B07 | Verde |
| 10-B09 | Tela `/configuracoes/diagnostico` | M | B07 | Mostra o comando exato |
| 10-B10 | Entrada do diagnóstico na `MATRIZ` | P | B09 | Perfil certo acessa |
| 10-B11 | Menu de Agendamentos condicional | P | B07 | Some sem infraestrutura |
| 10-B12 | **Rota** de agendamentos também protegida | P | B11 | Acesso direto por URL barrado |
| 10-B13 | Aviso em `AgendarRelatorio` | P | B07 | Tela |
| 10-B14 | Texto humanizado do Histórico | P | B04 | Gestor aprova |
| 10-B15 | `<x-ajuda-tela>` recolhível | M | B14 | Preferência lembrada |
| 10-B16 | Ajuda em Graus de Satisfação | P | B15 | Gestor aprova |
| 10-B17 | Ajuda em Cadeia de Valor, RAE, Cenários, TOWS, Memória de Cálculo | M | B15 | Gestor aprova cada uma |
| 10-B18 | Comando de limpeza por retenção | M | B03 | Arquivo velho some, registro fica |
| 10-B19 | Botão "Gerar novamente" com os filtros salvos | M | B04 | Mesmos filtros |
| 10-B20 | Documentar a Tarefa Agendada no roteiro | M | B09 | Cliente configura sozinho |
| 10-B21 | Aplicar o design de [09](09-reconstrucao-ui-dos-relatorios.md) à tela | P | [09] | Visual |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 8. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

O pedido tem duas metades, e a segunda é a difícil.

1. **"O cliente não pode ter dúvida":** gerar um relatório em `/relatorios` e ir ao Histórico.
   A linha tem de estar lá. É a expectativa que hoje é frustrada — e a única prova que responde
   ao passo 4 da seção 2.3.
2. **"Só mostrar quando for possível confirmar":** o diagnóstico precisa ser testado nos **dois
   estados**. Desligar a Tarefa Agendada, esperar a janela do heartbeat, e confirmar que o
   sistema **percebe e reage**. Um diagnóstico que só foi testado com tudo funcionando não
   diagnostica nada — só concorda.
3. **10-B12:** esconder do menu e deixar a rota aberta seria teatro. O teste precisa acessar a
   URL direto.
4. 🔒 **10-B02**, antes de qualquer aumento de uso do Histórico.
5. **Os textos, com o gestor.** Se ele — que conhece o sistema — não conseguiu explicar a tela,
   o texto está certo quando **ele** lê e diz "agora dá para explicar". Não há teste automático
   para isso, e é o critério que fecha a demanda.
6. `php artisan view:clear && php artisan config:clear` antes de julgar tela.

---

## 9. O que NÃO foi verificado

- **Se a Tarefa Agendada existe em alguma instalação de cliente** — a suposição de que não existe
  vem da ausência de documentação e do sintoma relatado, não de medição
- Quantas linhas há hoje em `pei.tab_relatorios_gerados` em base real (exige autorização)
- Se algum cliente já agendou relatório e está esperando algo que nunca virá — **esta é a pergunta
  mais urgente da demanda**, e ela é do gestor
- Se `RelatorioAgendado` tem campo de última execução que confirmaria ou negaria o diagnóstico
- Tamanho médio dos PDFs gerados — necessário para a decisão de retenção (§4.1), que é medida e
  não escolhida
- Se `ProcessScheduledReports` trata falha por agendamento ou aborta o lote inteiro — só as
  primeiras 60 linhas foram lidas
- Se existe algum outro ponto do sistema que dependa de fila e esteja igualmente inerte
