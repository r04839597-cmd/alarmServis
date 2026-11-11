<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Role & status ditentukan server. Superadmin tidak bisa mendaftar lewat form.
        $isAdminBengkel = $data['account_type'] === 'admin_bengkel';

        $user = User::create([
            'name'                    => $data['name'],
            'phone_number'            => $data['phone_number'], // sudah 628...
            'password'                => $data['password'],     // di-hash oleh cast 'hashed'
            'role_id'                 => $isAdminBengkel ? User::ROLE_ADMIN_BENGKEL : User::ROLE_USER,
            'notification_preference' => 'both',
            'status'                  => $isAdminBengkel ? 'pending_approval' : 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

      return redirect()->to($user->getRedirectRoute());
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'phone_number' => ['required', 'string'],
            'password'     => ['required', 'string'],
        ], [
            'phone_number.required' => 'Nomor HP wajib diisi.',
            'password.required'     => 'Password wajib diisi.',
        ]);

        $credentials = [
            'phone_number' => PhoneNumber::sanitize($request->input('phone_number')),
            'password'     => $request->input('password'),
        ];

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['phone_number' => 'Nomor HP atau password salah.'])
                ->onlyInput('phone_number');
        }

        $request->session()->regenerate();

        // Tanpa intended(): mencegah redirect ke URL milik role lain (berujung 403).
      return redirect()->to(Auth::user()->getRedirectRoute());
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
