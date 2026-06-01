<div>
    @php
        $todas       = $entregasAgrupadas->flatten();
        $emAndamento = $todas->where('bln_status', 'Em Andamento')->count();
        $totalPlanos = $entregasAgrupadas->count();
        $prioColors  = \App\Models\ActionPlan\Entrega::PRIORIDADE_OPTIONS;
        $statusColors= \App\Models\ActionPlan\Entrega::STATUS_COLORS;
    @endphp

    {{-- ═══════════ Header ═══════════ --}}
    <div class="me-header mb-4">
        <div class="me-header-bg"></div>
        <div class="position-relative p-4 p-lg-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2" style="--bs-breadcrumb-divider-color:rgba(255,255,255,.5);">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" wire:navigate style="color:rgba(255,255,255,.75);text-decoration:none;">Dashboard</a></li>
                    <li class="breadcrumb-item active" style="color:#fff;">Minhas Entregas</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="me-header-icon"><i class="bi bi-person-check-fill"></i></div>
                    <div>
                        <h1 class="h3 fw-bold mb-0 text-white">Minhas Entregas</h1>
                        <p class="mb-0" style="color:rgba(255,255,255,.78);font-size:.9rem;">Tarefas atribuídas a você, agrupadas por plano de ação.</p>
                    </div>
                </div>
                @if($totalAtrasadas > 0)
                <div class="me-alert-pill">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ $totalAtrasadas }} entrega{{ $totalAtrasadas > 1 ? 's' : '' }} atrasada{{ $totalAtrasadas > 1 ? 's' : '' }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════ KPIs ═══════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 me-kpi">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="me-kpi-icon" style="background:rgba(27,64,142,.1);color:#1B408E;"><i class="bi bi-inbox"></i></div>
                    <div>
                        <div class="me-kpi-value">{{ $totalPendente }}</div>
                        <div class="me-kpi-label">Pendentes</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 me-kpi">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="me-kpi-icon" style="background:rgba(13,110,253,.12);color:#0d6efd;"><i class="bi bi-arrow-repeat"></i></div>
                    <div>
                        <div class="me-kpi-value">{{ $emAndamento }}</div>
                        <div class="me-kpi-label">Em andamento</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 me-kpi {{ $totalAtrasadas > 0 ? 'me-kpi-danger' : '' }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="me-kpi-icon" style="background:rgba(220,53,69,.12);color:#dc3545;"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="me-kpi-value {{ $totalAtrasadas > 0 ? 'text-danger' : '' }}">{{ $totalAtrasadas }}</div>
                        <div class="me-kpi-label">Atrasadas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 me-kpi">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="me-kpi-icon" style="background:rgba(46,139,87,.12);color:#2e8b57;"><i class="bi bi-list-task"></i></div>
                    <div>
                        <div class="me-kpi-value">{{ $totalPlanos }}</div>
                        <div class="me-kpi-label">{{ $totalPlanos == 1 ? 'Plano' : 'Planos' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ Filtros ═══════════ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-body border-end-0"><i class="bi bi-search text-body-secondary"></i></span>
                        <input type="text" wire:model.live.debounce.400ms="busca" class="form-control border-start-0 ps-0" placeholder="Buscar entrega...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select wire:model.live="filtroStatus" class="form-select">
                        <option value="">Todos os status</option>
                        @foreach($statusOptions as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select wire:model.live="filtroPrioridade" class="form-select">
                        <option value="">Todas as prioridades</option>
                        @foreach($prioridades as $k => $v)
                            <option value="{{ $k }}">{{ $v['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div wire:loading.delay class="mt-2">
                <span class="spinner-border spinner-border-sm text-primary me-1"></span>
                <small class="text-body-secondary">Atualizando…</small>
            </div>
        </div>
    </div>

    {{-- ═══════════ Lista ═══════════ --}}
    @if($entregasAgrupadas->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="me-empty-icon mx-auto mb-3"><i class="bi bi-check2-circle"></i></div>
                <h5 class="fw-bold text-success mb-1">Tudo em dia!</h5>
                <p class="text-body-secondary mb-0">
                    @if($busca || $filtroStatus || $filtroPrioridade)
                        Nenhuma entrega corresponde aos filtros aplicados.
                    @else
                        Você não tem entregas pendentes atribuídas no momento.
                    @endif
                </p>
                @if($busca || $filtroStatus || $filtroPrioridade)
                    <button wire:click="$set('busca','');$set('filtroStatus','');$set('filtroPrioridade','')" class="btn btn-sm btn-outline-secondary rounded-pill mt-3">
                        <i class="bi bi-x-lg me-1"></i>Limpar filtros
                    </button>
                @endif
            </div>
        </div>
    @else
        @foreach($entregasAgrupadas as $planoId => $entregas)
        @php
            $plano       = $entregas->first()?->planoDeAcao;
            $atrasadasNo = $entregas->filter(fn($e) => $e->dte_prazo && $e->dte_prazo->isPast())->count();
        @endphp
        <div class="card border-0 shadow-sm mb-4 me-plano-card">
            {{-- Cabeçalho do plano --}}
            <div class="card-header bg-body border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="me-plano-icon"><i class="bi bi-list-task"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <h6 class="fw-bold mb-0 text-body text-truncate">{{ $plano?->dsc_plano_de_acao ?? 'Plano não identificado' }}</h6>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                            @if($plano?->objetivo?->perspectiva)
                                <span class="badge rounded-pill bg-primary-subtle text-primary fw-medium">{{ $plano->objetivo->perspectiva->dsc_perspectiva }}</span>
                            @endif
                            @if($plano?->objetivo)
                                <small class="text-body-secondary text-truncate"><i class="bi bi-bullseye me-1"></i>{{ $plano->objetivo->nom_objetivo }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary">{{ $entregas->count() }}</span>
                        @if($atrasadasNo > 0)
                            <span class="badge rounded-pill bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle me-1"></i>{{ $atrasadasNo }}</span>
                        @endif
                        <a href="{{ route('planos.entregas', $planoId) }}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            Ver plano <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Entregas --}}
            <div class="card-body p-0">
                @foreach($entregas as $entrega)
                @php
                    $atrasada = $entrega->dte_prazo && $entrega->dte_prazo->isPast();
                    $prio     = $prioColors[$entrega->cod_prioridade] ?? $prioColors['media'];
                    $stColor  = $statusColors[$entrega->bln_status] ?? '#e3e2e0';
                @endphp
                <div class="me-entrega {{ $atrasada ? 'is-atrasada' : '' }}" style="border-left-color:{{ $prio['color'] }};">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-grow-1 min-w-0">
                            <p class="fw-semibold text-body mb-2">{{ $entrega->dsc_entrega }}</p>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="me-chip" style="background:{{ $stColor }};">{{ $entrega->bln_status }}</span>
                                <span class="me-chip" style="background:{{ $prio['color'] }};">
                                    <i class="bi bi-{{ $prio['icon'] }} me-1"></i>{{ $prio['label'] }}
                                </span>
                                @if($entrega->dte_prazo)
                                    <span class="me-prazo {{ $atrasada ? 'is-late' : '' }}">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $entrega->dte_prazo->format('d/m/Y') }}
                                        <span class="opacity-75">· {{ $entrega->dte_prazo->diffForHumans() }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('planos.entregas', $planoId) }}" wire:navigate
                           class="btn btn-sm btn-icon btn-ghost-primary rounded-circle flex-shrink-0"
                           title="Abrir no plano">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    @endif

    <style>
        /* ─── Header ─── */
        .me-header { position: relative; border-radius: 1rem; overflow: hidden; box-shadow: 0 8px 24px rgba(27,64,142,.18); }
        .me-header-bg { position: absolute; inset: 0; background: linear-gradient(120deg, #1a3a5c 0%, #1B408E 55%, #2e6da4 100%); }
        .me-header-bg::after {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(ellipse 60% 80% at 90% 20%, rgba(255,255,255,.12) 0%, transparent 60%);
        }
        .me-header-icon {
            width: 54px; height: 54px; border-radius: .875rem; flex-shrink: 0;
            background: rgba(255,255,255,.18); backdrop-filter: blur(6px);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.5rem;
        }
        .me-alert-pill {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.95); color: #c0392b;
            font-weight: 700; font-size: .82rem; padding: .5rem 1rem; border-radius: 999px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
        }

        /* ─── KPIs ─── */
        .me-kpi { border-radius: .875rem; transition: transform .2s ease, box-shadow .2s ease; }
        .me-kpi:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(27,64,142,.12) !important; }
        .me-kpi-icon { width: 46px; height: 46px; border-radius: .75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
        .me-kpi-value { font-size: 1.6rem; font-weight: 800; line-height: 1; color: var(--bs-emphasis-color); }
        .me-kpi-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--bs-secondary-color); margin-top: .15rem; }

        /* ─── Plano card ─── */
        .me-plano-card { border-radius: .875rem; overflow: hidden; }
        .me-plano-icon {
            width: 40px; height: 40px; border-radius: .65rem; flex-shrink: 0;
            background: rgba(27,64,142,.1); color: #1B408E;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .min-w-0 { min-width: 0; }

        /* ─── Entrega item ─── */
        .me-entrega {
            padding: .95rem 1.25rem; border-left: 4px solid transparent;
            border-bottom: 1px solid var(--bs-border-color-translucent);
            transition: background .15s ease;
        }
        .me-entrega:last-child { border-bottom: 0; }
        .me-entrega:hover { background: var(--bs-tertiary-bg); }
        .me-entrega.is-atrasada { background: rgba(220,53,69,.05); }

        .me-chip {
            display: inline-flex; align-items: center;
            font-size: .72rem; font-weight: 600; color: #2d3748;
            padding: .2rem .6rem; border-radius: 6px; white-space: nowrap;
        }
        .me-prazo {
            display: inline-flex; align-items: center;
            font-size: .75rem; color: var(--bs-secondary-color); font-weight: 500;
        }
        .me-prazo.is-late { color: #dc3545; font-weight: 700; }

        .me-empty-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(46,139,87,.12); color: #2e8b57;
            display: flex; align-items: center; justify-content: center; font-size: 2.2rem;
        }

        /* ─── Dark mode ─── */
        [data-bs-theme="dark"] .me-chip { color: #1a1a1a; }
        [data-bs-theme="dark"] .me-plano-icon { background: rgba(127,179,245,.15); color: #7fb3f5; }
        [data-bs-theme="dark"] .me-entrega.is-atrasada { background: rgba(220,53,69,.1); }
        [data-bs-theme="dark"] .me-alert-pill { background: rgba(30,41,59,.95); color: #f1948a; }
    </style>
</div>
