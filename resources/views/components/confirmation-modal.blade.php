@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="p-4">
        {{-- Título --}}
        <div class="mb-4">
            {{ $title }}
        </div>

        {{-- Conteúdo --}}
        <div class="mb-4">
            {{ $content }}
        </div>
    </div>

    <div class="modal-footer bg-light border-0 p-4 d-flex justify-content-end gap-2">
        {{ $footer }}
    </div>
</x-modal>
