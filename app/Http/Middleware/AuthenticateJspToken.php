<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AuthenticateJspToken
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $payload = json_decode(
                Crypt::decryptString($request->bearerToken() ?? ''),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (DecryptException|\JsonException $e) {
            abort(401, 'Please sign in again.');
        }
        abort_unless(
            is_array($payload) &&
                ($payload['purpose'] ?? '') === 'jsp-api' &&
                ($payload['expires'] ?? 0) > time(),
            401,
            'Your API session has expired.',
        );
        $user = User::query()->find($payload['id'] ?? 0);
        abort_unless($user && $user->is_active, 401, 'This account is inactive.');
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
