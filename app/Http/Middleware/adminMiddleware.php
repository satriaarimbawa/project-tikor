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

        $role = session()->get('role');
        $isItSupport = session()->get('is_it_support', false);

        if (!in_array($role, ['admin', 'it_support', 'superadmin']) && !$isItSupport) {
            return redirect('/dashboard-operator-penugasan')->with('error', 'Akses ditolak! Anda bukan Administrator.');
        }

        return $next($request);
    }
}
