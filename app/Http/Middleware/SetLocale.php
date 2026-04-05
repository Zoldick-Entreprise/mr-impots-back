<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        $lang = config('app.locale');

        if ($user && $user->preferred_language) {
            $lang = ($user->preferred_language);
        } elseif ($request->hasHeader('Accept-Language')) {
            $lang = $request->getPreferredLanguage(['en', 'fr']);
        }

        App::setLocale($lang);

        return $next($request);
    }
}
