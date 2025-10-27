<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Rate limiter general para API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiter específico para endpoints de elementos
        RateLimiter::for('elements', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    \Log::info('🚫 Rate limit alcanzado - elementos', [
                        'user_id' => $request->user()?->id,
                        'ip' => $request->ip(),
                        'route' => $request->getPathInfo()
                    ]);
                    return response()->json([
                        'message' => 'Muchas peticiones de elementos. Espera unos segundos.',
                        'retry_after' => 60
                    ], 429, $headers);
                });
        });

        // Rate limiter más restrictivo para actualizaciones
        RateLimiter::for('updates', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    \Log::info('🚫 Rate limit alcanzado - updates', [
                        'user_id' => $request->user()?->id,
                        'ip' => $request->ip(),
                        'route' => $request->getPathInfo()
                    ]);
                    return response()->json([
                        'message' => 'Muchas actualizaciones. Espera unos segundos.',
                        'retry_after' => 60
                    ], 429, $headers);
                });
        });

        // Rate limiter muy restrictivo para metas (el que más problemas causa)
        RateLimiter::for('metas', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    \Log::info('🚫 Rate limit alcanzado - metas', [
                        'user_id' => $request->user()?->id,
                        'ip' => $request->ip(),
                        'route' => $request->getPathInfo()
                    ]);
                    return response()->json([
                        'message' => 'Muchas peticiones de metas. Espera unos segundos.',
                        'retry_after' => 60
                    ], 429, $headers);
                });
        });

        // Rate limiter para logs del frontend
        RateLimiter::for('logs', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    \Log::warning('🚫 Rate limit alcanzado - logs', [
                        'user_id' => $request->user()?->id,
                        'ip' => $request->ip(),
                        'route' => $request->getPathInfo()
                    ]);
                    return response()->json([
                        'message' => 'Muchos logs enviados. Espera unos segundos.',
                        'retry_after' => 60
                    ], 429, $headers);
                });
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
