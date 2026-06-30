{{-- Gantt / Timeline --}}
@php
    use Carbon\Carbon;

    $DAY_W = 38; // largura fixa de cada coluna (px)

    $inicio = Carbon::parse($timelineInicio)->startOfDay();
    $fim    = Carbon::parse($timelineFim)->startOfDay();
    $hoje   = Carbon::now()->startOfDay();

    // Gera array de dias
    $dias = [];
    $cur  = $inicio->copy();
    while ($cur <= $fim) { $dias[] = $cur->copy(); $cur->addDay(); }
    $totalDias   = count($dias);
    $labelWidth  = 220;                          // px — coluna de rótulos
    $gridWidth   = $totalDias * $DAY_W;          // px — área de grade
    $rowWidth    = $labelWidth + $gridWidth;     // px — linha total

    // Meses para o cabeçalho
    $meses = collect($dias)->groupBy(fn($d) => $d->format('Y-m'));

    // Cores por status
    $statusColor = [
        'Não Iniciado' => '#64748b',
        'Em Andamento' => '#2563eb',
        'Concluído'    => '#16a34a',
        'Cancelado'    => '#dc2626',
        'Suspenso'     => '#d97706',
    ];

    // Posição da linha "Hoje" (px a partir do início da linha)
    $todayOffsetDias = (int) $inicio->diffInDays($hoje);
    $todayPx = $labelWidth + ($todayOffsetDias + 0.5) * $DAY_W;

    // Contadores do toolbar
    $noPeriodo  = $entregas->filter(fn($e) => $e->dte_prazo && $e->dte_prazo->between($inicio, $fim))->count();
    $nConcluido = $entregas->filter(fn($e) => $e->isConcluida())->count();
    $nAtrasado  = $entregas->filter(fn($e) => $e->isAtrasada())->count();
@endphp

<div class="pei-gantt" x-data="peiGantt()">

    {{-- ─── Toolbar ────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">

                <div class="btn-group btn-group-sm">
                    <button wire:click="timelineAnterior" class="btn btn-outline-secondary">
                        <i class="bi bi-chevron-double-left"></i>
                    </button>
                    <button wire:click="timelineHoje" class="btn btn-primary px-3">
                        <i class="bi bi-calendar-check me-1"></i>Hoje
                    </button>
                    <button wire:click="timelineProximo" class="btn btn-outline-secondary">
                        <i class="bi bi-chevron-double-right"></i>
                    </button>
                </div>

                <span class="text-muted small fw-semibold">
                    {{ $inicio->translatedFormat('d M') }} — {{ $fim->translatedFormat('d M Y') }}
                    <span class="badge bg-light text-secondary border ms-1">{{ $totalDias }}d</span>
                </span>

                <div class="d-flex gap-3 small">
                    <span class="text-muted"><span class="g-dot" style="background:#2563eb"></span>{{ $noPeriodo }} no período</span>
                    <span class="text-success"><span class="g-dot" style="background:#16a34a"></span>{{ $nConcluido }} concluídas</span>
                    @if($nAtrasado)<span class="text-danger"><span class="g-dot" style="background:#dc2626"></span>{{ $nAtrasado }} atrasadas</span>@endif
                </div>

                <div class="btn-group btn-group-sm">
                    <button wire:click="timelineZoomIn"  class="btn btn-outline-secondary" {{ $totalDias <= 7  ? 'disabled' : '' }}><i class="bi bi-zoom-in"></i></button>
                    <button wire:click="timelineZoomOut" class="btn btn-outline-secondary" {{ $totalDias >= 120 ? 'disabled' : '' }}><i class="bi bi-zoom-out"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Tabela Gantt ───────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="g-scroll">

            {{-- Cabeçalho meses --}}
            <div class="g-row g-hd" style="min-width:{{ $rowWidth }}px">
                <div class="g-label-col" style="width:{{ $labelWidth }}px;min-width:{{ $labelWidth }}px">
                    <span class="g-hd-label">Entrega</span>
                </div>
                <div class="g-months" style="width:{{ $gridWidth }}px;min-width:{{ $gridWidth }}px">
                    @foreach($meses as $mesKey => $diasMes)
                        @php $mc = Carbon::parse($mesKey . '-01'); @endphp
                        <div class="g-month" style="width:{{ count($diasMes) * $DAY_W }}px;min-width:{{ count($diasMes) * $DAY_W }}px">
                            {{ $mc->translatedFormat('F Y') }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Cabeçalho dias (sticky) --}}
            <div class="g-row g-hd g-hd-sticky" style="min-width:{{ $rowWidth }}px">
                <div class="g-label-col" style="width:{{ $labelWidth }}px;min-width:{{ $labelWidth }}px"></div>
                <div class="g-days" style="width:{{ $gridWidth }}px;min-width:{{ $gridWidth }}px">
                    @foreach($dias as $dia)
                        <div class="g-day-cell{{ $dia->isToday()?' g-today':'' }}{{ $dia->isWeekend()?' g-wk':'' }}{{ $dia->isMonday()?' g-mon':'' }}"
                             style="width:{{ $DAY_W }}px;min-width:{{ $DAY_W }}px">
                            <span class="g-dn">{{ mb_substr($dia->translatedFormat('D'),0,1) }}</span>
                            <span class="g-dd{{ $dia->isToday()?' g-today-circle':'' }}">{{ $dia->format('d') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Body --}}
            <div class="g-body" style="position:relative;min-width:{{ $rowWidth }}px">

                @forelse($entregas as $entrega)
                    @php
                        $prazo   = $entrega->dte_prazo;
                        $criacao = $entrega->created_at->startOfDay();

                        // Início e fim visíveis da barra
                        $barIni = $criacao->greaterThan($inicio) ? $criacao->copy() : $inicio->copy();
                        $barFim = $prazo ? $prazo->copy() : $barIni->copy()->addDay();
                        if ($barFim < $barIni) $barFim = $barIni->copy()->addDay();

                        // Clipa barFim ao fim do período
                        if ($barFim->greaterThan($fim)) $barFim = $fim->copy();

                        // Posição em pixels — sempre positivos e baseados no início da grade
                        $offDias = (int) $inicio->diffInDays($barIni);
                        $durDias = max(1, (int) $barIni->diffInDays($barFim) + 1);

                        $barLeft  = $offDias * $DAY_W;           // px a partir da grade
                        $barWidth = max($DAY_W, $durDias * $DAY_W); // mínimo 1 coluna

                        // Fora do período?
                        $foraDir = $prazo && $prazo->greaterThan($fim);
                        $foraEsq = $prazo && $prazo->lessThan($inicio);
                        $fora    = $foraDir || $foraEsq;

                        // Se a entrega está fora, resetamos barWidth para não tentar renderizar barra
                        if ($fora) { $barWidth = 0; }

                        // Cor da barra
                        $isAtrasada = $entrega->isAtrasada();
                        $cor = $isAtrasada ? '#dc2626' : ($statusColor[$entrega->bln_status] ?? '#64748b');

                        // Progresso temporal
                        $progPct = 0;
                        if ($prazo && $criacao->lessThan($prazo)) {
                            $span    = max(1, $criacao->diffInDays($prazo));
                            $elapsed = max(0, min((int) $criacao->diffInDays($hoje), $span));
                            $progPct = (int)(($elapsed / $span) * 100);
                        } elseif ($entrega->isConcluida()) {
                            $progPct = 100;
                        }

                        $prazoFmt = $prazo ? $prazo->translatedFormat('d/m/Y') : '—';
                        $durFmt   = $prazo ? $criacao->diffInDays($prazo).'d' : '—';
                    @endphp

                    <div class="g-row g-data-row{{ $entrega->isConcluida()?' g-done':'' }}{{ $isAtrasada?' g-late':'' }}"
                         style="min-width:{{ $rowWidth }}px">

                        {{-- Rótulo --}}
                        <div class="g-label-col g-label"
                             style="width:{{ $labelWidth }}px;min-width:{{ $labelWidth }}px"
                             wire:click="openDetails('{{ $entrega->cod_entrega }}')"
                             title="{{ $entrega->dsc_entrega }}">
                            <span class="g-status-dot" style="background:{{ $cor }}"></span>
                            <span class="g-label-text">{{ Str::limit($entrega->dsc_entrega, 25) }}</span>
                            @if($prazo)
                                <span class="g-label-date{{ $isAtrasada?' text-danger':'' }}">{{ $prazo->format('d/m') }}</span>
                            @endif
                        </div>

                        {{-- Área de grade --}}
                        <div class="g-grid" style="width:{{ $gridWidth }}px;min-width:{{ $gridWidth }}px;position:relative;height:46px">

                            {{-- Fundo zebrado por dia --}}
                            @foreach($dias as $dia)
                                <div class="g-cell{{ $dia->isToday()?' g-today':'' }}{{ $dia->isWeekend()?' g-wk':'' }}{{ $dia->isMonday()?' g-mon':'' }}"
                                     style="width:{{ $DAY_W }}px;min-width:{{ $DAY_W }}px;height:46px"></div>
                            @endforeach

                            {{-- Barra --}}
                            @if(!$fora && $barWidth > 0)
                                <div class="g-bar{{ $entrega->isConcluida()?' g-bar-done':'' }}{{ $isAtrasada?' g-bar-late':'' }}"
                                     style="left:{{ $barLeft }}px;width:{{ $barWidth }}px;background-color:{{ $cor }};"
                                     wire:click="openDetails('{{ $entrega->cod_entrega }}')"
                                     @mouseenter="show($event,'{{ addslashes(Str::limit($entrega->dsc_entrega,60)) }}','{{ $entrega->bln_status }}','{{ $prazoFmt }}','{{ $durFmt }}',{{ $progPct }})"
                                     @mouseleave="hide()">

                                    @if($progPct > 0 && $progPct < 100)
                                        <div class="g-bar-prog" style="width:{{ $progPct }}%"></div>
                                    @endif

                                    <span class="g-bar-txt">
                                        @if($entrega->isConcluida())<i class="bi bi-check-lg me-1"></i>@endif
                                        {{ Str::limit($entrega->dsc_entrega, max(3, (int)($barWidth / 8))) }}
                                    </span>

                                    @if($barWidth > 80 && $prazo)
                                        <span class="g-bar-date">{{ $prazo->format('d/m') }}</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Fora do período --}}
                            @if($fora && $prazo)
                                <div class="g-out{{ $foraEsq?' g-out-l':' g-out-r' }}">
                                    <i class="bi bi-arrow-{{ $foraEsq?'left':'right' }}-circle-fill me-1"></i>
                                    {{ $prazoFmt }}
                                </div>
                            @endif
                        </div>
                    </div>

                @empty
                    <div class="g-row" style="padding:60px;justify-content:center;color:#9ca3af;text-align:center;display:block">
                        <i class="bi bi-bar-chart-steps" style="font-size:2rem;opacity:.3"></i>
                        <p class="mt-2 mb-0 fw-semibold">Nenhuma entrega para exibir no Gantt.</p>
                        <small>Adicione entregas com prazos para visualizá-las aqui.</small>
                    </div>
                @endforelse

                {{-- Linha "Hoje" posicionada em pixels absolutos --}}
                @if($hoje->between($inicio, $fim))
                    <div class="g-today-line" style="left:{{ $todayPx }}px">
                        <div class="g-today-badge">Hoje</div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ─── Sem prazo ─────────────────────────────────────────────────── --}}
    @php $semPrazo = $entregas->filter(fn($e) => !$e->dte_prazo); @endphp
    @if($semPrazo->isNotEmpty())
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body py-2 px-3">
                <p class="small fw-semibold text-warning mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    {{ $semPrazo->count() }} entrega(s) sem prazo — não exibidas no Gantt
                </p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($semPrazo->take(8) as $e)
                        <span class="badge rounded-pill bg-light text-dark border" wire:click="openDetails('{{ $e->cod_entrega }}')" style="cursor:pointer">
                            <span class="g-dot" style="background:{{ $statusColor[$e->bln_status] ?? '#64748b' }}"></span>
                            {{ Str::limit($e->dsc_entrega, 28) }}
                        </span>
                    @endforeach
                    @if($semPrazo->count() > 8)<span class="text-muted small align-self-center">+{{ $semPrazo->count() - 8 }} mais</span>@endif
                </div>
            </div>
        </div>
    @endif

    {{-- ─── Legenda ─────────────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap gap-4 mt-3 justify-content-center" style="font-size:.75rem;color:#6b7280">
        <span><span class="g-dot" style="background:#64748b"></span>Não Iniciado</span>
        <span><span class="g-dot" style="background:#2563eb"></span>Em Andamento</span>
        <span><span class="g-dot" style="background:#16a34a"></span>Concluído</span>
        <span><span class="g-dot" style="background:#dc2626"></span>Cancelado / Atrasado</span>
        <span><span class="g-dot" style="background:#d97706"></span>Suspenso</span>
        <span><span class="g-dot" style="background:rgba(0,0,0,.2);border:1px solid #ccc"></span>Tempo decorrido</span>
    </div>

    {{-- ─── Tooltip ─────────────────────────────────────────────────────── --}}
    <div x-show="vis" x-transition.opacity
         :style="'position:fixed;top:'+y+'px;left:'+x+'px;z-index:1050'"
         class="g-tooltip" style="display:none">
        <p class="g-tt-title" x-text="ttl"></p>
        <div class="g-tt-row"><span>Status</span><strong x-text="sts"></strong></div>
        <div class="g-tt-row"><span>Prazo</span><strong x-text="pra"></strong></div>
        <div class="g-tt-row"><span>Duração</span><strong x-text="dur"></strong></div>
        <div class="g-tt-row" x-show="prg > 0"><span>Decorrido</span><strong x-text="prg+'%'"></strong></div>
    </div>

</div>

<script>
function peiGantt() {
    return {
        vis: false, x: 0, y: 0,
        ttl: '', sts: '', pra: '', dur: '', prg: 0,
        show(e, ttl, sts, pra, dur, prg) {
            this.ttl = ttl; this.sts = sts; this.pra = pra; this.dur = dur; this.prg = prg;
            let tx = e.clientX + 14, ty = e.clientY + 14;
            if (tx + 280 > window.innerWidth)  tx = e.clientX - 294;
            if (ty + 160 > window.innerHeight) ty = e.clientY - 174;
            this.x = tx; this.y = ty; this.vis = true;
        },
        hide() { this.vis = false; }
    }
}
</script>

<style>
/* ── Scroll container ──────────────────────── */
.g-scroll {
    overflow-x: auto;
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
}
.g-scroll::-webkit-scrollbar { height: 5px; }
.g-scroll::-webkit-scrollbar-thumb { background:#d1d5db; border-radius:3px; }

/* ── Linhas ────────────────────────────────── */
.g-row {
    display: flex;
    align-items: stretch;
}
.g-hd {
    background: #f8f9fb;
    border-bottom: 1px solid #e2e8f0;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748b;
}
.g-hd-sticky {
    position: sticky;
    top: 0;
    z-index: 20;
    background: #fff;
    border-bottom: 2px solid #cbd5e1;
    box-shadow: 0 2px 6px rgba(0,0,0,.06);
}
.g-data-row {
    border-bottom: 1px solid #f1f5f9;
    transition: background .12s;
}
.g-data-row:hover { background: #f8faff; }
.g-data-row.g-done { opacity: .6; }
.g-data-row.g-late { background: #fff5f5; }

/* ── Coluna de rótulo ──────────────────────── */
.g-label-col {
    flex-shrink: 0;
    border-right: 2px solid #e2e8f0;
    background: #fafbfc;
}
.g-hd-label {
    display: flex;
    align-items: center;
    height: 100%;
    padding: 0 14px;
}
.g-label {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 0 12px;
    cursor: pointer;
    min-height: 46px;
    transition: background .12s;
}
.g-label:hover { background: #eff6ff; }
.g-status-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.g-label-text {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: .8rem;
    font-weight: 500;
    color: #374151;
}
.g-label-date {
    font-size: .68rem;
    color: #9ca3af;
    flex-shrink: 0;
    margin-left: auto;
}

/* ── Cabeçalho meses ───────────────────────── */
.g-months { display: flex; }
.g-month {
    flex-shrink: 0;
    padding: 6px 10px;
    border-right: 1px solid #e2e8f0;
    color: #3b82f6;
    font-weight: 800;
    font-size: .68rem;
    text-align: center;
    background: rgba(59,130,246,.04);
    white-space: nowrap;
    overflow: hidden;
}

/* ── Cabeçalho dias ────────────────────────── */
.g-days { display: flex; }
.g-day-cell {
    flex-shrink: 0;
    text-align: center;
    padding: 4px 0;
    border-right: 1px solid #f0f4f8;
}
.g-day-cell.g-wk  { background: #f9fafb; }
.g-day-cell.g-mon { border-left: 1px solid #e2e8f0; }
.g-day-cell.g-today { background: rgba(37,99,235,.07); }
.g-dn {
    display: block;
    font-size: .52rem;
    color: #9ca3af;
    text-transform: uppercase;
    font-weight: 700;
}
.g-dd {
    display: block;
    font-size: .75rem;
    font-weight: 700;
    color: #374151;
}
.g-day-cell.g-today .g-dd { color: #2563eb; }
.g-today-circle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #2563eb;
    color: #fff;
    font-size: .65rem;
}

/* ── Grade (fundo das linhas) ──────────────── */
.g-grid { display: flex; }
.g-cell {
    flex-shrink: 0;
    border-right: 1px solid #f4f6f8;
}
.g-cell.g-wk  { background: #f9fafb; }
.g-cell.g-mon { border-left: 1px solid #e8ecf0; }
.g-cell.g-today { background: rgba(37,99,235,.05); }

/* ── Barra ─────────────────────────────────── */
.g-bar {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    height: 26px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 9px;
    cursor: pointer;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.18);
    transition: box-shadow .15s, filter .15s;
    z-index: 5;
}
.g-bar:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.24);
    filter: brightness(1.08);
    z-index: 10;
}
.g-bar.g-bar-late {
    box-shadow: 0 0 0 2px #dc2626, 0 2px 8px rgba(220,38,38,.25);
}
.g-bar.g-bar-done { opacity: .65; }

.g-bar-prog {
    position: absolute;
    left: 0; top: 0; bottom: 0;
    background: rgba(0,0,0,.2);
    border-radius: 5px 0 0 5px;
    pointer-events: none;
}
.g-bar-txt {
    font-size: .68rem;
    font-weight: 700;
    color: rgba(255,255,255,.95);
    text-shadow: 0 1px 2px rgba(0,0,0,.3);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
    min-width: 0;
}
.g-bar-date {
    font-size: .6rem;
    font-weight: 700;
    color: rgba(255,255,255,.9);
    background: rgba(0,0,0,.2);
    padding: 1px 5px;
    border-radius: 3px;
    margin-left: 6px;
    flex-shrink: 0;
}

/* ── Fora do período ───────────────────────── */
.g-out {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: .65rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    white-space: nowrap;
}
.g-out-l { left:  8px; background:#fef3c7; color:#92400e; }
.g-out-r { right: 8px; background:#dbeafe; color:#1e40af; }

/* ── Linha Hoje ────────────────────────────── */
.g-today-line {
    position: absolute;
    top: 0; bottom: 0;
    width: 2px;
    background: #2563eb;
    z-index: 15;
    pointer-events: none;
}
.g-today-badge {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    background: #2563eb;
    color: #fff;
    font-size: .58rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 0 0 4px 4px;
    letter-spacing: .05em;
    white-space: nowrap;
}

/* ── Dots ──────────────────────────────────── */
.g-dot {
    display: inline-block;
    width: 9px; height: 9px;
    border-radius: 50%;
    margin-right: 5px;
    vertical-align: middle;
    flex-shrink: 0;
}

/* ── Tooltip ───────────────────────────────── */
.g-tooltip {
    background: #1e293b;
    color: #f1f5f9;
    border-radius: 10px;
    padding: 12px 16px;
    min-width: 220px;
    max-width: 290px;
    box-shadow: 0 12px 40px rgba(0,0,0,.3);
    pointer-events: none;
    font-size: .76rem;
}
.g-tt-title {
    font-weight: 700;
    font-size: .82rem;
    color: #f8fafc;
    margin: 0 0 8px;
    line-height: 1.35;
}
.g-tt-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 3px 0;
    border-top: 1px solid rgba(255,255,255,.08);
    color: #94a3b8;
}
.g-tt-row strong { color: #f1f5f9; }
</style>
