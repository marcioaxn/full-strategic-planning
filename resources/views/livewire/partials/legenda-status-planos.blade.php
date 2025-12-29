@php
    $legendaPlanos = \App\Models\PEI\PlanoDeAcao::getStatusLegend();
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2 px-4 bg-white rounded-3">
        <div class="d-flex align-items-center flex-wrap gap-4">
            <span class="small fw-bold text-muted text-uppercase me-2">
                <i class="bi bi-info-circle me-1"></i>Legenda Status (Planos):
            </span>
            @foreach($legendaPlanos as $item)
                <div class="d-flex align-items-center">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $item['color'] }}; display: inline-block; margin-right: 6px; box-shadow: 0 0 2px rgba(0,0,0,0.2);"></div>
                    <small class="text-muted" style="font-size: 0.75rem;">{{ $item['label'] }}</small>
                </div>
            @endforeach
        </div>
    </div>
</div>
