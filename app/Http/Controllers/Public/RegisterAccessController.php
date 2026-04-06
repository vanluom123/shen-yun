<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class RegisterAccessController extends Controller
{
    public function show()
    {
        $guestToken = hash_hmac('sha256', (string) config('rsvp.guest_password'), (string) config('app.key'));
        if (Session::get('guest_authed', false) || request()->cookie('guest_remember') === $guestToken) {
            if (! Session::get('guest_authed', false)) {
                Session::put('guest_authed', true);
            }

            return redirect()->to('/register');
        }

        return view('public.register_access');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $input = trim((string) $data['password']);

        $expected = trim((string) config('rsvp.guest_password'));
        if ($expected === '') {
            throw ValidationException::withMessages([
                'password' => 'Chưa cấu hình GUEST_PASSWORD trong .env',
            ]);
        }

        if (! hash_equals($expected, $input)) {
            throw ValidationException::withMessages([
                'password' => 'Mật khẩu không đúng.',
            ]);
        }

        Session::put('guest_authed', true);

        // Remember for 1 month (43200 minutes)
        $guestToken = hash_hmac('sha256', $expected, (string) config('app.key'));
        Cookie::queue('guest_remember', $guestToken, 43200);

        return redirect()->to('/register');
    }

    public function destroy()
    {
        Session::forget('guest_authed');

        Session::forget('registration_draft_v1');

        Cookie::queue(Cookie::forget('guest_remember'));

        return redirect()->to('/login')->with('status', 'Đã đăng xuất.');
    }
}
