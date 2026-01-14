@props(['title', 'placement' => 'top', 'html' => false])

<span 
    data-bs-toggle="tooltip" 
    data-bs-placement="{{ $placement }}" 
    @if($html) data-bs-html="true" @endif
    title="{{ $title }}"
    class="cursor-help ms-1 text-primary opacity-75"
>
    <i class="bi bi-question-circle"></i>
</span>

@once
<script>
    window.initTooltips = function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.forEach(function (el) {
            if (el.classList.contains('tooltip-initialized')) return;
            
            el.classList.add('tooltip-initialized');
            new bootstrap.Tooltip(el, {
                container: 'body',
                boundary: 'clippingParents',
                trigger: 'hover'
            });
        });
    }

    window.hideAllTooltips = function() {
        // Encontra todos os elementos de tooltip renderizados no body e remove-os
        const tooltips = document.querySelectorAll('.tooltip');
        tooltips.forEach(t => {
            t.classList.remove('show');
            setTimeout(() => t.remove(), 150);
        });
        
        // Esconde via instância do Bootstrap se disponível
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.forEach(function (el) {
            var tooltip = bootstrap.Tooltip.getInstance(el);
            if (tooltip) tooltip.hide();
        });
    };

    document.addEventListener('DOMContentLoaded', window.initTooltips);
    document.addEventListener('livewire:navigated', window.initTooltips);
    
    // Antes de navegar para outra página via wire:navigate
    document.addEventListener('livewire:navigate', window.hideAllTooltips);
    
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updating', (el, component) => {
            window.hideAllTooltips();
        });
        
        Livewire.hook('morph.updated', (el, component) => {
            window.initTooltips();
        });
    });
</script>
<style>
    .cursor-help { cursor: help; }
    .tooltip { pointer-events: none !important; z-index: 1100 !important; }
</style>
@endonce
