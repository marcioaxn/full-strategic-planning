@props(['page' => 1, 'label' => null])
<a href="{{ route('documentos.projetos.pdf') }}#page={{ $page }}"
   target="_blank"
   rel="noopener"
   class="gppei-ref-link d-inline-flex align-items-center gap-1 text-decoration-none"
   data-bs-toggle="tooltip"
   title="Abrir referência no Guia Prático de Projetos — página {{ $page }}">
    <i class="bi bi-journal-bookmark-fill" style="font-size: 0.72rem; color: #e07b39;"></i>
    <span class="text-muted" style="font-size: 0.72rem; font-weight: 600; letter-spacing: 0.02em;">
        Guia Projetos p.{{ $page }}{{ $label ? ' · '.$label : '' }}
    </span>
</a>
