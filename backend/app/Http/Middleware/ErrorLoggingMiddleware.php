<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ErrorLoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo loggear errores (4xx y 5xx)
        if ($response->getStatusCode() >= 400) {
            $this->logError($request, $response);
        }

        return $response;
    }

    /**
     * Log details of the error response
     */
    protected function logError(Request $request, Response $response)
    {
        try {
            $statusCode = $response->getStatusCode();
            $logData = [
                'status_code' => $statusCode,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName(),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'request_body' => $this->sanitizeRequestBody($request),
                'response_body' => $this->getResponseBody($response),
                'headers' => $this->sanitizeHeaders($request->headers->all()),
                'timestamp' => now()->toISOString()
            ];

            // Determinar el nivel de log según el código de estado
            if ($statusCode >= 500) {
                Log::error("🚨 Server Error [{$statusCode}] {$request->method()} {$request->fullUrl()}", $logData);
            } elseif ($statusCode >= 400) {
                Log::warning("⚠️ Client Error [{$statusCode}] {$request->method()} {$request->fullUrl()}", $logData);
            }

        } catch (\Exception $e) {
            // No queremos que el logging de errores cause más errores
            Log::error("❌ Error en ErrorLoggingMiddleware", [
                'middleware_error' => $e->getMessage(),
                'original_status' => $response->getStatusCode(),
                'url' => $request->fullUrl()
            ]);
        }
    }

    /**
     * Sanitize request body to remove sensitive information
     */
    protected function sanitizeRequestBody(Request $request)
    {
        $body = $request->all();

        // Campos sensibles que debemos ocultar
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'ssn',
            'phone',
            'email' // opcional, depende de tus políticas
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($body[$field])) {
                $body[$field] = '[REDACTED]';
            }
        }

        // Limitar el tamaño para evitar logs enormes
        $bodyString = json_encode($body);
        if (strlen($bodyString) > 2000) {
            return '[BODY TOO LARGE - ' . strlen($bodyString) . ' chars]';
        }

        return $body;
    }

    /**
     * Get response body safely
     */
    protected function getResponseBody(Response $response)
    {
        try {
            $content = $response->getContent();

            // Solo loggear JSON responses
            if ($response->headers->get('Content-Type') === 'application/json') {
                // Limitar tamaño de respuesta en logs
                if (strlen($content) > 1000) {
                    $decoded = json_decode($content, true);
                    if (is_array($decoded)) {
                        // Mantener solo mensaje de error y código
                        return [
                            'message' => $decoded['message'] ?? null,
                            'error' => $decoded['error'] ?? null,
                            'errors' => $decoded['errors'] ?? null,
                            '_truncated' => 'Response too large for logging'
                        ];
                    }
                    return '[RESPONSE TOO LARGE - ' . strlen($content) . ' chars]';
                }
                return json_decode($content, true);
            }

            return '[NON-JSON RESPONSE]';

        } catch (\Exception $e) {
            return '[ERROR READING RESPONSE]';
        }
    }

    /**
     * Sanitize headers to remove sensitive information
     */
    protected function sanitizeHeaders(array $headers)
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token'
        ];

        $sanitized = [];
        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveHeaders)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                // Solo mantener headers útiles para debugging
                if (in_array($lowerKey, [
                    'user-agent',
                    'accept',
                    'content-type',
                    'x-requested-with',
                    'origin',
                    'referer'
                ])) {
                    $sanitized[$key] = is_array($value) ? implode(', ', $value) : $value;
                }
            }
        }

        return $sanitized;
    }
}