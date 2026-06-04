# Ralph Wiggum — Loop autônomo iterativo para o projeto PEI
# Uso: .\ralph-start.ps1 -Prompt ux-critica -MaxIter 20
# Templates disponíveis: feature, bugfix, ux-critica, novo-modulo

param(
    [string]$Prompt = "feature",
    [int]$MaxIter = 30
)

$env:RALPH_ACTIVE = "1"
$env:RALPH_MAX_ITERATIONS = $MaxIter

$promptFile = ".claude\ralph\prompts\$Prompt.md"
if (-not (Test-Path $promptFile)) {
    Write-Host "[ralph] Template '$Prompt' nao encontrado em $promptFile" -ForegroundColor Red
    Write-Host "[ralph] Templates disponíveis: feature, bugfix, ux-critica, novo-modulo" -ForegroundColor Yellow
    exit 1
}

$logDir = ".claude\ralph\logs"
if (-not (Test-Path $logDir)) { New-Item -ItemType Directory -Path $logDir -Force | Out-Null }

$logFile = "$logDir\ralph-$(Get-Date -Format 'yyyyMMdd-HHmmss').log"
$promptContent = Get-Content $promptFile -Raw
$iteration = 0

Write-Host "[ralph] Iniciando: template=$Prompt | max=$MaxIter iteracoes" -ForegroundColor Cyan
Write-Host "[ralph] Log: $logFile" -ForegroundColor Gray

while ($iteration -lt $MaxIter) {
    $iteration++
    $timestamp = Get-Date -Format 'HH:mm:ss'
    Write-Host "[ralph] Iteracao $iteration/$MaxIter - $timestamp" -ForegroundColor Yellow
    Add-Content -Path $logFile -Value "[ralph] Iteracao $iteration/$MaxIter - $timestamp"

    $result = claude --print $promptContent 2>&1
    Add-Content -Path $logFile -Value $result

    if ($result -match '<promise>COMPLETE</promise>') {
        Write-Host "[ralph] Concluido na iteracao $iteration." -ForegroundColor Green
        Add-Content -Path $logFile -Value "[ralph] COMPLETE na iteracao $iteration"
        break
    }
    Start-Sleep -Seconds 2
}

$env:RALPH_ACTIVE = "0"
Write-Host "[ralph] Loop encerrado. Log salvo em: $logFile"
