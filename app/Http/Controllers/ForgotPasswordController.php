<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function showLinkRequestForm()
    {
        return view('login.forgot-password');
    }

    public function sendResetCodeEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $throttleKey = 'otp-send:' . Str::lower($request->email) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Terlalu banyak permintaan OTP. Silakan tunggu $seconds detik.");
        }

        $users = $this->database->getReference('users')
                    ->orderByChild('email')
                    ->equalTo($request->email)
                    ->getValue();

        if (!$users) {
            RateLimiter::hit($throttleKey, 300);
            return back()->with('error', 'Email tidak terdaftar!');
        }

        $otp = (string) random_int(100000, 999999);
        $expiresAt = Carbon::now('Asia/Makassar')->addMinutes(10);

        $emailKey = base64_encode($request->email);
        $this->database->getReference("password_resets/{$emailKey}")->set([
            'email' => $request->email,
            'otp' => Hash::make($otp),
            'expires_at' => $expiresAt->toDateTimeString()
        ]);

        RateLimiter::hit($throttleKey, 300);

        Mail::raw("Kode OTP Lupa Password Anda adalah: $otp. Kode ini berlaku selama 10 menit.", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Kode OTP Reset Password Uji Petik');
        });

        session(['reset_email' => $request->email]);

        return redirect()->route('password.otp')->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    public function showOtpForm()
    {
        if (!session('reset_email')) return redirect()->route('password.request');
        return view('login.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);
        
        $email = session('reset_email');
        if (!$email) return redirect()->route('password.request');

        $throttleKey = 'otp-verify:' . Str::lower($email) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Terlalu banyak percobaan OTP salah. Silakan coba lagi dalam $seconds detik.");
        }

        $emailKey = base64_encode($email);
        $resetData = $this->database->getReference("password_resets/{$emailKey}")->getValue();

        if (!$resetData || !Hash::check((string)$request->otp, $resetData['otp'])) {
            RateLimiter::hit($throttleKey, 600);
            return back()->with('error', 'Kode OTP salah atau tidak ditemukan!');
        }

        if (Carbon::now('Asia/Makassar')->gt(Carbon::parse($resetData['expires_at']))) {
            return back()->with('error', 'Kode OTP telah kedaluwarsa!');
        }

        RateLimiter::clear($throttleKey);
        session(['otp_verified' => true]);

        return redirect()->route('password.reset')->with('success', 'OTP Terverifikasi. Silakan masukkan password baru.');
    }

    public function showResetForm()
    {
        if (!session('otp_verified')) return redirect()->route('password.request');
        return view('login.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(6)->numbers()->symbols(), 'regex:/[A-Z]/']
        ]);

        $email = session('reset_email');

        $users = $this->database->getReference('users')
                    ->orderByChild('email')
                    ->equalTo($email)
                    ->getValue();

        if (!$users) return redirect()->route('password.request')->with('error', 'User tidak ditemukan.');

        $uid = array_key_first($users);
        $user_data = $users[$uid];

        $this->database->getReference("users/{$uid}/password")->set(Hash::make($request->password));

        $emailKey = base64_encode($email);
        $this->database->getReference("password_resets/{$emailKey}")->remove();

        $redirectTo = ($user_data['role_user'] === 'admin') ? '/login-admin' : '/login';

        session()->forget(['reset_email', 'otp_verified', 'simulated_otp']);

        return redirect($redirectTo)->with('success', 'Password berhasil diperbarui. Silakan login.');
    }
}
