@props(['page' => 1, 'label' => null])
<a href="{{ route('documentos.gppei') }}#page={{ $page }}"
   target="_blank"
   rel="noopener"
   class="gppei-ref-link d-inline-flex align-items-center gap-1 text-decoration-none"
   data-bs-toggle="tooltip"
   title="Abrir referência metodológica no Guia GPPEI — página {{ $page }}">
    <i class="bi bi-book-half" style="font-size: 0.72rem; color: #2e6da4;"></i>
    <span class="text-muted" style="font-size: 0.72rem; font-weight: 600; letter-spacing: 0.02em;">
        GPPEI p.{{ $page }}{{ $label ? ' · '.$label : '' }}
    </span>
</a>
