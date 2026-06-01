@props([
    'num'       => null,    // número do ODS (1..17) — forma leve, sem consulta ao banco
    'ods'       => null,    // OU instância do model App\Models\Agenda2030\ODS
    'size'      => 'md',    // sm | md | lg
    'showLabel' => false,   // exibir o nome abreviado ao lado do ícone
    'link'      => false,   // se true, vira link para o Painel da Agenda 2030
])

@php
    // Mapa oficial ONU (cor + nome abreviado) — embutido para performance e
    // independência de banco. Espelha o OdsSeeder.
    $mapaOds = [
        1  => ['Sem Pobreza',              '#e5243b'],
        2  => ['Fome Zero',                '#dda63a'],
        3  => ['Saúde e Bem-Estar',        '#4c9f38'],
        4  => ['Educação de Qualidade',    '#c5192d'],
        5  => ['Igualdade de Gênero',      '#ff3a21'],
        6  => ['Água e Saneamento',        '#26bde2'],
        7  => ['Energia Limpa',            '#fcc30b'],
        8  => ['Trabalho e Crescimento',   '#a21942'],
        9  => ['Indústria e Inovação',     '#fd6925'],
        10 => ['Redução das Desigualdades','#dd1367'],
        11 => ['Cidades Sustentáveis',     '#fd9d24'],
        12 => ['Consumo Responsável',      '#bf8b2e'],
        13 => ['Ação Climática',           '#3f7e44'],
        14 => ['Vida na Água',             '#0a97d9'],
        15 => ['Vida Terrestre',           '#56c02b'],
        16 => ['Paz e Justiça',            '#00689d'],
        17 => ['Parcerias e Meios',        '#19486a'],
        18 => ['Igualdade Étnico-Racial',  '#6c321a'],
    ];

    // Resolve o número do ODS a partir de $ods (model) ou $num
    $numero = $ods ? (int) $ods->num_ods : (int) $num;

    if ($numero < 1 || $numero > 18) {
        $numero = null;
    }

    $nomeAbrev = $numero ? ($mapaOds[$numero][0] ?? ('ODS ' . $numero)) : '';
    $cor       = $numero ? ($mapaOds[$numero][1] ?? '#475569') : '#475569';
    $numFmt    = $numero ? str_pad((string) $numero, 2, '0', STR_PAD_LEFT) : '00';

    // Caminho do ícone oficial IPEA (fallback automático se não existir)
    $arquivoIcone = 'img/ods/ods-' . $numFmt . '.png';
    $temIcone     = $numero && file_exists(public_path($arquivoIcone));

    // Dimensões por tamanho
    $dim = match ($size) {
        'sm' => 28,
        'lg' => 64,
        default => 40,
    };
    $fonteNum = match ($size) {
        'sm' => '.7rem',
        'lg' => '1.4rem',
        default => '.95rem',
    };

    $titulo = $numero ? ('ODS ' . $numero . ' — ' . $nomeAbrev) : 'ODS não definido';
    $tag    = $link ? 'a' : 'span';
@endphp

<{{ $tag }}
    @if($link) href="{{ route('agenda2030.index') }}" wire:navigate @endif
    {{ $attributes->merge(['class' => 'ods-badge d-inline-flex align-items-center text-decoration-none' . ($showLabel ? ' gap-2' : '')]) }}
    data-bs-toggle="tooltip"
    title="{{ $titulo }}"
    style="vertical-align:middle;"
>
    @if($temIcone)
        <img src="{{ asset($arquivoIcone) }}"
             alt="{{ $titulo }}"
             width="{{ $dim }}" height="{{ $dim }}"
             style="border-radius:{{ $size === 'sm' ? 5 : 8 }}px;display:block;flex-shrink:0;object-fit:cover;">
    @else
        {{-- Fallback: quadrado colorido com o número, no padrão visual ODS --}}
        <span style="width:{{ $dim }}px;height:{{ $dim }}px;background:{{ $cor }};
                     border-radius:{{ $size === 'sm' ? 5 : 8 }}px;color:#fff;
                     font-weight:800;font-size:{{ $fonteNum }};line-height:1;
                     display:flex;align-items:center;justify-content:center;flex-shrink:0;
                     letter-spacing:-.02em;">
            {{ $numero ?? '–' }}
        </span>
    @endif

    @if($showLabel && $numero)
        <span class="ods-badge-label" style="font-size:{{ $size === 'lg' ? '.9rem' : '.78rem' }};font-weight:600;color:{{ $cor }};line-height:1.15;">
            <span style="font-size:.62rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;opacity:.7;display:block;">ODS {{ $numero }}</span>
            {{ $nomeAbrev }}
        </span>
    @endif
</{{ $tag }}>
