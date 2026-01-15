{{-- View Calendário - Modo Funcional com Navegação --}}
@php
    use Carbon\Carbon;

    // Usa as propriedades do componente para determinar mês/ano
    $mesAtual = Carbon::createFromDate($calendarioAno, $calendarioMes, 1);
    $hoje = now();

    // Calcula primeiro e último dia do grid (inclui dias do mês anterior/próximo para completar semanas)
    $primeiroDia = $mesAtual->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
    $ultimoDia = $mesAtual->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

    // Gera array de semanas
    $semanas = [];
    $diaAtual = $primeiroDia->copy();

    while ($diaAtual <= $ultimoDia) {
        $semana = [];
        for ($i = 0; $i < 7; $i++) {
            $semana[] = $diaAtual->copy();
            $diaAtual->addDay();
        }
        $semanas[] = $semana;
    }

    // Agrupar entregas por data de prazo
    $entregasPorData = $entregas->filter(fn($e) => $e->dte_prazo)->groupBy(fn($e) => $e->dte_prazo->format('Y-m-d'));

    // Anos disponíveis para seleção (5 anos atrás até 5 anos à frente)
    $anosDisponiveis = range($hoje->year - 5, $hoje->year + 5);

    // Nomes dos meses em português
    $mesesNomes = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];
@endphp

<div class="notion-calendario" wire:key="calendario-{{ $calendarioMes }}-{{ $calendarioAno }}">
    <div class="card border-0 shadow-sm overflow-hidden">
        {{-- Navegação do mês com controles funcionais --}}
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center py-2">
                {{-- Botão Mês Anterior --}}
                <button
                    wire:click="calendarioAnterior"
                    class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                    title="Mês Anterior"
                >
                    <i class="bi bi-chevron-left"></i>
                    <span class="d-none d-sm-inline ms-1">Anterior</span>
                </button>

                {{-- Seletor de Mês/Ano --}}
                <div class="d-flex align-items-center gap-2">
                    {{-- Seletor de Mês --}}
                    <select
                        wire:change="calendarioIrPara($event.target.value, {{ $calendarioAno }})"
                        class="form-select form-select-sm border-0 bg-light fw-bold text-center"
                        style="width: auto;"
                    >
                        @foreach($mesesNomes as $numMes => $nomeMes)
                            <option value="{{ $numMes }}" {{ $calendarioMes == $numMes ? 'selected' : '' }}>
                                {{ $nomeMes }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Seletor de Ano --}}
                    <select
                        wire:change="calendarioIrPara({{ $calendarioMes }}, $event.target.value)"
                        class="form-select form-select-sm border-0 bg-light fw-bold text-center"
                        style="width: auto;"
                    >
                        @foreach($anosDisponiveis as $ano)
                            <option value="{{ $ano }}" {{ $calendarioAno == $ano ? 'selected' : '' }}>
                                {{ $ano }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Botão Hoje --}}
                    @if($calendarioMes != $hoje->month || $calendarioAno != $hoje->year)
                        <button
                            wire:click="calendarioHoje"
                            class="btn btn-sm btn-primary rounded-pill px-3 ms-2"
                            title="Ir para Hoje"
                        >
                            <i class="bi bi-calendar-check me-1"></i>
                            Hoje
                        </button>
                    @endif
                </div>

                {{-- Botão Próximo Mês --}}
                <button
                    wire:click="calendarioProximo"
                    class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                    title="Próximo Mês"
                >
                    <span class="d-none d-sm-inline me-1">Próximo</span>
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>

            {{-- Indicador do mês atual --}}
            <div class="text-center pb-2">
                <small class="text-muted">
                    @php
                        $totalEntregasMes = $entregas->filter(function($e) use ($mesAtual) {
                            return $e->dte_prazo && $e->dte_prazo->month == $mesAtual->month && $e->dte_prazo->year == $mesAtual->year;
                        })->count();
                    @endphp
                    <i class="bi bi-calendar-event me-1"></i>
                    {{ $totalEntregasMes }} {{ $totalEntregasMes == 1 ? 'entrega' : 'entregas' }} neste mês
                </small>
            </div>
        </div>

        {{-- Grid do calendário --}}
        <div class="notion-calendario-grid">
            {{-- Cabeçalho dos dias da semana --}}
            <div class="notion-calendario-header">
                @foreach(['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $index => $diaSemana)
                    <div class="notion-calendario-weekday {{ in_array($index, [0, 6]) ? 'weekend' : '' }}">
                        {{ $diaSemana }}
                    </div>
                @endforeach
            </div>

            {{-- Semanas --}}
            @foreach($semanas as $semana)
                <div class="notion-calendario-week">
                    @foreach($semana as $dia)
                        @php
                            $diaKey = $dia->format('Y-m-d');
                            $entregasDoDia = $entregasPorData[$diaKey] ?? collect();
                            $isMesAtual = $dia->month === $mesAtual->month;
                            $isHoje = $dia->isToday();
                            $isPassado = $dia->isPast() && !$isHoje;
                            $isWeekend = $dia->isWeekend();

                            // Conta entregas por status
                            $entregasPendentes = $entregasDoDia->filter(fn($e) => !$e->isConcluida())->count();
                            $entregasAtrasadas = $entregasDoDia->filter(fn($e) => $e->isAtrasada())->count();
                        @endphp

                        <div
                            class="notion-calendario-day {{ !$isMesAtual ? 'other-month' : '' }} {{ $isHoje ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }} {{ $entregasAtrasadas > 0 ? 'has-overdue' : '' }}"
                            @can('update', $plano)
                                wire:click="openQuickAdd('Não Iniciado')"
                                style="cursor: pointer;"
                            @endcan
                            title="{{ $dia->format('d/m/Y') }}{{ $entregasDoDia->count() > 0 ? ' - ' . $entregasDoDia->count() . ' entrega(s)' : '' }}"
                        >
                            <div class="notion-calendario-daynum {{ $isHoje ? 'active' : '' }}">
                                {{ $dia->format('d') }}
                            </div>

                            <div class="notion-calendario-events">
                                @foreach($entregasDoDia->take(3) as $entrega)
                                    <div
                                        class="notion-calendario-event {{ $entrega->isConcluida() ? 'completed' : '' }} {{ $entrega->isAtrasada() ? 'overdue' : '' }}"
                                        style="background-color: {{ $entrega->getStatusColor() }};"
                                        wire:click.stop="openDetails('{{ $entrega->cod_entrega }}')"
                                        title="{{ $entrega->dsc_entrega }} - {{ $entrega->bln_status }}"
                                    >
                                        <span class="notion-calendario-event-text">
                                            {{ Str::limit($entrega->dsc_entrega, 18) }}
                                        </span>
                                    </div>
                                @endforeach

                                @if($entregasDoDia->count() > 3)
                                    <div
                                        class="notion-calendario-more"
                                        wire:click.stop="$dispatch('show-day-entregas', { date: '{{ $diaKey }}' })"
                                    >
                                        +{{ $entregasDoDia->count() - 3 }} mais
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    {{-- Entregas sem prazo --}}
    @php
        $semPrazo = $entregas->filter(fn($e) => !$e->dte_prazo);
    @endphp

    @if($semPrazo->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-calendar-x me-2 text-warning"></i>
                    Entregas sem prazo definido
                    <span class="badge bg-warning text-dark ms-2">{{ $semPrazo->count() }}</span>
                </h6>
                <small class="text-muted">Clique para definir um prazo</small>
            </div>
            <div class="card-body p-0">
                <div class="d-flex flex-wrap gap-2 p-3">
                    @foreach($semPrazo->take(15) as $entrega)
                        @php
                            $prioInfo = $entrega->getPrioridadeInfo();
                        @endphp
                        <div
                            class="notion-calendario-chip"
                            style="background-color: {{ $entrega->getStatusColor() }}40; border-left: 3px solid {{ $entrega->getStatusColor() }};"
                            wire:click="openDetails('{{ $entrega->cod_entrega }}')"
                            title="{{ $entrega->dsc_entrega }}"
                        >
                            <i class="bi bi-{{ $prioInfo['icon'] ?? 'dot' }} me-1" style="color: {{ $prioInfo['color'] ?? $entrega->getStatusColor() }};"></i>
                            {{ Str::limit($entrega->dsc_entrega, 25) }}
                        </div>
                    @endforeach
                    @if($semPrazo->count() > 15)
                        <span class="text-muted small align-self-center">
                            e mais {{ $semPrazo->count() - 15 }} entregas...
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Legenda --}}
    <div class="d-flex flex-wrap gap-3 mt-3 small text-muted justify-content-center">
        <div class="d-flex align-items-center gap-2">
            <div class="notion-calendario-legend-dot today"></div>
            <span>Hoje</span>
        </div>
        @foreach(\App\Models\ActionPlan\Entrega::STATUS_OPTIONS as $status)
            <div class="d-flex align-items-center gap-2">
                <div class="notion-calendario-legend-dot" style="background-color: {{ \App\Models\ActionPlan\Entrega::STATUS_COLORS[$status] }}"></div>
                <span>{{ $status }}</span>
            </div>
        @endforeach
        <div class="d-flex align-items-center gap-2">
            <div class="notion-calendario-legend-dot overdue"></div>
            <span>Atrasada</span>
        </div>
    </div>
</div>

<style>
    .notion-calendario-grid {
        display: flex;
        flex-direction: column;
    }

    .notion-calendario-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        border-bottom: 2px solid #e4e4e4;
        background: #f8f9fa;
    }

    .notion-calendario-weekday {
        padding: 12px;
        text-align: center;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
    }

    .notion-calendario-weekday.weekend {
        color: #dc3545;
        background: rgba(220, 53, 69, 0.05);
    }

    .notion-calendario-week {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }

    .notion-calendario-day {
        min-height: 110px;
        border-right: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
        padding: 6px;
        transition: all 0.2s ease;
        position: relative;
    }

    .notion-calendario-day:hover {
        background-color: #f0f7ff;
        box-shadow: inset 0 0 0 2px rgba(var(--bs-primary-rgb), 0.2);
    }

    .notion-calendario-day:nth-child(7n) {
        border-right: none;
    }

    .notion-calendario-day.other-month {
        background-color: #fafafa;
    }

    .notion-calendario-day.other-month .notion-calendario-daynum {
        color: #ccc;
    }

    .notion-calendario-day.weekend {
        background-color: #fef9f9;
    }

    .notion-calendario-day.today {
        background-color: #e8f4fd;
        box-shadow: inset 0 0 0 2px rgba(var(--bs-primary-rgb), 0.3);
    }

    .notion-calendario-day.has-overdue {
        background: linear-gradient(135deg, transparent 95%, #dc3545 95%);
    }

    .notion-calendario-daynum {
        font-size: 0.85rem;
        font-weight: 600;
        color: #37352f;
        padding: 4px 8px;
        display: inline-block;
    }

    .notion-calendario-daynum.active {
        background: linear-gradient(135deg, var(--bs-primary), var(--theme-primary-light, #4361EE));
        color: white;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), 0.3);
    }

    .notion-calendario-events {
        display: flex;
        flex-direction: column;
        gap: 3px;
        margin-top: 4px;
    }

    .notion-calendario-event {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.15s ease;
        overflow: hidden;
        color: #37352f;
        font-weight: 500;
    }

    .notion-calendario-event:hover {
        transform: translateX(2px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        filter: brightness(0.95);
    }

    .notion-calendario-event.completed {
        opacity: 0.6;
        text-decoration: line-through;
    }

    .notion-calendario-event.overdue {
        border-left: 3px solid #dc3545;
        animation: pulse-overdue 2s ease-in-out infinite;
    }

    @keyframes pulse-overdue {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .notion-calendario-event-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    .notion-calendario-more {
        font-size: 0.65rem;
        color: var(--bs-primary);
        padding: 2px 6px;
        cursor: pointer;
        font-weight: 600;
    }

    .notion-calendario-more:hover {
        text-decoration: underline;
    }

    .notion-calendario-chip {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.15s ease;
        font-weight: 500;
    }

    .notion-calendario-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .notion-calendario-legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .notion-calendario-legend-dot.today {
        background: linear-gradient(135deg, var(--bs-primary), var(--theme-primary-light, #4361EE));
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.2);
    }

    .notion-calendario-legend-dot.overdue {
        background: #dc3545;
        animation: pulse-overdue 2s ease-in-out infinite;
    }

    /* Dark mode */
    [data-bs-theme="dark"] .notion-calendario-header {
        background: #2d3748;
        border-color: #4a5568;
    }

    [data-bs-theme="dark"] .notion-calendario-weekday {
        color: #a0aec0;
    }

    [data-bs-theme="dark"] .notion-calendario-day {
        border-color: #4a5568;
    }

    [data-bs-theme="dark"] .notion-calendario-day:hover {
        background-color: #2d3748;
    }

    [data-bs-theme="dark"] .notion-calendario-day.other-month {
        background-color: #1a202c;
    }

    [data-bs-theme="dark"] .notion-calendario-day.today {
        background-color: #2c5282;
    }

    [data-bs-theme="dark"] .notion-calendario-daynum {
        color: #e2e8f0;
    }

    [data-bs-theme="dark"] .notion-calendario-event {
        color: #1a202c;
    }

    /* Responsivo */
    @media (max-width: 768px) {
        .notion-calendario-day {
            min-height: 70px;
            padding: 4px;
        }

        .notion-calendario-daynum {
            font-size: 0.75rem;
            padding: 2px 4px;
        }

        .notion-calendario-daynum.active {
            width: 26px;
            height: 26px;
        }

        .notion-calendario-event-text {
            display: none;
        }

        .notion-calendario-event {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            padding: 0;
        }

        .notion-calendario-events {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 4px;
        }

        .notion-calendario-more {
            font-size: 0.6rem;
        }
    }
</style>
