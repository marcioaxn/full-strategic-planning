<div class="auth-wrapper w-100" style="max-width: 420px;">
    <div class="text-center mb-4">
        {{ $logo }}
    </div>

    <div class="card auth-card border-0">
        <div class="card-body p-4 p-lg-5">
            {{ $slot }}
        </div>
    </div>
</div>

<style>
    .auth-card {
        background-color: #ffffff;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    [data-bs-theme="dark"] .auth-card {
        background-color: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }

    /* Logo adjustments for dark mode */
    [data-bs-theme="dark"] .auth-wrapper img {
        filter: brightness(0.9);
    }
</style>
