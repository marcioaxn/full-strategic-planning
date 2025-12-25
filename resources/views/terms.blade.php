<x-guest-layout>
    <section class="w-100 py-5 bg-body-tertiary">
        <div class="container d-flex flex-column align-items-center">
            <div class="mb-4">
                <x-authentication-card-logo />
            </div>

            <div class="card shadow-sm border-0 w-100" style="max-width: 720px;">
                <div class="card-body p-4 p-lg-5 fs-6 lh-base text-body">
                    {!! $terms !!}
                </div>
            </div>
        </div>
    </section>
</x-guest-layout>
