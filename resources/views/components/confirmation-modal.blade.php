@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="modal-body-modern">
        {{-- Título --}}
        <div class="modal-title-container">
            {{ $title }}
        </div>

        {{-- Conteúdo --}}
        <div class="modal-content-container mt-3">
            {{ $content }}
        </div>
    </div>

    <div class="modal-footer-modern">
        {{ $footer }}
    </div>
</x-modal>
