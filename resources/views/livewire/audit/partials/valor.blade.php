{{-- Um valor auditado já traduzido por AuditoriaLegivel::valor(). Parâmetros: $v, $tom (danger|success|null). --}}
@php $tom ??= null; @endphp
<div class="h-100 rounded px-3 py-2 text-break {{ $tom ? 'bg-'.$tom.'-subtle border border-'.$tom.'-subtle' : 'bg-body-tertiary border' }} {{ $v['vazio'] ? 'fst-italic text-body-secondary' : '' }}"
     style="white-space: pre-wrap;">@if($v['cor'])<span class="d-inline-block rounded-circle border me-1 align-middle" style="width: 12px; height: 12px; background: {{ $v['cor'] }};" aria-hidden="true"></span>@endif{{ $v['texto'] }}</div>
