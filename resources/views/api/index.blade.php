<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 text-body-emphasis mb-0">
            {{ __('API Tokens') }}
        </h1>
    </x-slot>

    <section class="py-5 bg-body-tertiary">
        <div class="container">
            @livewire('api.api-token-manager')
        </div>
    </section>
</x-app-layout>
