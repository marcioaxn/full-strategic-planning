# Recria do zero o banco de demonstração do manual (pei_manual) e o popula.
# Pré-requisito: $env:SENHA_DEMO definido na sessão (senha dos usuários de demonstração).
# Uso: .\documentacao\manual\gerador\recriar-ambiente-demonstracao.ps1   (na raiz do projeto)

$ErrorActionPreference = 'Stop'
if (-not $env:SENHA_DEMO) { throw 'Defina $env:SENHA_DEMO antes de rodar.' }

$raiz = Resolve-Path (Join-Path $PSScriptRoot '..\..\..')
Push-Location $raiz
try {
    $env:DB_PORT = '5434'

    # 1. Banco novo, schemas e pgcrypto (conecta pelo banco do .env só para o CREATE/DROP DATABASE)
    Remove-Item Env:DB_DATABASE -ErrorAction SilentlyContinue
    php documentacao/manual/gerador/criar-banco-demonstracao.php --recriar
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao criar o banco.' }

    # 2. Daqui em diante tudo roda contra pei_manual
    $env:DB_DATABASE = 'pei_manual'
    php artisan migrate --force --path=database/migrations --path=database/migrations/ActionPlan --path=database/migrations/Organization --path=database/migrations/PerformanceIndicators --path=database/migrations/RiskManagement --path=database/migrations/StrategicPlanning
    if ($LASTEXITCODE -ne 0) { throw 'Falha no migrate.' }

    # 3. Seeders de referência
    foreach ($s in 'PerfilAcessoSeeder', 'TipoExecucaoSeeder', 'OrganizacaoRaizSeeder') {
        php artisan db:seed --class=$s --force
        if ($LASTEXITCODE -ne 0) { throw "Falha no seeder $s." }
    }

    # 4. Dados de demonstração e conferência
    php documentacao/manual/gerador/popular-demonstracao.php
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao popular.' }
    php documentacao/manual/gerador/contar-demonstracao.php
}
finally {
    Remove-Item Env:DB_DATABASE -ErrorAction SilentlyContinue
    Pop-Location
}
