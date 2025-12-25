<div>
    {{-- Enhanced Header with Gradient --}}
    <x-slot name="header">
        <div class="gradient-theme-header rounded-3 p-4 mb-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="avatar-greeting">
                            <div class="avatar-circle">
                                <i class="bi bi-person-circle"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="h3 mb-1 fw-bold text-white">{{ __('Bem-vindo(a),') }} {{ Auth::user()->name }}!</h1>
                            <p class="mb-0 text-white-50 small">
                                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('d \d\e F \d\e Y') }}
                                <span class="mx-2">•</span>
                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25">
                                    <i class="bi bi-circle-fill pulse-dot me-1" style="font-size: 0.5rem;"></i>{{ __('Online') }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Enhanced Stats Cards with Animations --}}
    <div class="row g-3 g-lg-4 mb-4">
        {{-- Total Organizações --}}
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-card-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-header">
                        <span class="stat-card-label">Organizações</span>
                    </div>
                    <div class="stat-card-value">
                        <span class="stat-number">{{ $totalOrganizacoes }}</span>
                    </div>
                    <p class="stat-card-info">
                        <i class="bi bi-check-circle me-1"></i>Cadastradas
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Objetivos --}}
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-card-info">
                <div class="stat-card-icon">
                    <i class="bi bi-bullseye"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-header">
                        <span class="stat-card-label">Objetivos</span>
                    </div>
                    <div class="stat-card-value">
                        <span class="stat-number">{{ $totalObjetivos }}</span>
                    </div>
                    <p class="stat-card-info">
                        <i class="bi bi-graph-up-arrow me-1"></i>Estratégicos
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Indicadores --}}
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-card-success">
                <div class="stat-card-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-header">
                        <span class="stat-card-label">Indicadores</span>
                    </div>
                    <div class="stat-card-value">
                        <span class="stat-number">{{ $totalIndicadores }}</span>
                    </div>
                    <p class="stat-card-info">
                        <i class="bi bi-clipboard-data me-1"></i>Monitorados
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Planos --}}
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-icon">
                    <i class="bi bi-list-task"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-header">
                        <span class="stat-card-label">Planos de Ação</span>
                    </div>
                    <div class="stat-card-value">
                        <span class="stat-number">{{ $totalPlanos }}</span>
                    </div>
                    @if($planosAtrasados > 0)
                        <p class="stat-card-info text-danger fw-bold">
                            <i class="bi bi-exclamation-circle me-1"></i>{{ $planosAtrasados }} Atrasados
                        </p>
                    @else
                         <p class="stat-card-info text-success">
                            <i class="bi bi-check-all me-1"></i>Em dia
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-3 g-lg-4 mb-4">
         {{-- Riscos Críticos --}}
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-card-danger" style="--stat-color: var(--bs-danger);">
                <div class="stat-card-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-header">
                        <span class="stat-card-label">Riscos Críticos</span>
                        <span class="stat-card-badge badge-danger" style="background: rgba(var(--bs-danger-rgb), 0.1); color: var(--bs-danger);">
                            <i class="bi bi-fire"></i>Alerta
                        </span>
                    </div>
                    <div class="stat-card-value">
                        <span class="stat-number">{{ $riscosCriticos }}</span>
                    </div>
                    <p class="stat-card-info">
                        <i class="bi bi-shield-exclamation me-1"></i>Requerem atenção
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="row g-3 g-lg-4 mb-4">
        {{-- Welcome Component --}}
        <div class="col-12 col-xl-8">
            <div class="welcome-card">
                 <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="card-title fw-bold mb-3">Bem-vindo ao Sistema de Planejamento Estratégico</h4>
                        <p class="card-text text-secondary">
                            Este sistema permite o gerenciamento completo do Planejamento Estratégico Institucional (PEI), 
                            incluindo a definição de Missão, Visão e Valores, monitoramento de Objetivos Estratégicos, 
                            Indicadores de Desempenho e Planos de Ação.
                        </p>
                        <hr>
                        <h5 class="fw-bold fs-6 mb-3">Acesso Rápido</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('pei.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-clipboard-data me-1"></i> Consultar PEI
                            </a>
                            <a href="{{ route('objetivos.index') }}" class="btn btn-outline-info">
                                <i class="bi bi-bullseye me-1"></i> Objetivos
                            </a>
                             <a href="{{ route('planos.index') }}" class="btn btn-outline-warning text-dark">
                                <i class="bi bi-list-task me-1"></i> Meus Planos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Enhanced Checklist with Progress (Mantido como exemplo de componente visual) --}}
        <div class="col-12 col-xl-4">
            <div class="checklist-card">
                <div class="checklist-header">
                    <div class="checklist-icon">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <div class="checklist-title-area">
                        <h2 class="checklist-title">Status do Sistema</h2>
                         <div class="checklist-progress-wrapper">
                            <span class="checklist-percentage text-success">Operacional</span>
                        </div>
                    </div>
                </div>

                <div class="checklist-body">
                    <div class="checklist-item checklist-item-completed">
                        <div class="checklist-item-icon">
                            <i class="bi bi-database-check"></i>
                        </div>
                        <div class="checklist-item-content">
                            <div class="checklist-item-title">Banco de Dados</div>
                            <div class="checklist-item-description">Conectado e Sincronizado</div>
                        </div>
                    </div>

                    <div class="checklist-item checklist-item-completed">
                        <div class="checklist-item-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="checklist-item-content">
                            <div class="checklist-item-title">Segurança</div>
                            <div class="checklist-item-description">Políticas de acesso ativas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom Styles for Enhanced Dashboard --}}
    <style>
        /* Dashboard Header - uses global .gradient-theme-header class */

        /* Avatar Greeting */
        .avatar-greeting .avatar-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        /* Pulse Animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .pulse-dot {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Enhanced Stat Cards */
        .stat-card {
            background: var(--bs-body-bg);
            border-radius: 16px;
            padding: 1.5rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--bs-border-color);
        }

        [data-bs-theme="dark"] .stat-card {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--stat-color), transparent);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .stat-card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.4);
            background: rgba(255, 255, 255, 0.08);
        }

        .stat-card-primary { --stat-color: var(--bs-primary); }
        .stat-card-success { --stat-color: var(--bs-success); }
        .stat-card-warning { --stat-color: var(--bs-warning); }
        .stat-card-info { --stat-color: var(--bs-info); }
        .stat-card-danger { --stat-color: var(--bs-danger); }

        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            background: var(--stat-color);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-card-content {
            flex: 1;
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .stat-card-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--bs-secondary);
        }

        .stat-card-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge-success {
            background: rgba(var(--bs-success-rgb), 0.1);
            color: var(--bs-success);
        }

        .badge-warning {
            background: rgba(var(--bs-warning-rgb), 0.1);
            color: var(--bs-warning);
        }

        .badge-info {
            background: rgba(var(--bs-info-rgb), 0.1);
            color: var(--bs-info);
        }

        .stat-card-value {
            margin-bottom: 0.75rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--bs-body-color);
        }

        .stat-total {
            font-size: 1rem;
            color: var(--bs-secondary);
            margin-left: 0.25rem;
        }

        .stat-status-ready {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--bs-success);
        }

        .stat-milestone {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--bs-info);
        }

        .stat-card-info {
            font-size: 0.875rem;
            color: var(--bs-secondary);
            margin: 0;
        }

        .stat-progress {
            height: 6px;
            border-radius: 3px;
            background: rgba(var(--bs-secondary-rgb), 0.1);
            overflow: hidden;
        }

        .stat-progress .progress-bar {
            background: var(--stat-color);
        }

        .stat-card-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .stat-card-tags .tag {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            background: rgba(var(--bs-secondary-rgb), 0.1);
            color: var(--bs-body-color);
        }

        /* Enhanced Checklist */
        .checklist-card {
            background: var(--bs-body-bg);
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            border: 1px solid var(--bs-border-color);
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .checklist-card {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .checklist-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05), rgba(var(--bs-primary-rgb), 0.1));
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .checklist-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--bs-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .checklist-title-area {
            flex: 1;
        }

        .checklist-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--bs-body-color);
        }

        .checklist-progress-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .checklist-progress {
            flex: 1;
            height: 8px;
            border-radius: 4px;
            background: rgba(var(--bs-secondary-rgb), 0.1);
        }

        .checklist-percentage {
            font-size: 0.875rem;
            font-weight: 600;
            min-width: 40px;
            text-align: right;
        }

        .bg-gradient-success {
            background: linear-gradient(90deg, var(--bs-success), #20c997);
        }

        .checklist-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .checklist-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .checklist-item-completed {
            background: rgba(var(--bs-success-rgb), 0.05);
            border: 1px solid rgba(var(--bs-success-rgb), 0.2);
        }

        .checklist-item-active {
            background: rgba(var(--bs-primary-rgb), 0.05);
            border: 1px solid rgba(var(--bs-primary-rgb), 0.3);
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.1);
        }

        .checklist-item-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .checklist-item-completed .checklist-item-icon {
            color: var(--bs-success);
        }

        .checklist-item-active .checklist-item-icon {
            color: var(--bs-primary);
        }

        .checklist-item-content {
            flex: 1;
            min-width: 0;
        }

        .checklist-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--bs-body-color);
        }

        .checklist-item-completed .checklist-item-title {
            color: var(--bs-success);
        }

        .checklist-item-active .checklist-item-title {
            color: var(--bs-primary);
        }

        .checklist-item-description {
            font-size: 0.875rem;
            color: var(--bs-secondary);
        }

        .checklist-footer {
            padding: 1rem 1.5rem 1.5rem;
        }

        .btn-hover-lift {
            transition: all 0.3s ease;
        }

        .btn-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Responsive Adjustments */
        @media (max-width: 991px) {
            .stat-card-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .stat-number {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 767px) {
            .avatar-greeting .avatar-circle {
                width: 48px;
                height: 48px;
                font-size: 1.5rem;
            }

            .stat-card {
                padding: 1rem;
            }
        }
    </style>
</div>
