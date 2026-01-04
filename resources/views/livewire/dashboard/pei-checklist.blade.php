<div class="card card-modern border-0 shadow-sm educational-card-gradient mb-4 overflow-hidden" 
     x-data="{ expanded: {{ $guidance['progress'] < 100 ? 'true' : 'false' }} }"
     style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
    
    <div class="card-body p-0">
        {{-- Header Compacto --}}
        <div class="p-3 px-4 d-flex align-items-center justify-content-between cursor-pointer" 
             @click="expanded = !expanded"
             style="background: rgba(255,255,255,0.05);">
            
            <div class="d-flex align-items-center gap-3">
                <div class="mentor-icon-container">
                    <i class="bi bi-{{ $aiEnabled ? 'robot' : 'speedometer2' }} fs-5 text-white"></i>
                    @if($guidance['progress'] < 100)
                        <span class="pulse-dot"></span>
                    @endif
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-white">
                        {{ $aiEnabled ? __('Mentor Estratégico') : __('Monitor Estratégico') }}
                        <span class="badge bg-white text-primary ms-2 small fw-bold shadow-sm" style="font-size: 0.7rem; vertical-align: middle;">
                            {{ $guidance['progress'] }}%
                        </span>
                    </h6>
                    <p class="mb-0 text-white-50 small d-none d-md-block" style="font-size: 0.75rem;">
                        {{ Str::limit($guidance['message'], 80) }}
                    </p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                @if(!$guidance['status'] === 'success' || $guidance['progress'] < 100)
                    <a href="{{ route($guidance['action_route']) }}" 
                       wire:navigate
                       @click.stop 
                       class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3 d-none d-sm-inline-flex align-items-center shadow-sm"
                       style="font-size: 0.75rem;">
                        {{ __($guidance['action_label']) }}
                    </a>
                @endif
                
                <button class="btn btn-link text-white p-0 shadow-none">
                    <i class="bi bi-chevron-down transition-all" :class="expanded ? 'rotate-180' : ''"></i>
                </button>
            </div>
        </div>

        {{-- Conteúdo Expandido (Timeline Moderna) --}}
        <div x-show="expanded" 
             x-collapse
             x-cloak
             class="p-4 bg-body text-body">
            
            <div class="timeline-steps">
                @foreach($guidance['phases'] as $key => $phase)
                    @php
                        $isCompleted = $phase['status'] === 'completed';
                        $isActive = $phase['status'] === 'active' || $phase['status'] === 'in_progress';
                        $isLocked = $phase['status'] === 'locked';
                    @endphp

                    <div class="timeline-step @if($isCompleted) completed @elseif($isActive) active @else locked @endif">
                        <div class="step-icon-wrapper">
                            <div class="step-icon shadow-sm">
                                <i class="bi bi-{{ $phase['icon'] }}"></i>
                            </div>
                            @if(!$loop->last)
                                <div class="step-connector"></div>
                            @endif
                        </div>
                        <div class="step-content mt-2">
                            <span class="d-block fw-bold small-title">{{ $phase['name'] }}</span>
                            @if($isActive)
                                <span class="badge bg-primary-subtle text-primary border-0 rounded-pill mt-1" style="font-size: 0.6rem;">
                                    {{ __('Foco Atual') }}
                                </span>
                            @elseif($isCompleted)
                                <i class="bi bi-check-circle-all text-success" style="font-size: 0.8rem;"></i>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 p-3 rounded-3 border bg-body-tertiary d-flex align-items-center gap-2">
                <i class="bi bi-lightbulb text-primary"></i>
                <span class="small text-body-secondary"><strong>Dica:</strong> {{ $guidance['message'] }}</span>
            </div>
        </div>
    </div>

    <style>
        .rotate-180 { transform: rotate(180deg); }
        .transition-all { transition: all 0.3s ease; }
        .mentor-icon-container {
            width: 38px; height: 38px; 
            background: rgba(255,255,255,0.15); 
            border-radius: 10px; 
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .pulse-dot {
            position: absolute; top: -2px; right: -2px;
            width: 10px; height: 10px; background: #4ade80;
            border-radius: 50%; border: 2px solid #667eea;
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(74, 222, 128, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
        }

        /* Timeline Moderna */
        .timeline-steps {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
        }
        .timeline-step {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            min-width: 0;
        }
        .step-icon-wrapper {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .step-icon {
            width: 42px; height: 42px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            z-index: 2;
            transition: all 0.3s ease;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-secondary-color);
        }
        .step-connector {
            position: absolute;
            height: 2px;
            background: var(--bs-border-color);
            width: 100%;
            left: 50%;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
        }
        
        /* Modificadores de Estado */
        .timeline-step.completed .step-icon {
            background: #059669; color: white; border-color: #059669;
        }
        .timeline-step.completed .step-connector {
            background: #059669;
        }
        .timeline-step.active .step-icon {
            background: var(--bs-body-bg); color: var(--bs-primary);
            border: 3px solid var(--bs-primary);
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(var(--bs-primary-rgb), 0.3) !important;
        }
        .timeline-step.active .small-title { color: var(--bs-primary); font-weight: 800 !important; }
        .timeline-step.locked { opacity: 0.5; }

        .small-title {
            font-size: 0.7rem;
            margin-top: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            color: var(--bs-body-color);
        }

        @media (max-width: 768px) {
            .timeline-steps { overflow-x: auto; padding-bottom: 15px; justify-content: flex-start; }
            .timeline-step { flex: 0 0 100px; }
        }
    </style>
</div>