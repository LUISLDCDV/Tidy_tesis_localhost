<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$limits): Response
    {
        $user = $request->user();
        $ip = $request->ip();
        $route = $request->route()->getName() ?: $request->getPathInfo();

        // Crear clave única para el rate limiting
        $key = $user ? "user:{$user->id}:{$route}" : "ip:{$ip}:{$route}";

        // Configuraciones de rate limiting por tipo de endpoint
        $throttleConfig = $this->getThrottleConfig($route);

        // Aplicar rate limiting
        if (RateLimiter::tooManyAttempts($key, $throttleConfig['attempts'])) {
            $retryAfter = RateLimiter::availableIn($key);

            \Log::warning("🚫 Rate limit alcanzado", [
                'key' => $key,
                'route' => $route,
                'user_id' => $user?->id,
                'ip' => $ip,
                'retry_after' => $retryAfter
            ]);

            return response()->json([
                'message' => 'Demasiadas peticiones. Intenta de nuevo en unos segundos.',
                'retry_after' => $retryAfter
            ], 429)->header('Retry-After', $retryAfter);
        }

        // Registrar el intento
        RateLimiter::hit($key, $throttleConfig['decay']);

        $response = $next($request);

        // Agregar headers informativos
        $response->headers->set('X-RateLimit-Limit', $throttleConfig['attempts']);
        $response->headers->set('X-RateLimit-Remaining',
            $throttleConfig['attempts'] - RateLimiter::attempts($key)
        );

        return $response;
    }

    /**
     * Obtener configuración de throttling por ruta
     */
    private function getThrottleConfig($route): array
    {
        // Configuraciones específicas por tipo de endpoint
        $configs = [
            // Endpoints de metas - más permisivos
            '/elementos/{id}/metas' => [
                'attempts' => 60, // 60 peticiones
                'decay' => 60     // por minuto
            ],

            // Endpoints de actualización de metas
            'meta.update' => [
                'attempts' => 60,
                'decay' => 60
            ],

            // Endpoints de elementos generales
            '/usuarios/elementos' => [
                'attempts' => 100,
                'decay' => 60
            ],

            // Endpoints de objetivos
            '/usuarios/objetivos' => [
                'attempts' => 60,
                'decay' => 60
            ],

            // Autenticación - restrictivo para seguridad
            '/login' => [
                'attempts' => 5,
                'decay' => 300 // 5 intentos cada 5 minutos
            ],

            // Default para otros endpoints
            'default' => [
                'attempts' => 100,
                'decay' => 60
            ]
        ];

        // Buscar configuración específica
        foreach ($configs as $pattern => $config) {
            if (str_contains($route, $pattern)) {
                return $config;
            }
        }

        return $configs['default'];
    }
}