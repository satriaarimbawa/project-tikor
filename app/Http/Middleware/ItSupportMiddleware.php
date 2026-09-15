<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItSupportMiddleware
{
    /**
     * Handle an incoming request.
     * Ensure only IT Support accounts can access system settings.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('login_status')) {
            return redirect('/login-admin')->with('error', 'Silakan login terlebih dahulu.');
        }

        $username = strtolower(session('username', ''));
        $email = strtolower(session('email', ''));
        $role = strtolower(session('role', ''));
        $isItSupport = session('is_it_support', false);

        $allowed = $isItSupport 
            || $role === 'it_support'
            || str_contains($username, 'it support')
            || str_contains($username, 'itsupport')
            || str_contains($email, 'itsupport');

        if (!$allowed) {
            return redirect('/dashboard-admin')->with('error', 'Akses ditolak! Halaman Pengaturan Sistem khusus untuk akun IT Support.');
        }

        return $next($request);
    }
}
