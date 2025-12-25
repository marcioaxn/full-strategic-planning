@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="modal-body-modern">
        <div class="modal-title-container">
            {{ $title }}
        </div>

        <div class="modal-content-container">
            {{ $content }}
        </div>
    </div>

    <div class="modal-footer-modern modal-footer-actions">
        {{ $footer }}
    </div>
</x-modal>

<style>
    .modal-footer-actions {
        justify-content: flex-end !important;
    }

    .modal-footer-actions > * {
        margin: 0;
    }
</style>
