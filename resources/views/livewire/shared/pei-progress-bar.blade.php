<div class="pei-progress-sidebar sidebar-text px-2 pb-2">
    @if(!empty($steps))
        <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="text-muted" style="font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;">
                Ciclo PEI
            </span>
            <span class="fw-bold" style="font-size: 0.72rem; color: {{ $progresso >= 100 ? '#198754' : '#2e6da4' }};">
                {{ $progresso }}%
            </span>
        </div>
        <div class="progress mb-2" style="height: 5px; border-radius: 3px;">
            <div class="progress-bar {{ $progresso >= 100 ? 'bg-success' : 'bg-primary' }}"
                 role="progressbar"
                 style="width: {{ $progresso }}%; transition: width 0.4s ease;"
                 aria-valuenow="{{ $progresso }}"
                 aria-valuemin="0"
                 aria-valuemax="100">
            </div>
        </div>
        <div class="d-flex justify-content-between gap-1">
            @php
                $stepIcons = [
                    'inaugurar'    => ['icon' => 'flag-fill',  'tip' => 'Inaugurar'],
                    'identidade'   => ['icon' => 'gem',        'tip' => 'Identidade'],
                    'perspectivas' => ['icon' => 'layers',      'tip' => 'Perspectivas'],
                    'objetivos'    => ['icon' => 'bullseye',    'tip' => 'Objetivos'],
                    'graus'        => ['icon' => 'palette',     'tip' => 'Graus'],
                    'indicadores'  => ['icon' => 'graph-up',    'tip' => 'Indicadores'],
                    'planos'       => ['icon' => 'list-check',  'tip' => 'Planos'],
                ];
            @endphp
            @foreach($stepIcons as $key => $meta)
                <span data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="{{ $meta['tip'] }}: {{ ($steps[$key] ?? false) ? 'Concluído' : 'Pendente' }}"
                      style="font-size: 0.65rem; cursor: default; color: {{ ($steps[$key] ?? false) ? '#198754' : '#adb5bd' }};">
                    <i class="bi bi-{{ $meta['icon'] }}"></i>
                </span>
            @endforeach
        </div>
    @else
        <span class="text-muted" style="font-size: 0.68rem;">Selecione organização e PEI</span>
    @endif
</div>
