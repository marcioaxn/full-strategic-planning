<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mapa Estratégico</title>
    <style>
        @page { margin: 0.5cm; size: a4 landscape; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #333; line-height: 1.2; margin: 0; padding: 0; }
        
        /* Cores */
        .text-primary { color: #1B408E; }
        
        /* Cabeçalho Compacto */
        .header { border-bottom: 2px solid #1B408E; padding-bottom: 5px; margin-bottom: 10px; display: table; width: 100%; }
        .header-left { display: table-cell; width: 60%; vertical-align: bottom; }
        .header-right { display: table-cell; width: 40%; text-align: right; vertical-align: bottom; }
        .doc-title { font-size: 18px; font-weight: bold; color: #1B408E; text-transform: uppercase; margin: 0; }
        .doc-subtitle { font-size: 11px; color: #555; margin: 2px 0 0 0; }
        
        /* Mapa Estratégico Compacto */
        .map-container { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        .persp-row { page-break-inside: avoid; }
        
        /* Header da Perspectiva */
        .persp-header-cell; 
            width: 30px; 
            text-align: center; 
            vertical-align: middle; 
            color: white; 
            font-weight: bold; 
            font-size: 9px; 
            text-transform: uppercase; 
            border-radius: 4px 0 0 4px;
        }
        .persp-header-text {
            writing-mode: vertical-rl; 
            transform: rotate(180deg); 
            white-space: nowrap;
            padding: 5px 0;
            margin: 0 auto;
        }

        .persp-body-cell; 
            background: #fdfdfd; 
            border: 1px solid #ddd; 
            border-left: none; 
            border-radius: 0 4px 4px 0; 
            padding: 5px; 
            vertical-align: middle;
        }

        /* Card de Objetivo Ultra Compacto */
        .obj-card {
            display: inline-block;
            width: 23%; 
            vertical-align: top;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px;
            margin: 3px;
            text-align: left;
            box-shadow: 1px 1px 3px rgba(0,0,0,0.05);
            position: relative;
            min-height: 40px;
        }
        .obj-title { font-weight: bold; font-size: 8px; color: #333; margin-bottom: 3px; display: block; line-height: 1.1; }
        .obj-status { position: absolute; top: 0; right: 0; width: 8px; height: 8px; border-radius: 0 4px 0 4px; }
        .obj-meta { font-size: 7px; color: #777; margin-top: 2px; }

        /* Rodapé */
        footer { position: fixed; bottom: -15px; left: 0; right: 0; height: 15px; text-align: center; font-size: 7px; color: #999; border-top: 1px solid #eee; padding-top: 2px; }
    </style>
</head>
<body>

    <footer>
        {{ $organizacao->nom_organizacao }} | Gerado em {{ now()->format('d/m/Y H:i') }}
    </footer>

    <!-- MAPA ESTRATÉGICO (Página 2) -->
    <div class="chapter-title" style="margin-top: 0;">1. Mapa Estratégico</div>
    
    <!-- Identidade Separada (Cards - Altura Igual e Cantos Arredondados) -->
    <table style="width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 15px; table-layout: fixed; border: none;" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <!-- Card Missão -->
            <td style="width: 49%; background: #ffffff; border-left: 4px solid #1B408E; border-top: 1px solid #eeeeee; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; border-radius: 6px; padding: 15px; vertical-align: top;">
                <strong style="color: #1B408E; margin-bottom: 12px; display: block; font-size: 9px;">MISSÃO</strong>
                <div style="font-style: italic; font-size: 10px; line-height: 1.4;">{{ $identidade->dsc_missao ?? 'Não definida' }}</div>
            </td>
            
            <!-- Espaçador Central (Invisível) -->
            <td style="width: 2%; border: none; background: none;"></td>
            
            <!-- Card Visão -->
            <td style="width: 49%; background: #ffffff; border-left: 4px solid #1B408E; border-top: 1px solid #eeeeee; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; border-radius: 6px; padding: 15px; vertical-align: top;">
                <strong style="color: #1B408E; margin-bottom: 12px; display: block; font-size: 9px;">VISÃO</strong>
                <div style="font-style: italic; font-size: 10px; line-height: 1.4;">{{ $identidade->dsc_visao ?? 'Não definida' }}</div>
            </td>
        </tr>
    </table>

    <!-- Valores (Card Dedicado - Arredondado) -->
    <table style="width: 100%; margin-bottom: 15px; border-collapse: separate; border-spacing: 0; border: none;" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background: #ffffff; border-left: 4px solid #1B408E; border-top: 1px solid #eeeeee; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; border-radius: 6px; padding: 15px; vertical-align: top; text-align: center;">
                <strong style="color: #1B408E; margin-bottom: 25px; padding-bottom: 5px; display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">VALORES INSTITUCIONAIS</strong>
                <div style="text-align: center; margin-top: 10px;">
                    @forelse($valores as $valor)
                        <span class="badge" style="background: #f1f4f9; color: #1B408E; margin: 4px 8px; font-weight: bold; border: 1px solid #1B408E; padding: 5px 12px; font-size: 9px;">
                            {{ $valor->nom_valor }}
                        </span>
                    @empty
                        <span style="font-style: italic; color: #999; font-size: 8px;">Valores não cadastrados.</span>
                    @endforelse
                </div>
            </td>
        </tr>
    </table>

    <!-- Temas Norteadores (Card Dedicado - Arredondado) -->
    @if($temasNorteadores->isNotEmpty())
        <table style="width: 100%; margin-bottom: 25px; border-collapse: separate; border-spacing: 0; border: none;" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td style="background: #ffffff; border-left: 4px solid #1B408E; border-top: 1px solid #eeeeee; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; border-radius: 6px; padding: 15px; vertical-align: top; text-align: center;">
                    <strong style="color: #1B408E; margin-bottom: 25px; padding-bottom: 5px; display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">TEMAS NORTEADORES</strong>
                    <div style="line-height: 2.2; text-align: center; margin-top: 10px;">
                        @foreach($temasNorteadores as $objEst)
                            <span class="badge" style="background: #fff; color: #1B408E; margin: 4px 8px; font-weight: bold; border: 1px solid #1B408E; padding: 5px 12px; font-size: 9px;">
                                {{ $objEst->nom_tema_norteador }}
                            </span>
                        @endforeach
                    </div>
                </td>
            </tr>
        </table>
    @endif

    <!-- Swimlanes do Mapa -->
    <div class="map-container">
        @php
            // Mapeamento de Cores por Nível (Cientificamente extraído do MapaEstrategico.php)
            $coresNivel = [
                1 => '#6c757d', // Secondary/Slate
                2 => '#198754', // Success
                3 => '#0dcaf0', // Info
                4 => '#ffc107', // Warning
                5 => '#0d6efd', // Primary
            ];
        @endphp

        @foreach($perspectivas as $persp)
            @php
                $corPersp = $coresNivel[$persp->num_nivel_hierarquico_apresentacao] ?? '#1B408E';
                // Ajuste de contraste para texto (Preto para Warning/Amarelo, Branco para resto)
                $textoCor = ($persp->num_nivel_hierarquico_apresentacao == 4) ? '#000' : '#fff';
            @endphp
            <div class="persp-row" style="border-color: {{ $corPersp }};">
                <div class="persp-header" style="background: {{ $corPersp }}; color: {{ $textoCor }};">{{ $persp->dsc_perspectiva }}</div>
                <div class="persp-body">
                    @forelse($persp->objetivos as $obj)
                        @php
                            $atingimento = $obj->atingimento_calculado ?? 0;
                            $cor = $getCorSatisfacao($atingimento);
                        @endphp
                        <div class="obj-card" style="border-left: 3px solid {{ $cor }};">
                            <div class="obj-status" style="background: {{ $cor }};"></div>
                            <span class="obj-title">{{ $obj->nom_objetivo }}</span>
                            <div style="font-size: 8px; color: #666; margin-top: 4px;">
                                Atingimento: <strong>{{ number_format($atingimento, 2, ',', '.') }}%</strong>
                            </div>
                        </div>
                    @empty
                        <div style="font-style: italic; color: #999; padding: 10px;">Sem objetivos vinculados.</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>