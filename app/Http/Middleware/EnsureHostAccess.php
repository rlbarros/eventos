<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Conta de anfitrião cujo perfil foi retirado na administração (nenhuma jurisdição ativa) perde
 * o acesso: a sessão é encerrada e o login avisa o motivo. Contas antigas, sem vínculo com a
 * administração, não passam por aqui.
 */
class EnsureHostAccess
{
    public const MESSAGE = 'Seu acesso de anfitrião foi retirado na administração. Fale com a secretaria para reativá-lo.';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hostJurisdiction()->hasAccess()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => self::MESSAGE]);
        }

        return $next($request);
    }
}
