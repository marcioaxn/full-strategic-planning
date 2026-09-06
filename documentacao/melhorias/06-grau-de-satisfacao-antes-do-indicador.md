# 06 — Grau de Satisfação: o cliente é mandado para uma tela que ele não pode abrir

> **Tema:** C — Honestidade metodológica da interface · **Tipo:** **Correção** (não é melhoria)
> **Impacto:** 🔴 **alto** — trava o preenchimento do ciclo e contamina o farol de todos os módulos
> **Risco de regressão:** médio (mexe na MATRIZ de capacidades)
> **Relacionada a:** [08](08-quem-gerencia-o-que-papeis-e-responsaveis.md), [12](12-visao-holistica-achados-transversais.md)
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "Para o cliente preencher os Indicadores seria importante já ter o Grau de Satisfação
> preenchido. Além de explicar o motivo do preenchimento — alguns clientes não identificam o
> [Grau de Satisfação] como o semáforo que mostrará o atingimento do resultado do indicador e é
> importante um texto humanizado para explicar."

---

## 2. 🔴 A conclusão, primeiro

**O sistema já manda o cliente preencher o Grau de Satisfação antes dos Indicadores — e a tela
para onde ele o manda devolve 403 para todo perfil que não seja Super Admin.**

Não é falta de orientação. É uma **contradição interna, reproduzível**, entre duas peças do
sistema que discordam uma da outra.

### 2.1 A prova, em três arquivos

**(a) O serviço de orientação manda ir:**

`app/Services/PeiGuidanceService.php:114-123`

```php
// --- PHASE 5: Grau de Satisfação (NEW) ---
$grausCount = \App\Models\StrategicPlanning\GrauSatisfacao::count();

if ($grausCount == 0) {
    $phases['graus']['status'] = 'active';
    return $this->buildResponse($phases, 'graus', 50, $pei,
        "Objetivos salvos!{$objetivoWarning} Agora, defina as cores e níveis do Grau de Satisfação.",
        'graus-satisfacao.index', 'Configurar Níveis');
}
```

A ordem metodológica já está implementada e está **correta**:
`Ciclo → Inaugurar → Identidade → Perspectivas → Objetivos → **Graus** → Indicadores`.

**(b) O componente exige a capacidade:**

`app/Livewire/StrategicPlanning/ListarGrausSatisfacao.php:63-65`

```php
public function mount()
{
    $this->authorize('modulo.acessar', 'graus-satisfacao');
```

**(c) A matriz não concede a capacidade a ninguém:**

`app/Services/Authorization/CapacidadeResolver.php:76`

```php
// Restritos a Super Admin: nenhum outro perfil recebe capacidade.
'graus-satisfacao' => [],
```

### 2.2 O que o cliente vive

| Passo | O que acontece |
|---|---|
| 1 | Termina de cadastrar os objetivos |
| 2 | O sistema exibe: *"Objetivos salvos! Agora, defina as cores e níveis do Grau de Satisfação."* |
| 3 | O cliente clica no botão **"Configurar Níveis"** |
| 4 | **403.** |
| 5 | O cliente vai preencher os Indicadores assim mesmo |
| 6 | O farol não acende, ou acende com o critério de outro PEI (ver seção 3) |

O passo 4 é o que aconteceu na Presidência da República. A demanda
[08](08-quem-gerencia-o-que-papeis-e-responsaveis.md) — "ficou sem entender quem poderá fazer a
gestão" — é o mesmo defeito visto de outro ângulo.

---

## 3. 🔴 Segundo achado: o farol mistura critérios de PEIs diferentes

`GrauSatisfacao` tem `cod_pei` **e** `num_ano` no `$fillable`. Ou seja: cada ciclo PEI tem a
própria faixa de semáforo, e o modelo foi desenhado para isso.

**13 dos 17 pontos que consultam a tabela ignoram esse filtro.**

| Arquivo:linha | Query | Filtra por PEI? |
|---|---|---|
| `Services/IndicadorCalculoService.php:735` | `where('cod_pei', $codPei)` | ✅ |
| `Livewire/Shared/PeiProgressBar.php:65` | `where('cod_pei', $codPei)` | ✅ |
| `Livewire/StrategicPlanning/DetalharGrauSatisfacao.php:20` | `findOrFail($id)` | ✅ (por id) |
| `Livewire/StrategicPlanning/ListarGrausSatisfacao.php:294` | query própria | ⚠️ verificar |
| **`Services/PeiGuidanceService.php:115`** | `GrauSatisfacao::count()` | ❌ |
| **`Livewire/StrategicPlanning/MapaEstrategico.php:109`** | `orderBy('vlr_minimo')->get()` | ❌ |
| **`Livewire/PerformanceIndicators/ListarIndicadores.php:161`** | idem | ❌ |
| **`Livewire/ActionPlan/ListarPlanos.php:214`** | idem | ❌ |
| **`Livewire/LandingPage.php:47`** | idem | ❌ |
| **`Livewire/Dashboard/Index.php:437`** | `where('vlr_minimo','<=',$pct)…` sem PEI | ❌ |
| **`Services/Reports/ReportGenerationService.php:118`** | `orderBy('vlr_minimo')->get()` | ❌ |
| **`Services/Reports/ReportGenerationService.php:277`** | idem | ❌ |
| **`Services/Reports/ReportGenerationService.php:544`** | idem | ❌ |
| **`views/livewire/partials/objetivo-contexto.blade.php:87`** | idem — **query dentro da Blade** | ❌ |

### 3.1 Por que isso é grave

O `CLAUDE.md` nomeia exatamente este risco:

> *"O risco maior de desonestidade de interface é o **farol do indicador** e o **percentual de
> execução**: número que arredonda para 'verde' o que não é verde mente para quem decide."*

Com dois ciclos PEI cadastrados — o encerrado e o vigente — e faixas diferentes entre eles
(o que é normal: a régua aperta a cada ciclo), o `first()` devolve **a primeira linha que casar,
de qualquer PEI**. O farol do mapa, do dashboard, da landing page pública e dos três relatórios
pode acender com o critério do ciclo errado.

E ninguém percebe, porque a tela renderiza normalmente. É verde, e é mentira.

> ⚠️ **Não medi quantos PEIs existem em cada base de cliente.** Se hoje só existe um PEI por
> instalação, o defeito está latente e não manifesto. Isso não o torna menos defeito: o sistema é
> multiciclo por construção, e o segundo ciclo é questão de tempo.

### 3.2 Um agravante de arquitetura

`resources/views/livewire/partials/objetivo-contexto.blade.php:87` executa **query no banco dentro
da Blade**. Além do N+1, é um ponto que nenhum teste de componente alcança.

---

## 4. Terceiro achado: não há explicação em lugar nenhum

O gestor pediu texto humanizado, e ele está certo — mas o texto sozinho não resolve. O cliente
que não entende o que é Grau de Satisfação está diante de uma tela com quatro campos
(`dsc_grau_satisfacao`, `cor`, `vlr_minimo`, `vlr_maximo`) e nenhuma frase dizendo para que
servem. Quando entende e clica, toma 403.

**Ordem de correção: destravar o acesso → explicar → sugerir valor padrão.** Explicar primeiro
seria explicar uma porta trancada.

---

## 5. Soluções avaliadas

### Para o bloqueio de acesso (achado 1)

#### Opção A — Conceder `graus-satisfacao` ao Admin de Unidade ✅ **RECOMENDADA**

```php
'graus-satisfacao' => [
    PerfilAcesso::ADMIN_UNIDADE      => ['acessar', 'criar', 'editar', 'excluir'],
    PerfilAcesso::GESTOR_RESPONSAVEL => ['acessar'],          // vê a régua, não a altera
    PerfilAcesso::GESTOR_SUBSTITUTO  => ['acessar'],
],
```

**Justificativa do recorte:** o Grau de Satisfação é a **régua** com que a organização julga o
próprio desempenho. Quem define a régua é quem responde pelo planejamento da unidade — o Admin de
Unidade. Quem lança evolução (Gestor Responsável) precisa **ver** a régua para interpretar o
farol, mas não deve poder mudá-la: alterar a faixa depois do resultado lançado é reescrever a nota
depois da prova.

> 🔴 `CapacidadeResolver.php` é **arquivo de infraestrutura global** pelo `CLAUDE.md`. Esta
> alteração exige autorização explícita do gestor e alerta no resumo da entrega.

#### Opção B — Manter fechado e ajustar a orientação ⚠️
Se a decisão institucional for que só o Super Admin define a régua, então o
`PeiGuidanceService` **não pode** mandar o cliente para lá. A mensagem passaria a ser:
*"Aguardando a definição dos níveis pelo administrador da plataforma."*

É coerente, e transfere a tarefa para fora do cliente. Torna o autosserviço impossível — a cada
instalação alguém do time do produto teria de configurar. **Não recomendada para produto
multicliente**, mas é uma decisão legítima do gestor.

#### Opção C — Semear graus padrão na instalação ✅ **complemento, não substituto**
Um `GrauSatisfacaoSeeder` sobre [16](16-padrao-seeders-idempotentes.md) cria a régua clássica de
quatro faixas no ato da instalação:

| Faixa | Rótulo sugerido | Cor |
|---|---|---|
| 0–49,99% | Crítico | vermelho |
| 50–79,99% | Atenção | amarelo |
| 80–94,99% | Adequado | verde-claro |
| 95–100%+ | Excelente | verde |

Assim o cliente **nunca** encontra a tela vazia — e continua podendo ajustar. Isso resolve a
demanda pela raiz: a maior parte dos clientes não precisará mexer nisso nunca.

⚠️ Os valores acima são **sugestão de partida, não medição**. Precisam da validação metodológica
do gestor antes de virar seeder.

### Para o farol contaminado (achado 2)

#### Opção D — `scopeDoPei()` no Model + correção dos 13 pontos ✅ **RECOMENDADA**
```php
public function scopeDoPei($query, string $codPei, ?int $ano = null) { ... }
```
Cada um dos 13 pontos passa a declarar de qual PEI quer a régua. **Não é substituição em massa** —
cada ponto precisa saber qual é o PEI ativo naquele contexto, e alguns (relatórios) recebem o PEI
por parâmetro.

#### Opção E — Global scope automático ❌
Amarraria o Model à sessão. Quebra os relatórios agendados, que rodam sem sessão. Descartada.

### Para a falta de explicação (achado 3)

#### Opção F — Texto humanizado no lugar da decisão ✅
Não um parágrafo genérico no topo, mas a explicação onde o cliente hesita:

> **O que é o Grau de Satisfação**
> É o **semáforo** do seu planejamento. Você define as faixas; o sistema pinta cada indicador com
> a cor da faixa em que o resultado caiu.
>
> Exemplo: se você definir que *80% a 94,99% = Adequado (verde-claro)*, um indicador que atingiu
> **87% da meta** aparece em verde-claro no Mapa Estratégico, no Dashboard e nos relatórios.
>
> **Por que preencher antes dos indicadores:** sem as faixas, o sistema calcula o percentual mas
> não tem como dizer se ele é bom ou ruim — o indicador fica sem cor.

Mais um aviso de cobertura na própria tela: *"Suas faixas cobrem de 0% a 100%. Não há intervalo
descoberto."* — ou o inverso, se houver buraco.

---

## 6. Plano de ação

### Onda 1 — destravar (é o que impede o cliente de trabalhar)
1. Levar a Opção A ao gestor. `CapacidadeResolver` é arquivo global: **não tocar sem autorização.**
2. Aplicar a entrada na `MATRIZ`.
3. Rodar `tests/Feature/Authorization/` **inteiro** — é o que o `CLAUDE.md` exige ao mexer em autorização.
4. Escrever o teste que faltava: *"o perfil X consegue abrir a rota para onde o
   `PeiGuidanceService` o envia."* É a trava que teria pego este defeito.

### Onda 2 — corrigir o farol
5. `scopeDoPei()` em `GrauSatisfacao`.
6. Corrigir os 13 pontos, **um a um**, cada um com a origem do PEI declarada. Não é `sed`.
7. Tirar a query da Blade `objetivo-contexto.blade.php` e movê-la para o componente.
8. Teste de coerência: com dois PEIs de faixas diferentes, o farol do indicador do PEI A usa a
   régua do PEI A.

### Onda 3 — explicar e semear
9. Texto humanizado na tela de graus e no ponto de decisão do formulário de indicador.
10. Aviso de cobertura de faixa (buraco / sobreposição).
11. Validar as 4 faixas padrão com o gestor.
12. `GrauSatisfacaoSeeder` idempotente, por PEI.
13. No formulário de indicador: se o PEI não tem graus, mostrar o aviso e o link — agora acessível.

---

## 7. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R6.0** Decisão | Gestor escolhe A ou B, e valida o recorte de capacidades | Registrado neste arquivo | — |
| **R6.1** Acesso | Entrada de `graus-satisfacao` na `MATRIZ` | Admin de Unidade abre a tela; `tests/Feature/Authorization/` verde | R6.0 |
| **R6.2** Trava de coerência | Teste: toda rota sugerida pelo `PeiGuidanceService` é acessível ao perfil que a recebe | Verde — pega a classe inteira do defeito | R6.1 |
| **R6.3** Escopo por PEI | `scopeDoPei()` no Model | `php -l` limpo | — |
| **R6.4** 13 correções | Cada consulta declara o PEI | Nenhuma consulta a `GrauSatisfacao` sem PEI fora do CRUD da própria tela | R6.3 |
| **R6.5** Query fora da Blade | `objetivo-contexto.blade.php` sem acesso a banco | `grep` de `::` de Model em Blade limpo nesse arquivo | R6.4 |
| **R6.6** Teste do farol | Dois PEIs, faixas diferentes, farol correto em cada um | Verde | R6.4 |
| **R6.7** Coerência entre módulos | Mapa, Dashboard, landing e os 3 relatórios mostram o mesmo farol para o mesmo indicador | Comparação lado a lado | R6.4 |
| **R6.8** Texto humanizado | Explicação na tela de graus e no form de indicador | Aprovado pelo gestor | R6.1 |
| **R6.9** Aviso de cobertura | Alerta de buraco ou sobreposição de faixa | Faixas 0-50 e 60-100 → avisa | R6.8 |
| **R6.10** Faixas padrão | Validadas pelo gestor | Registrado | R6.8 |
| **R6.11** Seeder | `GrauSatisfacaoSeeder` por PEI | Testes de [16](16-padrao-seeders-idempotentes.md) verdes | R6.10 |

---

## 8. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 06-B01 | Levar a Opção A ao gestor com o recorte por perfil | P | — | Autorização escrita |
| 06-B02 | Entrada de `graus-satisfacao` na `MATRIZ` | P | B01 | `tests/Feature/Authorization/` verde |
| 06-B03 | Teste: Admin de Unidade abre `/graus-satisfacao` | M | B02 | Verde |
| 06-B04 | Teste: Gestor Responsável **vê** e **não edita** | M | B02 | Verde |
| 06-B05 | **Teste de coerência guia×permissão** para todas as fases do `PeiGuidanceService` | M | B02 | Verde |
| 06-B06 | `scopeDoPei()` em `GrauSatisfacao` | P | — | `php -l` |
| 06-B07 | Corrigir `PeiGuidanceService:115` | P | B06 | Contagem por PEI |
| 06-B08 | Corrigir `MapaEstrategico:109` | P | B06 | Farol do PEI ativo |
| 06-B09 | Corrigir `ListarIndicadores:161` | P | B06 | idem |
| 06-B10 | Corrigir `ListarPlanos:214` | P | B06 | idem |
| 06-B11 | Corrigir `LandingPage:47` | P | B06 | idem |
| 06-B12 | Corrigir `Dashboard/Index:437` | P | B06 | idem |
| 06-B13 | Corrigir os 3 pontos de `ReportGenerationService` | M | B06 | PDF confere com a tela |
| 06-B14 | Mover a query de `objetivo-contexto.blade.php` para o componente | M | B06 | Sem `::` de Model na Blade |
| 06-B15 | Verificar `ListarGrausSatisfacao:294` | P | B06 | Lista só o PEI ativo |
| 06-B16 | Teste: 2 PEIs com faixas diferentes → farol correto em cada | M | B13 | Verde |
| 06-B17 | Teste de coerência entre módulos para o mesmo indicador | M | B13 | Verde |
| 06-B18 | Texto humanizado na tela de graus | P | B02 | Gestor aprova |
| 06-B19 | Texto no form de indicador quando não há graus | P | B18 | Tela |
| 06-B20 | Aviso de buraco/sobreposição de faixa | M | B18 | Cenário de teste |
| 06-B21 | Validar as 4 faixas padrão com o gestor | P | B18 | Registrado |
| 06-B22 | `GrauSatisfacaoSeeder` por PEI | M | B21, 16-B02 | Testes de [16] verdes |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 9. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

1. **A reprodução completa, com o perfil do cliente** — não com Super Admin, que passa por tudo e
   por isso nunca viu o defeito. Logar como Admin de Unidade, cadastrar objetivos, seguir o botão
   que o sistema oferece, e chegar na tela. **Este é o único teste que responde à queixa.**
2. **06-B05**, a resposta à pergunta do `CLAUDE.md` — *"que verificação automática teria pego
   isto?"*: um teste que percorre todas as fases do `PeiGuidanceService` e afirma que a rota
   sugerida é acessível ao perfil que a recebe. Sem ele, o próximo módulo restrito repete o erro.
3. **O farol, com número:** dois PEIs com faixas propositalmente diferentes; o mesmo percentual
   tem de acender cores diferentes em cada um. Se acender igual, a contaminação continua.
4. **Coerência entre módulos:** o mesmo indicador, no mapa, no dashboard e no PDF — mesma cor.
   Divergência aqui é o que o `CLAUDE.md` chama de destruidor de confiança na plataforma.
5. `tests/Feature/Authorization/` **inteiro**, mais `tests/Feature/StrategicPlanning/`.
6. `php artisan view:clear && php artisan config:clear` antes de julgar qualquer tela.

---

## 10. O que NÃO foi verificado

- **Quantos PEIs existem por instalação de cliente** — determina se o achado 2 é latente ou
  manifesto hoje. Exige leitura no banco, com autorização
- **Se algum cliente já tem graus cadastrados**, e com quais faixas
- Se `ListarGrausSatisfacao:294` filtra por PEI — a query não foi lida por inteiro
- Se o 403 é apresentado como página de erro ou como tela em branco (muda a percepção do cliente)
- Se existe alguma tela que dependa de `GrauSatisfacao` sem filtro **de propósito** — o item 06-B04
  ao 06-B15 precisa confirmar caso a caso, não presumir
- Se `num_ano` do grau é usado em algum lugar — ele está no `$fillable` e não apareceu em nenhuma
  das 17 consultas
