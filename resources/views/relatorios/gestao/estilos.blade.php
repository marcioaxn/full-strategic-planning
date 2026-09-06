{{--
    Sistema de design do Relatório de Gestão.

    Não foi inventado: veio de medição do modelo oficial
    (documentacao/relatorios/RelatriodeGesto2025PReVPR31mar.pdf), rasterizado
    página a página.

    O QUE O MODELO É, e a primeira versão deste arquivo não era:

      • A4 PAISAGEM. Todas as 198 páginas.
      • Corpo em COLUNAS (2 a 4), justificado.
      • Cabeçalho de três colunas em verde, com régua verde por baixo:
        órgão · "Relatório de Gestão AAAA" · capítulo corrente.
      • Rodapé com régua verde de cada lado do número da página, URL do órgão
        à esquerda.
      • Capa de foto sangrada, título em branco no alto à esquerda.
      • Na página do mapa estratégico, cada bloco tem um RÓTULO VERTICAL
        colorido na lateral esquerda (MISSÃO, VISÃO, VALORES, OBJETIVOS
        FINALÍSTICOS, SUPORTE).

    🔴 O que NÃO se copia: marca, foto e redes sociais da Presidência. Este é
    um produto multicliente — o layout é do modelo, os ativos são de cada
    órgão, vindos de `system_settings`. Copiar a identidade visual de um órgão
    para o relatório de outro seria falsificação institucional.
--}}
<style>
    /* ─────────────────────────── Página ───────────────────────────
       Paisagem, como o modelo. Margem superior e inferior IGUAIS (74px), e
       as faixas fixas de cabeçalho e rodapé têm a mesma altura (48px) —
       é assim que a simetria pedida existe na página impressa, não só no CSS. */
    @page { size: a4 landscape; margin: 74px 52px 74px 52px; }


    * { box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
        font-size: 9.5px;
        color: #2C2E35;
        line-height: 1.5;
        margin: 0; padding: 0;
    }

    /* Cabeçalho e rodapé são desenhados no canvas (AcabamentoPdf), não aqui:
       elemento fixo do DomPDF aparece também na capa, e o texto ficaria na
       camada de texto do PDF mesmo coberto pela pintura da capa.
       A margem de @page acima é que reserva o espaço deles. */

    /* ─────────────────────────── Capa ───────────────────────────
       Sangrada: sem margem, foto ocupando a folha inteira. Sem foto do
       cliente, um fundo institucional sólido com a mesma tipografia. */
    .rg-capa {
        page-break-after: always;
        width: 1122px; height: 793px;
        position: relative;
        background: #1B3A2F;
        margin: 0; padding: 0;
    }
    .rg-capa-foto { position: absolute; top: 0; left: 0; width: 1122px; height: 793px; }
    .rg-capa-veu {
        position: absolute; top: 0; left: 0; width: 1122px; height: 300px;
        background: #14231D;
        opacity: .55;
    }
    .rg-capa-texto { position: absolute; top: 34px; left: 78px; width: 700px; }
    .rg-capa-orgao { font-size: 27px; font-weight: bold; color: #FFFFFF; margin: 0 0 16px 0; }
    .rg-capa-titulo { font-size: 25px; color: #FFFFFF; margin: 0 0 16px 34px; }
    .rg-capa-ano { font-size: 25px; font-weight: bold; color: #FFFFFF; margin: 0 0 0 92px; }
    .rg-capa-credito {
        position: absolute; top: 12px; right: 20px;
        font-size: 8px; color: #FFFFFF; background: #00000055; padding: 2px 6px;
    }
    .rg-capa-rodape {
        position: absolute; bottom: 34px; left: 78px; width: 900px;
        font-size: 10px; color: #FFFFFF;
    }

    /* ─────────── Sumário ─────────── */
    .rg-sumario { page-break-after: always; }
    .rg-sum-col { width: 50%; vertical-align: top; padding-right: 26px; }
    .rg-sum-cap { font-size: 11.5px; font-weight: bold; color: #3D9B33; margin: 12px 0 5px 0; }
    .rg-sum-item { font-size: 9.5px; color: #2C2E35; margin: 0 0 2px 10px; }
    .rg-sum-item .rg-sum-ext { color: #95969A; font-size: 8.5px; }

    /* ─────────── Títulos ─────────── */
    .rg-cap-titulo {
        font-size: 17px; font-weight: bold; color: #3D9B33;
        margin: 0 0 12px 0; page-break-after: avoid;
    }
    .rg-sec-titulo {
        font-size: 12px; font-weight: bold; color: #2C2E35;
        margin: 14px 0 7px 0; page-break-after: avoid;
    }
    .rg-corpo { text-align: justify; margin: 0 0 8px 0; }

    /* ─────────── Corpo em colunas ───────────
       O DomPDF 3 não implementa column-count. As colunas do modelo são
       reproduzidas com tabela: o texto não flui sozinho de uma para a outra,
       então quem monta a seção decide o que vai em cada coluna. */
    table.rg-colunas { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table.rg-colunas td { vertical-align: top; text-align: justify; padding-right: 22px; }
    table.rg-colunas td.rg-ultima { padding-right: 0; }

    /* ─────────── Blocos do mapa estratégico (p. 27 do modelo) ───────────
       Cada bloco é uma tabela de duas células: a da esquerda é a barra
       colorida com o rótulo na vertical; a da direita, o conteúdo. */
    table.rg-bloco {
        width: 100%; border-collapse: collapse; margin-bottom: 9px;
        page-break-inside: avoid;
    }
    table.rg-bloco td.rg-bloco-rotulo {
        width: 24px; text-align: center; vertical-align: middle;
        font-size: 7px; font-weight: bold; color: #FFFFFF;
        line-height: 1.05; letter-spacing: .02em; padding: 6px 2px;
    }
    table.rg-bloco td.rg-bloco-corpo {
        padding: 8px 11px; vertical-align: top;
        border-top: 1.2px solid; border-right: 1.2px solid; border-bottom: 1.2px solid;
    }

    .rg-b-missao  td.rg-bloco-rotulo { background: #EDC009; }
    .rg-b-missao  td.rg-bloco-corpo  { border-color: #EDC009; }
    .rg-b-visao   td.rg-bloco-rotulo { background: #3550A0; }
    .rg-b-visao   td.rg-bloco-corpo  { border-color: #3550A0; }
    .rg-b-valores td.rg-bloco-rotulo { background: #54B347; }
    .rg-b-valores td.rg-bloco-corpo  { border-color: #54B347; }
    .rg-b-fim     td.rg-bloco-rotulo { background: #FF361E; }
    .rg-b-fim     td.rg-bloco-corpo  { border-color: #FF361E; }
    .rg-b-sup     td.rg-bloco-rotulo { background: #2C2E35; }
    .rg-b-sup     td.rg-bloco-corpo  { border-color: #2C2E35; }

    /* Grade interna dos blocos: o modelo distribui os itens em colunas. */
    table.rg-grade { width: 100%; border-collapse: collapse; }
    table.rg-grade td {
        vertical-align: top; text-align: justify;
        font-size: 8.5px; padding: 0 10px 6px 0; line-height: 1.4;
    }
    table.rg-grade td.rg-ultima { padding-right: 0; }
    .rg-mapa-titulo {
        text-align: center; font-size: 11px; font-weight: bold;
        color: #2C2E35; letter-spacing: .04em; margin: 0 0 9px 0;
        text-transform: uppercase;
    }

    /* ─────────── Tabelas (p. 28 do modelo) ─────────── */
    .rg-tabela-titulo { font-size: 9.5px; color: #2C2E35; margin: 12px 0 4px 0; }
    table.rg-tabela { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8.5px; }
    table.rg-tabela th {
        background: #2C2E35; color: #fff; font-weight: normal;
        text-align: center; padding: 4px 6px; border: 1px solid #2C2E35;
        font-size: 8.5px;
    }
    table.rg-tabela td {
        border: 1px solid #B9BABD; padding: 5px 7px; vertical-align: middle;
        text-align: justify;
    }
    table.rg-tabela td.rg-centro { text-align: center; }
    table.rg-tabela tr { page-break-inside: avoid; }
    .rg-lista { margin: 0; padding-left: 11px; }
    .rg-lista li { margin-bottom: 2px; text-align: justify; }

    /* ─────────── Grandes números ─────────── */
    table.rg-numeros { width: 100%; border-collapse: separate; border-spacing: 7px 0; margin-bottom: 12px; }
    .rg-numero-card { border: 1px solid #D3EED1; background: #F4FBF3; padding: 11px; text-align: center; vertical-align: top; }
    .rg-numero-valor { font-size: 20px; font-weight: bold; color: #3D9B33; margin: 0; line-height: 1.1; }
    .rg-numero-rotulo { font-size: 7.5px; color: #595959; margin: 4px 0 0 0; text-transform: uppercase; letter-spacing: .04em; }

    /* ─────────── Seção de fonte externa (só na variante réplica) ─────────── */
    .rg-externa {
        border: 1px dashed #95969A;
        padding: 8px 11px; margin-bottom: 9px; background: #FAFAFA;
        page-break-inside: avoid;
    }
    .rg-externa-aviso { font-size: 9px; color: #595959; margin: 0; }
    .rg-externa-fonte { font-size: 8.5px; color: #95969A; margin: 3px 0 0 0; }

    .rg-vazio { font-size: 9px; color: #95969A; font-style: italic; }
    .rg-cap { page-break-before: always; }
    .rg-cap-primeiro { page-break-before: avoid; }
    .rg-link { color: #3550A0; }
</style>
