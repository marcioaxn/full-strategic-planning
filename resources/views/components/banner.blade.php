@props(['style' => session('flash.bannerStyle', 'success'), 'message' => session('flash.banner')])

<div x-data="{{ json_encode(['show' => true, 'style' => $style, 'message' => $message]) }}"
     x-cloak
     x-show="show && message"
     x-on:banner-message.window="
        style = event.detail.style;
        message = event.detail.message;
        show = true;
    "
     class="border-bottom bg-body-secondary">
    <div class="container py-2">
        <div class="alert d-flex align-items-center gap-3 mb-0"
             role="alert"
             :class="{
                'alert-success': style === 'success',
                'alert-danger': style === 'danger',
                'alert-warning': style === 'warning',
                'alert-secondary': style !== 'success' && style !== 'danger' && style !== 'warning'
             }">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0 bg-opacity-25"
                  :class="{
                    'bg-success text-success': style === 'success',
                    'bg-danger text-danger': style === 'danger',
                    'bg-warning text-warning': style === 'warning',
                    'bg-secondary text-secondary': style !== 'success' && style !== 'danger' && style !== 'warning'
                  }"
                  style="width: 2.5rem; height: 2.5rem;">
                <svg x-show="style === 'success'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm-1.25 14.331-3.75-3.75 1.061-1.061 2.689 2.689 5.439-5.439 1.061 1.061Z"/>
                </svg>
                <svg x-show="style === 'danger'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm0 13.75a1 1 0 1 1 0 2 1 1 0 0 1 0-2Zm.75-2.75a.75.75 0 0 1-1.5 0V7.75a.75.75 0 0 1 1.5 0Z"/>
                </svg>
                <svg x-show="style === 'warning'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10.615 2.301a2.25 2.25 0 0 1 3.77 0l8.584 13.116A2.25 2.25 0 0 1 20.969 19.5H3.031a2.25 2.25 0 0 1-1.999-4.083Z"/>
                    <path fill="#fff" d="M12 7a.75.75 0 0 1 .75.75v4.5a.75.75 0 1 1-1.5 0v-4.5A.75.75 0 0 1 12 7Zm0 8a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"/>
                </svg>
                <svg x-show="style !== 'success' && style !== 'danger' && style !== 'warning'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm-.25 5.5h1.5v1.5h-1.5Zm0 3h1.5v8h-1.5Z"/>
                </svg>
            </span>

            <p class="mb-0 flex-grow-1 lh-base" x-text="message"></p>

            <button type="button" class="btn-close ms-2" aria-label="Dismiss" @click="show = false"></button>
        </div>
    </div>
</div>
