<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mapa Estratégico</title>
    <style>
        @page { margin: 1cm; size: a4 landscape; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.3; margin: 0; padding: 0; }
        
        /* Cores */
        .text-primary { color: #1B408E; }
        .bg-primary { background-color: #1B408E; color: white; }
        .bg-light { background-color: #f8f9fa; }
        
        /* Capa */
        .cover-page { 
            position: relative; 
            height: 950px;
            text-align: center; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            page-break-after: always;
        }
        .cover-title { font-size: 36px; font-weight: bold; color: #1B408E; margin-bottom: 5px; text-transform: uppercase; }
        .cover-subtitle { font-size: 20px; color: #555; margin-top: 5px; margin-bottom: 60px; }
        .cover-meta { font-size: 11px; color: #777; margin-top: 100px; border-top: 1px solid #eee; padding-top: 20px; display: inline-block; width: 60%; }

        /* Mapa Estratégico */
        .map-header-row { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .map-card { 
            border: 1px dashed #ccc; 
            padding: 10px; 
            background: #ffffff; 
            font-size: 10px; 
            text-align: center; 
            border-radius: 6px; 
            height: auto;
            display: block;
            box-sizing: border-box;
        }
        
        .map-container { margin-top: 20px; }
        .persp-row { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; page-break-inside: avoid; }
        .persp-header { 
            background: #1B408E; color: white; 
            padding: 8px; text-align: center; 
            font-weight: bold; text-transform: uppercase; font-size: 11px; 
            letter-spacing: 1px;
        }
        .persp-body { padding: 10px; background: #fdfdfd; text-align: center; }
        
        .obj-card {
            display: inline-block;
            width: 28%;
            vertical-align: top;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 8px;
            margin: 5px;
            text-align: left;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.05);
            position: relative;
            min-height: 60px;
        }
        .obj-title { font-weight: bold; font-size: 9px; color: #333; margin-bottom: 5px; display: block; }
        .obj-status { position: absolute; top: 0; right: 0; width: 10px; height: 10px; border-radius: 0 6px 0 6px; }

        /* Tabelas */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: auto; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #e0e0e0; padding: 6px 8px; text-align: left; vertical-align: middle; }
        th { background: #f1f5f9; color: #1B408E; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        
        /* Badges e Status */
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 8px; font-weight: bold; display: inline-block; text-align: center; white-space: nowrap; }
        
        /* Seções */
        .chapter-title { 
            font-size: 18px; color: #1B408E; 
            border-bottom: 2px solid #1B408E; 
            padding-bottom: 8px; margin: 25px 0 15px 0; 
            text-transform: uppercase; font-weight: bold;
        }
        .sub-title { 
            background: #eef2f7; color: #2c3e50; 
            padding: 5px 10px; font-weight: bold; font-size: 10px; 
            border-left: 4px solid #1B408E; margin: 15px 0 10px 0; 
        }

        /* Legenda Semáforo */
        .legend-container { margin-bottom: 15px; background: #fff; padding: 10px; border: 1px solid #eee; border-radius: 5px; }
        .legend-title { font-weight: bold; font-size: 9px; margin-bottom: 5px; color: #666; text-transform: uppercase; }
        .legend-item { display: inline-block; margin-right: 15px; font-size: 8px; }
        .legend-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; vertical-align: middle; }

        /* Entregas */
        .deliverables-table { width: 95%; margin: 5px auto 10px auto; border: 1px solid #eee; }
        .deliverables-table th { background: #fff; color: #666; font-size: 8px; border-bottom: 1px solid #eee; }
        .deliverables-table td { font-size: 8px; border-bottom: 1px solid #eee; background: #fff; }

        /* Rodapé de Página */
        footer { position: fixed; bottom: -30px; left: 0; right: 0; height: 30px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }

        /* Utilitários */
        .page-break { page-break-after: always; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
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

    <!-- Objetivos Estratégicos (Card Dedicado - Arredondado) -->
    @if($objetivosEstrategicos->isNotEmpty())
        <table style="width: 100%; margin-bottom: 25px; border-collapse: separate; border-spacing: 0; border: none;" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td style="background: #ffffff; border-left: 4px solid #1B408E; border-top: 1px solid #eeeeee; border-right: 1px solid #eeeeee; border-bottom: 1px solid #eeeeee; border-radius: 6px; padding: 15px; vertical-align: top; text-align: center;">
                    <strong style="color: #1B408E; margin-bottom: 25px; padding-bottom: 5px; display: block; font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">OBJETIVOS ESTRATÉGICOS</strong>
                    <div style="line-height: 2.2; text-align: center; margin-top: 10px;">
                        @foreach($objetivosEstrategicos as $objEst)
                            <span class="badge" style="background: #fff; color: #1B408E; margin: 4px 8px; font-weight: bold; border: 1px solid #1B408E; padding: 5px 12px; font-size: 9px;">
                                {{ $objEst->nom_objetivo_estrategico }}
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
                            $atingimento = $obj->calcularAtingimentoConsolidado($filtros['ano'], $filtros['mesLimite']);
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