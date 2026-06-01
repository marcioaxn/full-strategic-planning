{{-- Sistema de Design compartilhado dos Relatórios PDF — alinhado ao GPPEI --}}
<style>
    @page { margin: 110px 35px 70px 35px; }

    * { box-sizing: border-box; }
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 10px; color: #2d3748; line-height: 1.5;
        margin: 0; padding: 0;
    }

    /* ─── Paleta GPPEI ─── */
    /* primary #1B408E · navy #1a3a5c · accent #e07b39 · success #2e8b57 */

    /* ─── Cabeçalho fixo (repete em todas as páginas) ─── */
    .rpt-header {
        position: fixed; top: -85px; left: 0; right: 0; height: 78px;
        background: linear-gradient(120deg, #1a3a5c 0%, #1B408E 60%, #2e5aa8 100%);
        border-radius: 0 0 10px 10px;
        padding: 12px 22px; color: #fff;
    }
    .rpt-header-table { width: 100%; border-collapse: collapse; }
    .rpt-header-icon {
        width: 46px; height: 46px; border-radius: 10px;
        background: rgba(255,255,255,.16); text-align: center; vertical-align: middle;
    }
    .rpt-header-icon span { font-size: 22px; line-height: 46px; color: #fff; }
    .rpt-eyebrow { font-size: 7.5px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255,255,255,.65); margin: 0; }
    .rpt-title { font-size: 17px; font-weight: bold; color: #fff; margin: 1px 0 0 0; letter-spacing: -.3px; }
    .rpt-subtitle { font-size: 9px; color: rgba(255,255,255,.8); margin: 2px 0 0 0; }
    .rpt-header-meta { text-align: right; vertical-align: middle; font-size: 8px; color: rgba(255,255,255,.85); }
    .rpt-header-meta strong { color: #fff; }
    .rpt-accent-bar { height: 3px; background: #e07b39; border-radius: 2px; margin-top: 6px; }

    /* ─── Rodapé fixo com numeração ─── */
    .rpt-footer {
        position: fixed; bottom: -48px; left: 0; right: 0; height: 38px;
        border-top: 1px solid #e2e8f0; padding-top: 6px;
        font-size: 7.5px; color: #a0aec0;
    }
    .rpt-footer-table { width: 100%; border-collapse: collapse; }
    .rpt-footer .pagenum:after { content: counter(page); }
    .rpt-footer .pagecount:after { content: counter(pages); }

    /* ─── Faixa de filtros ─── */
    .rpt-filtros {
        background: #f7fafc; border: 1px solid #e2e8f0; border-left: 3px solid #1B408E;
        border-radius: 6px; padding: 8px 14px; margin-bottom: 18px; font-size: 8.5px;
    }
    .rpt-filtros span { margin-right: 18px; color: #4a5568; }
    .rpt-filtros strong { color: #1a3a5c; }

    /* ─── Cards de KPI ─── */
    .kpi-grid { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin-bottom: 18px; }
    .kpi-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
        border-top: 3px solid #1B408E; padding: 12px 14px; vertical-align: top;
    }
    .kpi-card.accent { border-top-color: #e07b39; }
    .kpi-card.success { border-top-color: #2e8b57; }
    .kpi-card.danger  { border-top-color: #dc3545; }
    .kpi-card.warning { border-top-color: #d97706; }
    .kpi-label { font-size: 7.5px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; color: #718096; margin: 0; }
    .kpi-value { font-size: 24px; font-weight: bold; color: #1a3a5c; margin: 3px 0 0 0; line-height: 1; }
    .kpi-sub { font-size: 7.5px; color: #a0aec0; margin: 3px 0 0 0; }

    /* ─── Títulos de seção ─── */
    .secao-titulo {
        font-size: 12px; font-weight: bold; color: #1a3a5c;
        border-bottom: 2px solid #e07b39; padding-bottom: 5px; margin: 22px 0 12px 0;
    }
    .secao-titulo .bi-num { color: #e07b39; }

    /* ─── Faixa de perspectiva/grupo ─── */
    .grupo-band {
        background: linear-gradient(90deg, #1B408E, #2e5aa8);
        color: #fff; padding: 7px 14px; font-weight: bold; font-size: 10.5px;
        border-radius: 6px 6px 0 0; margin-top: 16px;
    }
    .grupo-band .contador { float: right; background: rgba(255,255,255,.2); border-radius: 10px; padding: 1px 9px; font-size: 8.5px; }

    /* ─── Tabelas modernas ─── */
    table.rpt { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    table.rpt thead th {
        background: #edf2f7; color: #1a3a5c; font-size: 8px; font-weight: bold;
        text-transform: uppercase; letter-spacing: .4px; text-align: left;
        padding: 7px 9px; border-bottom: 2px solid #cbd5e0;
    }
    table.rpt tbody td { padding: 7px 9px; border-bottom: 1px solid #edf2f7; font-size: 9px; vertical-align: top; }
    table.rpt tbody tr:nth-child(even) td { background: #f9fafb; }
    table.rpt.bordered tbody td, table.rpt.bordered thead th { border: 1px solid #e2e8f0; }

    .row-titulo { font-weight: bold; color: #2d3748; }
    .row-desc { font-size: 8px; color: #718096; }

    /* ─── Pills de status ─── */
    .pill { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 8px; font-weight: bold; }
    .pill-success  { background: #d1fae5; color: #065f46; }
    .pill-info     { background: #dbeafe; color: #1e40af; }
    .pill-warning  { background: #fef3c7; color: #92400e; }
    .pill-danger   { background: #fee2e2; color: #991b1b; }
    .pill-neutral  { background: #e5e7eb; color: #374151; }

    /* ─── Barra de progresso ─── */
    .progress-track { background: #edf2f7; border-radius: 999px; height: 9px; width: 100%; overflow: hidden; }
    .progress-fill { height: 9px; border-radius: 999px; }

    /* ─── Farol ─── */
    .farol { width: 11px; height: 11px; border-radius: 50%; display: inline-block; vertical-align: middle; }

    /* ─── Estado vazio ─── */
    .vazio { text-align: center; padding: 26px; color: #a0aec0; font-style: italic; font-size: 9px;
             background: #f9fafb; border: 1px dashed #cbd5e0; border-radius: 8px; }

    .avoid-break { page-break-inside: avoid; }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .mb-0 { margin-bottom: 0; }
</style>
