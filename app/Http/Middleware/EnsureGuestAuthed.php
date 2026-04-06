<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestAuthed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guestToken = hash_hmac('sha256', (string) config('rsvp.guest_password'), (string) config('app.key'));
        
        if (! Session::get('guest_authed', false)) {
            if ($request->cookie('guest_remember') === $guestToken) {
                Session::put('guest_authed', true);
            } else {
                return redirect()->to('/login');
            }
        }

        return $next($request);
    }
}
