<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rotas que gravam réplicas da administração (anfitriões, igrejas): só o usuário do data-sync,
 * identificado pelo e-mail em DATA_SYNC_EMAIL. Sem a variável, ninguém grava.
 */
class EnsureDataSyncUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = mb_strtolower(trim((string) config('services.data_sync.email')));
        $email = mb_strtolower(trim((string) $request->user()?->email));

        abort_if($expected === '' || $email !== $expected, 403, 'Somente o usuário do data-sync pode gravar esta réplica.');

        return $next($request);
    }
}
