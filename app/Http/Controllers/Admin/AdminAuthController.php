<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function show()
    {
        if (Session::get('admin_authed', false)) {
            return redirect()->to('/admin');
        }

        return view('admin.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $expected = (string) config('rsvp.admin_password');
        if ($expected === '') {
            throw ValidationException::withMessages([
                'password' => 'Chưa cấu hình ADMIN_PASSWORD trong .env',
            ]);
        }

        if (! hash_equals($expected, $data['password'])) {
            throw ValidationException::withMessages([
                'password' => 'Mật khẩu không đúng.',
            ]);
        }

        Session::put('admin_authed', true);

        return redirect()->to('/admin');
    }

    public function destroy()
    {
        Session::forget('admin_authed');

        return redirect()->to('/admin/login')->with('status', 'Đã đăng xuất admin session.');
    }
}
