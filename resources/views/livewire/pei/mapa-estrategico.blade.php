<div>
    @guest
        <!-- Navbar Pública para Visitantes -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top">
            <div class="container-fluid px-4">
                <a class="navbar-brand fw-bold d-flex align-items-center" href="/">
                    <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-2">
                        <i class="bi bi-diagram-3 fs-5"></i>
                    </div>
                    <span class="text-primary">SEAE</span>
                    <span class="text-muted ms-2 small d-none d-md-inline">| Planejamento Estratégico</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarPublic">
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <li class="nav-item me-lg-3">
                            <button type="button" id="guestThemeSwitcher" class="btn btn-link nav-link px-2 d-flex align-items-center" title="Alternar Tema">
                                <i class="bi bi-circle-half fs-5"></i>
                            </button>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/"><i class="bi bi-house-door me-1"></i>Início</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div style="margin-top: 80px;"></div>
    @endguest

    @auth
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mapa Estratégico</li>
                        </ol>
                    </nav>
                    <h2 class="h4 fw-bold mb-0">Mapa Estratégico BSC</h2>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-calendar3 me-2"></i>PEI: {{ $peiAtivo?->dsc_pei ?? 'Nenhum ativo' }}
                    </span>
                </div>
            </div>
        </x-slot>
    @endauth

    @if(!$peiAtivo)
        <div class="container py-5">
            <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center p-4" role="alert">
                <i class="bi bi-exclamation-octagon-fill fs-2 me-4"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Nenhum PEI Ativo</h5>
                    <p class="mb-0">Não foi possível carregar o mapa estratégico pois não há um PEI ativo configurado no momento.</p>
                </div>
            </div>
        </div>
    @else
        <div class="container-fluid px-lg-4 py-3">
            @guest
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold text-primary mb-2">Mapa Estratégico Institucional</h1>
                    <p class="lead text-muted">{{ $peiAtivo->dsc_pei }} ({{ $peiAtivo->num_ano_inicio_pei }} - {{ $peiAtivo->num_ano_fim_pei }})</p>
                </div>
            @endguest

            <div class="map-container">
                <div class="card border-0 shadow-lg overflow-hidden rounded-4">
                    <div class="card-header bg-white py-3 border-bottom text-center">
                        <h5 class="mb-0 fw-bold text-uppercase letter-spacing-1">
                            {{ $organizacaoNome }}
                        </h5>
                    </div>
                    <div class="card-body p-0 bg-light bg-opacity-50">
                        <div class="bsc-map p-3 p-lg-4">
                            @foreach($perspectivas as $p)
                                <div class="perspective-row d-flex flex-column flex-lg-row align-items-stretch mb-3">
                                    <div class="perspective-label gradient-theme text-white d-flex align-items-center justify-content-center px-3 text-center fw-bold rounded-start shadow-sm mb-2 mb-lg-0" style="min-width: 180px; min-height: 80px;">
                                        <span class="text-uppercase small">{{ $p->dsc_perspectiva }}</span>
                                    </div>
                                    <div class="perspective-content flex-grow-1 bg-white p-3 border border-start-0 rounded-end shadow-sm d-flex flex-wrap align-items-center justify-content-center gap-3">
                                        @forelse($p->objetivos as $obj)
                                            <div class="objective-card p-3 rounded-3 border text-center shadow-sm hover-lift transition-all" 
                                                 style="width: 240px; border-left: 5px solid var(--bs-primary) !important; cursor: pointer;"
                                                 @auth onclick="window.location.href='{{ route('objetivos.index') }}?search={{ urlencode($obj->nom_objetivo_estrategico) }}'" @endauth>
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="badge bg-light text-primary border rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                                                        {{ $obj->num_nivel_hierarquico_apresentacao }}
                                                    </span>
                                                    @php 
                                                        $soma = 0; $cont = 0;
                                                        foreach($obj->indicadores as $ind) { $soma += $ind->calcularAtingimento(); $cont++; }
                                                        $media = $cont > 0 ? $soma / $cont : 0;
                                                        $statusCor = $media >= 100 ? 'success' : ($media >= 80 ? 'warning' : 'danger');
                                                    @endphp
                                                    <span class="badge bg-{{ $statusCor }}-subtle text-{{ $statusCor }} border border-{{ $statusCor }}-subtle rounded-pill" style="font-size: 0.65rem;">
                                                        {{ number_format($media, 1) }}%
                                                    </span>
                                                </div>
                                                <h6 class="mb-0 fw-bold small text-dark" style="line-height: 1.4;">
                                                    {{ Str::limit($obj->nom_objetivo_estrategico, 70) }}
                                                </h6>
                                            </div>
                                        @empty
                                            <span class="text-muted small italic">Nenhum objetivo definido</span>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 border-top d-flex flex-wrap justify-content-center gap-4">
                        <div class="d-flex align-items-center"><span class="status-dot bg-success me-2"></span><small class="text-muted">Atingido</small></div>
                        <div class="d-flex align-items-center"><span class="status-dot bg-warning me-2"></span><small class="text-muted">Em atenção</small></div>
                        <div class="d-flex align-items-center"><span class="status-dot bg-danger me-2"></span><small class="text-muted">Abaixo da meta</small></div>
                    </div>
                </div>
            </div>

            @guest
                <!-- Call to Action Público -->
                <div class="row mt-5 justify-content-center">
                    <div class="col-lg-8">
                        <div class="card gradient-theme text-white shadow-lg border-0 rounded-4 overflow-hidden">
                            <div class="card-body p-4 p-lg-5 text-center">
                                <h2 class="fw-bold mb-3"><i class="bi bi-unlock me-2"></i>Área Restrita</h2>
                                <p class="opacity-75 mb-4">Acesse o sistema completo para gerenciar indicadores, planos de ação, riscos e auditoria.</p>
                                <a href="{{ route('login') }}" class="btn btn-light btn-lg rounded-pill px-5 fw-bold shadow">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Acessar Painel de Gestão
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endguest
        </div>
    @endif

    <style>
        .letter-spacing-1 { letter-spacing: 1px; }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15)!important;
            border-color: var(--bs-primary) !important;
        }
        .transition-all { transition: all 0.3s ease; }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .icon-shape { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; }
        @media (max-width: 991px) {
            .perspective-label { border-radius: 8px !important; margin-bottom: 8px; }
            .perspective-content { border-radius: 8px !important; border-left: 1px solid #dee2e6 !important; }
        }
    </style>
</div>