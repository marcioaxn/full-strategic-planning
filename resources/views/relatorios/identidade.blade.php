<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Identidade Estratégica - {{ $organizacao->sgl_organizacao }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #1B408E; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #1B408E; }
        .title { font-size: 18px; color: #666; margin-top: 5px; }
        .section { margin-bottom: 40px; }
        .section-title { font-size: 20px; font-weight: bold; color: #1B408E; border-left: 5px solid #1B408E; padding-left: 10px; margin-bottom: 15px; background: #f8f9fa; padding-top: 5px; padding-bottom: 5px; }
        .content { font-size: 16px; text-align: justify; padding: 0 15px; font-style: italic; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .values-list { list-style: none; padding: 0; }
        .value-item { margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .value-name { font-weight: bold; color: #d97706; display: block; }
        .value-desc { font-size: 14px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">SEAE - Sistema de Planejamento Estratégico</div>
        <div class="title">Identidade Estratégica Institucional</div>
        <div style="font-size: 14px; color: #333; font-weight: bold; margin-top: 10px;">{{ $organizacao->nom_organizacao }} ({{ $organizacao->sgl_organizacao }})</div>
    </div>

    <div class="section">
        <div class="section-title">Missão</div>
        <div class="content">
            {{ $identidade->dsc_missao ?: 'Missão não definida.' }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Visão</div>
        <div class="content">
            {{ $identidade->dsc_visao ?: 'Visão não definida.' }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Valores Organizacionais</div>
        <ul class="values-list">
            @forelse($valores as $valor)
                <li class="value-item">
                    <span class="value-name">{{ $valor->nom_valor }}</span>
                    <span class="value-desc">{{ $valor->dsc_valor }}</span>
                </li>
            @empty
                <li style="color: #999;">Nenhum valor cadastrado.</li>
            @endforelse
        </ul>
    </div>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} | SEAE - Planejamento e Estratégia
    </div>
</body>
</html>
