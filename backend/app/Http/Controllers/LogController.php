<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LogController extends Controller
{
    /**
     * Registrar logs de error desde el frontend
     */
    public function logError(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:500',
                'level' => 'required|string|in:error,warning,info,debug',
                'source' => 'required|string|max:100', // Ej: 'frontend', 'mobile-app'
                'component' => 'nullable|string|max:100', // Ej: 'login-form', 'alarm-controller'
                'action' => 'nullable|string|max:100', // Ej: 'user-login', 'create-alarm'
                'error_code' => 'nullable|string|max:50',
                'user_agent' => 'nullable|string',
                'url' => 'nullable|string|max:500',
                'line_number' => 'nullable|integer',
                'stack_trace' => 'nullable|string|max:2000',
                'additional_data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de log inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $logData = [
                'user_id' => $user ? $user->id : null,
                'source' => $request->source,
                'component' => $request->component,
                'action' => $request->action,
                'error_code' => $request->error_code,
                'user_agent' => $request->user_agent ?: $request->header('User-Agent'),
                'url' => $request->url,
                'line_number' => $request->line_number,
                'stack_trace' => $request->stack_trace,
                'ip_address' => $request->ip(),
                'timestamp' => now()->toISOString(),
                'additional_data' => $request->additional_data
            ];

            // Escribir log según el nivel especificado
            $message = "🌐 [FRONTEND] {$request->message}";

            switch ($request->level) {
                case 'error':
                    Log::error($message, $logData);
                    break;
                case 'warning':
                    Log::warning($message, $logData);
                    break;
                case 'info':
                    Log::info($message, $logData);
                    break;
                case 'debug':
                    Log::debug($message, $logData);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Log registrado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error registrando log desde frontend", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Registrar múltiples logs en lote (para envíos offline)
     */
    public function logBatch(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'logs' => 'required|array|max:50', // Máximo 50 logs por lote
                'logs.*.message' => 'required|string|max:500',
                'logs.*.level' => 'required|string|in:error,warning,info,debug',
                'logs.*.source' => 'required|string|max:100',
                'logs.*.component' => 'nullable|string|max:100',
                'logs.*.action' => 'nullable|string|max:100',
                'logs.*.timestamp' => 'nullable|date_format:Y-m-d\TH:i:s.u\Z'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de logs inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $processedLogs = 0;
            $failedLogs = 0;

            foreach ($request->logs as $logEntry) {
                try {
                    $logData = [
                        'user_id' => $user ? $user->id : null,
                        'source' => $logEntry['source'],
                        'component' => $logEntry['component'] ?? null,
                        'action' => $logEntry['action'] ?? null,
                        'user_agent' => $request->header('User-Agent'),
                        'ip_address' => $request->ip(),
                        'original_timestamp' => $logEntry['timestamp'] ?? null,
                        'batch_processed_at' => now()->toISOString()
                    ];

                    $message = "🌐 [FRONTEND-BATCH] {$logEntry['message']}";

                    switch ($logEntry['level']) {
                        case 'error':
                            Log::error($message, $logData);
                            break;
                        case 'warning':
                            Log::warning($message, $logData);
                            break;
                        case 'info':
                            Log::info($message, $logData);
                            break;
                        case 'debug':
                            Log::debug($message, $logData);
                            break;
                    }

                    $processedLogs++;

                } catch (\Exception $e) {
                    $failedLogs++;
                    Log::error("❌ Error procesando log individual en lote", [
                        'log_entry' => $logEntry,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("📊 Lote de logs procesado", [
                'user_id' => $user ? $user->id : null,
                'total_logs' => count($request->logs),
                'processed' => $processedLogs,
                'failed' => $failedLogs
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lote de logs procesado',
                'processed' => $processedLogs,
                'failed' => $failedLogs,
                'total' => count($request->logs)
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error procesando lote de logs", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'logs_count' => count($request->logs ?? [])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Registrar evento de usuario (para analytics)
     */
    public function logEvent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'event_name' => 'required|string|max:100',
                'event_category' => 'required|string|max:50', // Ej: 'user_interaction', 'app_performance'
                'event_data' => 'nullable|array',
                'session_id' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de evento inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $eventData = [
                'user_id' => $user ? $user->id : null,
                'session_id' => $request->session_id,
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
                'timestamp' => now()->toISOString(),
                'event_data' => $request->event_data
            ];

            Log::info("📈 [EVENT] {$request->event_category}: {$request->event_name}", $eventData);

            return response()->json([
                'success' => true,
                'message' => 'Evento registrado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error registrando evento", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'event_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Registrar métrica de rendimiento
     */
    public function logPerformance(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'metric_name' => 'required|string|max:100',
                'metric_value' => 'required|numeric',
                'metric_unit' => 'required|string|max:20', // Ej: 'ms', 'seconds', 'bytes'
                'component' => 'nullable|string|max:100',
                'operation' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de métrica inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $performanceData = [
                'user_id' => $user ? $user->id : null,
                'metric_value' => $request->metric_value,
                'metric_unit' => $request->metric_unit,
                'component' => $request->component,
                'operation' => $request->operation,
                'user_agent' => $request->header('User-Agent'),
                'timestamp' => now()->toISOString()
            ];

            Log::info("⚡ [PERFORMANCE] {$request->metric_name}: {$request->metric_value}{$request->metric_unit}", $performanceData);

            return response()->json([
                'success' => true,
                'message' => 'Métrica registrada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error registrando métrica de rendimiento", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'metric_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}