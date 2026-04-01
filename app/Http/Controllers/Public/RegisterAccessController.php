<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class RegisterAccessController extends Controller
{
    public function show()
    {
        if (Session::get('guest_authed', false)) {
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

        return redirect()->to('/register');
    }

    public function destroy()
    {
        Session::forget('guest_authed');

        Session::forget('registration_draft_v1');

        return redirect()->to('/login')->with('status', 'Đã đăng xuất.');
    }
}
