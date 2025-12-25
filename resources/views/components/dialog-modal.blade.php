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

    <div class="modal-footer-modern">
        {{ $footer }}
    </div>
</x-modal>

<style>
    .modal-body-modern {
        padding: 2rem;
    }

    .modal-title-container h5,
    .modal-title-container h6 {
        color: var(--bs-body-color);
        margin-bottom: 0;
    }

    [data-bs-theme="dark"] .modal-title-container h5,
    [data-bs-theme="dark"] .modal-title-container h6 {
        color: rgba(255, 255, 255, 0.95);
    }

    .modal-content-container {
        color: var(--bs-body-color);
    }

    [data-bs-theme="dark"] .modal-content-container {
        color: rgba(255, 255, 255, 0.85);
    }

    .modal-footer-modern {
        background: rgba(var(--bs-secondary-rgb), 0.03);
        border-top: 1px solid var(--bs-border-color);
        padding: 1.5rem 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    [data-bs-theme="dark"] .modal-footer-modern {
        background: rgba(255, 255, 255, 0.02);
        border-color: rgba(255, 255, 255, 0.05);
    }

    @media (min-width: 576px) {
        .modal-footer-modern {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }
</style>
