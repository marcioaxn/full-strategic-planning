@props(['password' => ''])

@php
    $score = 0;
    if (strlen($password) >= 8) $score++;
    if (preg_match('/[A-Z]/', $password)) $score++;
    if (preg_match('/[a-z]/', $password)) $score++;
    if (preg_match('/[0-9]/', $password)) $score++;
    if (preg_match('/[^A-Za-z0-9]/', $password)) $score++;

    $color = match(true) {
        $score <= 2 => 'danger',
        $score <= 4 => 'warning',
        default => 'success',
    };

    $label = match(true) {
        $score <= 2 => 'Fraca',
        $score <= 4 => 'Média',
        default => 'Forte',
    };

    $width = ($score / 5) * 100;
@endphp

<div class="password-strength-meter mt-2">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="x-small text-muted text-uppercase fw-bold">Segurança da Senha:</span>
        <span class="badge bg-{{ $color }} x-small">{{ $label }}</span>
    </div>
    <div class="progress" style="height: 4px;">
        <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $width }}%" aria-valuenow="{{ $width }}" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <div class="mt-2 row g-1">
        <div class="col-6 x-small {{ strlen($password) >= 8 ? 'text-success' : 'text-muted' }}">
            <i class="bi bi-{{ strlen($password) >= 8 ? 'check-circle-fill' : 'circle' }} me-1"></i> 8+ caracteres
        </div>
        <div class="col-6 x-small {{ preg_match('/[A-Z]/', $password) ? 'text-success' : 'text-muted' }}">
            <i class="bi bi-{{ preg_match('/[A-Z]/', $password) ? 'check-circle-fill' : 'circle' }} me-1"></i> Letra maiúscula
        </div>
        <div class="col-6 x-small {{ preg_match('/[0-9]/', $password) ? 'text-success' : 'text-muted' }}">
            <i class="bi bi-{{ preg_match('/[0-9]/', $password) ? 'check-circle-fill' : 'circle' }} me-1"></i> Número
        </div>
        <div class="col-6 x-small {{ preg_match('/[^A-Za-z0-9]/', $password) ? 'text-success' : 'text-muted' }}">
            <i class="bi bi-{{ preg_match('/[^A-Za-z0-9]/', $password) ? 'check-circle-fill' : 'circle' }} me-1"></i> Símbolo
        </div>
    </div>
</div>
