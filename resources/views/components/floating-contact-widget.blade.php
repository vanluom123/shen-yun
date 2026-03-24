@php
    $hotline = config('rsvp.hotline_number');
    $zaloUrl = config('rsvp.zalo_url');
@endphp

<style>
    @keyframes pulse-ring {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.8;
        }
    }
    
    .contact-widget-btn {
        animation: pulse-ring 2s ease-in-out infinite;
    }
    
    .contact-widget-btn:hover {
        animation: none;
    }
</style>

@if ($hotline)
<div class="fixed bottom-6 right-4 z-50 flex flex-col gap-3 sm:bottom-8 sm:right-6">
    <a href="tel:{{ $hotline }}"
       aria-label="Gọi hotline {{ $hotline }}"
       class="contact-widget-btn flex items-center justify-center w-11 h-11 rounded-3xl shadow-lg transition-all duration-200 hover:scale-110"
       style="background-color: #D4B37D;"
       onmouseover="this.style.backgroundColor='#E5C78E'"
       onmouseout="this.style.backgroundColor='#D4B37D'">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="#2D3A2F" aria-hidden="true">
            <path fill-rule="evenodd" d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5z" clip-rule="evenodd" />
        </svg>
    </a>
</div>
@endif
