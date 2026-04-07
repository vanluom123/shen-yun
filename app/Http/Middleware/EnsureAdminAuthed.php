<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adminToken = hash_hmac('sha256', (string) config('rsvp.admin_password'), (string) config('app.key'));

        if (! Session::get('admin_authed', false)) {
            if ($request->cookie('admin_remember') === $adminToken) {
                Session::put('admin_authed', true);
            } else {
                return redirect()->guest('/admin/login');
            }
        }

        return $next($request);
    }
}
