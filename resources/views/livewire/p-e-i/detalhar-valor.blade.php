<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('pei.valores') }}" wire:navigate class="text-decoration-none">Valores Organizacionais</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $valor->nom_valor }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-gray-800 mb-0">
                <i class="bi bi-gem me-2 text-warning"></i>Detalhes do Valor
            </h2>
            <p class="text-muted mb-0">
                {{ $valor->organizacao->nom_organizacao }} • {{ $valor->pei->dsc_pei }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pei.valores') }}" wire:navigate class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-circle bg-warning bg-opacity-10 me-3">
                            <i class="bi bi-star-fill text-warning fs-3"></i>
                        </div>
                        <h3 class="fw-bold mb-0 text-body-emphasis">{{ $valor->nom_valor }}</h3>
                    </div>

                    <h6 class="text-uppercase text-muted small fw-bold mb-2">Descrição</h6>
                    <div class="p-4 bg-light rounded border-start border-4 border-warning">
                        @if($valor->dsc_valor)
                            <p class="mb-0 fs-5 text-secondary" style="line-height: 1.6;">{{ $valor->dsc_valor }}</p>
                        @else
                            <p class="mb-0 text-muted fst-italic">Nenhuma descrição cadastrada.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- O papel do valor no PEI. Os blocos "Em breve" e os contadores
                 fixos em 0 de objetivos/iniciativas saíram: valor não tem vínculo
                 com objetivo no modelo de dados, e a tela afirmava uma medição
                 que não existe. --}}
            <div class="alert alert-light border small text-muted mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Valores são os princípios que orientam o comportamento da organização na execução da estratégia.
                Eles compõem a Identidade Estratégica do ciclo, ao lado da Missão e da Visão, e não têm metas próprias.
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            <!-- Metadados -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <ul class="list-unstyled mb-0 font-monospace small">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Criado em:</span>
                            <span>{{ $valor->created_at->format('d/m/Y') }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Última edição:</span>
                            <span>{{ $valor->updated_at->format('d/m/Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
