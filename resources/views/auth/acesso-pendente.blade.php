<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="h5 fw-bold mb-3">Acesso aguardando liberação</h1>

        <p class="small text-muted mb-3">
            Sua conta foi criada, mas ainda não tem um perfil de acesso. Um administrador do
            {{ config('app.name') }} precisa vincular você a uma unidade e definir o seu perfil
            (por exemplo, Gestor de uma iniciativa ou Consulta).
        </p>
        <p class="small text-muted mb-4">
            Assim que isso for feito, entre de novo e as telas da sua unidade aparecerão.
        </p>

        @if(session()->has('impersonator_id'))
            {{-- Super Admin que assumiu a identidade desta conta precisa voltar à própria. --}}
            <form method="POST" action="{{ route('impersonate.stop') }}" class="mb-2">
                @csrf
                <x-button>Encerrar impersonação e voltar à minha conta</x-button>
            </form>
        @else
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button>Sair</x-button>
            </form>
        @endif
    </x-authentication-card>
</x-guest-layout>
