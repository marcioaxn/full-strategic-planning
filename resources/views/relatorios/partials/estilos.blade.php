{{--
    SISTEMA DE DESIGN DOS RELATÓRIOS EM PDF.

    Um arquivo só, para os onze relatórios. Não foi inventado: a linguagem
    visual veio de medição do Relatório de Gestão 2025 da Presidência da
    República (documentacao/relatorios/RelatriodeGesto2025PReVPR31mar.pdf),
    rasterizado página a página — paleta por amostragem de pixel, grid e
    tipografia conferidos na página impressa.

    🔴 O QUE ESTE ARQUIVO **NÃO** DECLARA: cabeçalho e rodapé.

    Os dois são desenhados no canvas, por App\Services\Reports\AcabamentoPdf,
    para os onze relatórios. Motivo: elemento `position: fixed` do DomPDF é
    desenhado também na capa e permanece na camada de texto mesmo coberto —
    quem selecionasse a capa copiaria "PÁGINA 1" de uma página que não mostra
    nada disso. A margem de `@page` aqui é o que RESERVA o espaço deles.

    PARÂMETROS
      $orientacao  'portrait' (padrão) ou 'landscape'

    A orientação é escolha de cada relatório: uma tabela de indicadores com
    meta, realizado e farol por ano pede paisagem; uma lista de objetivos ou um
    texto executivo se lê melhor em retrato.
--}}
<style>
    /*
       Margem SIMÉTRICA em cima e embaixo (74px), e igual nos lados (52px).

       Os 74px verticais reservam a faixa de cabeçalho e de rodapé, que o
       AcabamentoPdf desenha a 46,5pt de cada borda — a mesma constante nas
       duas pontas, para a simetria não poder quebrar pela metade.
    */
    @page { size: a4 {{ $orientacao ?? 'portrait' }}; margin: 74px 52px 74px 52px; }

    * { box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
        font-size: 9.5px;
        color: #2C2E35;              /* grafite do modelo */
        line-height: 1.5;
        margin: 0; padding: 0;
    }

    /* ─────────────── Paleta medida no modelo ───────────────
       verde #54B347 · verde escuro #3D9B33 · grafite #2C2E35
       cinza #95969A · cinza escuro #595959 · amarelo #EDC009
       azul #3550A0 · vermelho #FF361E · verde claro #F4FBF3   */

    /* ─────────────── Títulos ─────────────── */
    .rpt-doc-titulo {
        font-size: 17px; font-weight: bold; color: #3D9B33;
        margin: 0 0 4px 0; page-break-after: avoid;
    }
    .rpt-doc-sub { font-size: 9.5px; color: #595959; margin: 0 0 14px 0; }

    .secao-titulo {
        font-size: 12px; font-weight: bold; color: #2C2E35;
        border-bottom: 1.5px solid #54B347; padding-bottom: 4px;
        margin: 18px 0 9px 0; page-break-after: avoid;
    }
    .secao-sub { font-size: 9px; color: #595959; margin: -4px 0 9px 0; }

    .rpt-corpo { text-align: justify; margin: 0 0 8px 0; }

    /* ─────────────── Corpo em colunas ───────────────
       O DomPDF 3 não implementa column-count. As colunas são reproduzidas com
       tabela: o texto não flui sozinho de uma para a outra, então quem monta a
       seção decide o que vai em cada coluna. */
    table.rpt-colunas { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table.rpt-colunas td { vertical-align: top; text-align: justify; padding-right: 22px; }
    table.rpt-colunas td.rpt-ultima { padding-right: 0; }

    /* ─────────────── Faixa de filtros aplicados ───────────────
       O leitor precisa saber de que recorte o número saiu. Sem isto, dois
       relatórios do mesmo módulo com totais diferentes parecem contradição. */
    .rpt-filtros {
        border: 1px solid #D3EED1; background: #F4FBF3;
        padding: 7px 12px; margin-bottom: 14px; font-size: 8.5px;
    }
    .rpt-filtros span { margin-right: 18px; color: #595959; }
    .rpt-filtros strong { color: #2C2E35; }

    /* ─────────────── Cartões de número (KPI) ───────────────
       Nomes mantidos (`kpi-*`): são os que os onze relatórios já usam. O que
       mudou foi só a linguagem visual — do azul/laranja anterior para a paleta
       medida no modelo. Renomear a classe daria churn em dez arquivos sem
       entregar nada ao cliente. */
    table.kpi-grid { width: 100%; border-collapse: separate; border-spacing: 7px 0; margin-bottom: 14px; }
    .kpi-card {
        border: 1px solid #D3EED1; background: #F4FBF3;
        border-top: 2.5px solid #54B347;
        padding: 11px 12px; text-align: center; vertical-align: top;
    }
    .kpi-label { font-size: 7.5px; font-weight: bold; text-transform: uppercase; letter-spacing: .05em; color: #595959; margin: 0; }
    .kpi-value { font-size: 20px; font-weight: bold; color: #3D9B33; margin: 4px 0 0 0; line-height: 1.1; }
    .kpi-sub { font-size: 7.5px; color: #95969A; margin: 3px 0 0 0; }

    /* Variantes: a cor só entra quando o número CARREGA um juízo. Cartão de
       contagem ("total de indicadores") não tem juízo nenhum e fica neutro. */
    .kpi-card.accent  { border-top-color: #EDC009; }
    .kpi-card.accent .kpi-value  { color: #8A7200; }
    .kpi-card.success { border-top-color: #54B347; }
    .kpi-card.danger  { border-top-color: #FF361E; background: #FDF1F0; border-color: #F0B3AC; }
    .kpi-card.danger .kpi-value  { color: #A82214; }
    .kpi-card.warning { border-top-color: #EDC009; background: #FDF8E7; border-color: #EBD98C; }
    .kpi-card.warning .kpi-value { color: #8A7200; }
    .kpi-card.info    { border-top-color: #3550A0; background: #F2F5FC; border-color: #B9C6E6; }
    .kpi-card.info .kpi-value    { color: #2B4487; }
    .kpi-card.neutro  { border-color: #DDDEE0; background: #FAFAFA; border-top-color: #95969A; }
    .kpi-card.neutro .kpi-value  { color: #595959; }

    /* ─────────────── Faixa de grupo (perspectiva, categoria) ─────────────── */
    .grupo-band {
        background: #2C2E35; color: #fff;
        padding: 6px 12px; font-weight: bold; font-size: 10px;
        margin-top: 14px; page-break-after: avoid;
    }
    .grupo-band .contador { float: right; font-weight: normal; opacity: .8; font-size: 8.5px; }

    /* ─────────────── Tabelas (p. 28 do modelo) ─────────────── */
    .rpt-tabela-titulo { font-size: 9.5px; color: #2C2E35; margin: 12px 0 4px 0; }
    table.rpt { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8.5px; }
    table.rpt thead th {
        background: #2C2E35; color: #fff; font-weight: normal;
        text-align: center; padding: 4px 6px; border: 1px solid #2C2E35;
        font-size: 8.5px;
    }
    table.rpt tbody td {
        border: 1px solid #B9BABD; padding: 5px 7px;
        vertical-align: middle; text-align: justify;
    }
    table.rpt tbody td.text-center, table.rpt td.rpt-centro { text-align: center; }
    table.rpt tbody td.text-end { text-align: right; }
    table.rpt tr { page-break-inside: avoid; }

    .row-titulo { font-weight: bold; color: #2C2E35; }
    .row-desc { font-size: 8px; color: #595959; }

    .rpt-lista { margin: 0; padding-left: 11px; }
    .rpt-lista li { margin-bottom: 2px; text-align: justify; }

    /* ─────────────── Pílulas de status ───────────────
       Cores discretas: a cor forte fica reservada ao farol, que é o único
       lugar da página onde ela significa desempenho. */
    .pill { display: inline-block; padding: 2px 8px; font-size: 8px; font-weight: bold; border: 1px solid; }
    .pill-success  { background: #F4FBF3; color: #2F7A28; border-color: #A9DDA3; }
    .pill-info     { background: #F2F5FC; color: #2B4487; border-color: #B9C6E6; }
    .pill-warning  { background: #FDF8E7; color: #8A7200; border-color: #EBD98C; }
    .pill-danger   { background: #FDF1F0; color: #A82214; border-color: #F0B3AC; }
    .pill-neutral  { background: #F5F5F6; color: #595959; border-color: #D6D7D9; }

    /* ─────────────── Barra de progresso e farol ─────────────── */
    .progress-track { background: #EDEEEF; height: 8px; width: 100%; }
    .progress-fill { height: 8px; }
    .farol { width: 10px; height: 10px; border-radius: 50%; display: inline-block; vertical-align: middle; }

    /* ─────────────── Estado vazio ───────────────
       Regra do gestor: seção que o cliente não preencheu NÃO aparece. Quando
       aparecer mesmo assim, tem de dizer o que falta — título seguido de
       espaço em branco não informa nada a quem lê. */
    .vazio {
        text-align: center; padding: 18px; color: #95969A; font-style: italic;
        font-size: 9px; background: #FAFAFA; border: 1px dashed #C8C9CB;
    }

    /* Bordas em toda a tabela — usado onde a leitura é célula a célula. */
    table.rpt.bordered tbody td, table.rpt.bordered thead th { border: 1px solid #B9BABD; }

    .avoid-break { page-break-inside: avoid; }
    .page-break { page-break-before: always; }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .mb-0 { margin-bottom: 0; }
    .rpt-link { color: #3550A0; }
</style>
