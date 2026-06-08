<x-mail::layout>
{{-- Cabeçalho --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
Sistema PEI
</x-mail::header>
</x-slot:header>

{{-- Corpo --}}
{!! $slot !!}

{{-- Subcópia (link alternativo) --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Rodapé institucional (em Português) --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} Sistema de Planejamento Estratégico Integrado (PEI) — Ministério da Integração e do Desenvolvimento Regional (MIDR). Todos os direitos reservados.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
