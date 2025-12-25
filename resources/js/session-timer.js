/**
 * Session Timer Management System
 * Manages session countdown, warnings, and auto-logout
 */

class SessionTimer {
    constructor(options = {}) {
        // Session lifetime in minutes (from Laravel config)
        this.sessionLifetime = options.sessionLifetime || 120;

        // Convert to seconds
        this.totalSeconds = this.sessionLifetime * 60;
        this.remainingSeconds = this.totalSeconds;

        // Warning threshold (2 minutes before expiration)
        this.warningThreshold = 120;

        // Flags
        this.warningShown = false;
        this.isActive = false;
        this.lastActivityTime = Date.now();

        // Debounce timer for activity tracking
        this.activityDebounceTimer = null;
        this.activityDebounceDelay = 60000; // 1 minute

        // Timer interval
        this.timerInterval = null;

        // Toast element reference
        this.warningToast = null;

        // CSRF token
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Initialize
        this.init();
    }

    init() {
        console.log('🚀 Session Timer Initialized (Scientific/Absolute Mode)');
        console.log('   Session lifetime:', this.sessionLifetime, 'minutes');
        
        // Calculate the absolute expiration time based on NOW + Lifetime
        // This is robust against browser throttling/sleeping tabs
        const now = Date.now();
        this.expirationTime = now + (this.sessionLifetime * 60 * 1000);
        
        // Initial calculation
        this.remainingSeconds = this.sessionLifetime * 60;

        console.log('   Expiration Timestamp:', new Date(this.expirationTime).toLocaleTimeString());

        // Start the timer
        this.startTimer();

        // Setup activity listeners
        this.setupActivityListeners();

        // Setup visibility change listener
        this.setupVisibilityListener();

        // Create warning toast element
        this.createWarningToast();
    }

    startTimer() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }

        this.isActive = true;
        this.updateDisplay();

        // Run the check every second
        this.timerInterval = setInterval(() => {
            this.tick();
        }, 1000);
    }

    tick() {
        // Calculate remaining time by comparing NOW with TARGET
        // This fixes the "inactive tab" issue where browsers slow down counters
        const now = Date.now();
        const diff = this.expirationTime - now;
        
        // Update local state
        this.remainingSeconds = Math.max(0, Math.ceil(diff / 1000));
        
        this.updateDisplay();

        // Check for warning threshold
        if (this.remainingSeconds <= this.warningThreshold && !this.warningShown && this.remainingSeconds > 0) {
            this.showWarning();
        }

        // Check for expiration
        if (this.remainingSeconds <= 0) {
            this.handleExpiration();
        }
    }

    // When session is renewed (activity), we simply push the expiration time forward
    resetSessionStartTime() {
        const now = Date.now();
        this.expirationTime = now + (this.sessionLifetime * 60 * 1000);
        this.remainingSeconds = this.sessionLifetime * 60;
        
        this.warningShown = false;
        this.hideWarningToast();
        this.updateDisplay();
        
        console.log('   Timer extended. New expiration:', new Date(this.expirationTime).toLocaleTimeString());
    }

    updateDisplay() {
        const timerElement = document.getElementById('sessionTimer');
        if (!timerElement) return;

        const formatted = this.formatTime(this.remainingSeconds);
        timerElement.textContent = formatted;

        // Update color based on remaining time
        timerElement.className = 'session-timer-display small fw-mono';

        if (this.remainingSeconds <= 120) {
            timerElement.classList.add('text-danger');
        } else if (this.remainingSeconds <= 300) {
            timerElement.classList.add('text-warning');
        } else {
            timerElement.classList.add('text-body-secondary');
        }

        // Auto-hide warning if time was replenished (e.g. by another tab or activity)
        if (this.remainingSeconds > this.warningThreshold && this.warningShown) {
            this.hideWarningToast();
        }
    }

    formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    createWarningToast() {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');

        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toastHTML = `
            <div id="sessionWarningToast" class="toast align-items-center text-bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div>
                                <strong>Sua sessão expirará em breve!</strong>
                                <p class="mb-0 mt-1 small">Qualquer interação renovará o tempo automaticamente.</p>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHTML);

        this.warningToast = new bootstrap.Toast(document.getElementById('sessionWarningToast'), {
            autohide: false
        });
    }

    showWarning() {
        if (this.warningToast && !this.warningShown) {
            this.warningToast.show();
            this.warningShown = true;
        }
    }

    hideWarningToast() {
        if (this.warningToast && this.warningShown) {
            this.warningToast.hide();
            this.warningShown = false;
        }
    }

    async handleExpiration() {
        // Stop the timer
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }

        // Set flag for expired session
        localStorage.setItem('session_expired', 'true');

        // Perform logout
        await this.performLogout();
    }

    async performLogout() {
        try {
            console.log('🚪 Executing auto-logout sequence...');
            
            // Get routes from Meta Tags (Generated by Laravel route() helper)
            // This ensures we respect subdirectories and absolute paths
            const logoutUrl = document.querySelector('meta[name="route-logout"]')?.getAttribute('content') || '/logout';
            
            // Use fetch API instead of form submission
            await fetch(logoutUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            
            console.log('✅ Logout request completed');
        } catch (error) {
            console.warn('⚠️ Logout request failed (likely already expired):', error);
        } finally {
            // Determine login URL dynamically from Meta Tag
            let loginUrl = document.querySelector('meta[name="route-login"]')?.getAttribute('content') || '/login';
            
            try {
                // Safely append query parameter
                const urlObj = new URL(loginUrl);
                urlObj.searchParams.set('session_expired', '1');
                window.location.href = urlObj.toString();
            } catch (e) {
                // Fallback for relative paths if URL constructor fails (should not happen with route())
                const separator = loginUrl.includes('?') ? '&' : '?';
                window.location.href = `${loginUrl}${separator}session_expired=1`;
            }
        }
    }

    setupActivityListeners() {
        // Events that should renew the session
        const activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];

        activityEvents.forEach(eventType => {
            document.addEventListener(eventType, () => {
                this.handleActivity();
            }, { passive: true });
        });

        // Livewire events
        document.addEventListener('livewire:navigated', () => {
            this.handleActivity();
        });

        // Also listen for Livewire updates
        document.addEventListener('livewire:update', () => {
            this.handleActivity();
        });
    }

    handleActivity() {
        const now = Date.now();
        const timeSinceLastActivity = now - this.lastActivityTime;

        // Debounce: only process if more than 30 seconds since last activity
        // This ensures session is renewed more frequently
        if (timeSinceLastActivity >= 30000) {
            console.log('🔄 Activity detected - Time since last activity:', Math.floor(timeSinceLastActivity / 1000), 'seconds');
            this.lastActivityTime = now;
            this.renewSession();
        } else {
            console.log('⏭️ Activity detected but debounced - Time since last:', Math.floor(timeSinceLastActivity / 1000), 's (need 30s)');
        }
    }

    async renewSession() {
        console.log('📡 Attempting to renew session...');
        console.log('   Current remaining time:', this.formatTime(this.remainingSeconds));

        try {
            // Make a request to the session ping endpoint to renew the session
            const response = await fetch('/session/ping', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                credentials: 'same-origin'
            });

            console.log('   Response status:', response.status);

            if (response.ok) {
                const data = await response.json();
                console.log('   Response data:', data);

                // Session renewed successfully
                this.resetSessionStartTime();
                console.log('✅ Session renewed successfully!');
                console.log('   Timer reset to:', this.sessionLifetime, 'minutes');
                console.log('   New remaining time:', this.formatTime(this.remainingSeconds));

                // Update display immediately
                this.updateDisplay();
            } else {
                const errorText = await response.text();
                console.warn('⚠️ Failed to renew session');
                console.warn('   Status:', response.status);
                console.warn('   Response:', errorText);
            }
        } catch (error) {
            console.error('❌ Error renewing session:', error);
        }
    }

    setupVisibilityListener() {
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Page became visible again
                // Force a tick update immediately to sync the UI with real time
                this.tick();
            }
        });
    }

    destroy() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }

        if (this.activityDebounceTimer) {
            clearTimeout(this.activityDebounceTimer);
        }

        this.isActive = false;
    }
}

// Initialize session timer when DOM is ready
let sessionTimerInstance = null;

function initializeSessionTimer() {
    // Only initialize for authenticated users
    const timerElement = document.getElementById('sessionTimer');

    if (timerElement && !sessionTimerInstance) {
        // Get session lifetime from meta tag or use default
        const sessionLifetime = parseInt(
            document.querySelector('meta[name="session-lifetime"]')?.getAttribute('content') || '120'
        );

        sessionTimerInstance = new SessionTimer({
            sessionLifetime: sessionLifetime
        });
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initializeSessionTimer);

// Re-initialize on Livewire navigation
document.addEventListener('livewire:navigated', initializeSessionTimer);

// Export for use in other modules
export default SessionTimer;
