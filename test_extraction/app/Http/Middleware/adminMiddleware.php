<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class adminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('login_status')) {
            return redirect('/login-admin')->with('error', 'Silahkan login terlebih dahulu.');
        }

        if (session()->get('role') !== 'admin') {
            return redirect('/dashboard-operator-penugasan')->with('error', 'Akses ditolak! Anda bukan Admin.');
        }

        return $next($request);
    }
}
