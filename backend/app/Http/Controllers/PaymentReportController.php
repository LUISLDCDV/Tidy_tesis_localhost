<?php

namespace App\Http\Controllers;

use App\Models\PaymentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaymentReportController extends Controller
{
    /**
     * Guardar un nuevo informe de pago
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
                'transaction_id' => 'nullable|string|max:255',
                'comments' => 'nullable|string|max:2000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $paymentReport = PaymentReport::create([
                'user_id' => $user->id,
                'email' => $request->email,
                'transaction_id' => $request->transaction_id,
                'comments' => $request->comments,
                'status' => 'pending'
            ]);

            Log::info('Payment report created', [
                'report_id' => $paymentReport->id,
                'user_id' => $user->id,
                'email' => $request->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Informe de pago recibido correctamente',
                'data' => $paymentReport
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating payment report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el informe de pago'
            ], 500);
        }
    }

    /**
     * Obtener todos los informes de pago (admin)
     */
    public function index(Request $request)
    {
        try {
            $query = PaymentReport::with(['usuario', 'reviewer']);

            // Filtrar por estado si se proporciona
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Ordenar por fecha de creación descendente
            $query->orderBy('created_at', 'desc');

            $reports = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $reports
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching payment reports', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los informes'
            ], 500);
        }
    }

    /**
     * Obtener un informe específico
     */
    public function show($id)
    {
        try {
            $report = PaymentReport::with(['usuario', 'reviewer'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Informe no encontrado'
            ], 404);
        }
    }

    /**
     * Actualizar el estado de un informe (admin)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:approved,rejected',
                'notes' => 'nullable|string|max:2000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $report = PaymentReport::findOrFail($id);
            $admin = Auth::user();

            $report->markAsReviewed($admin->id, $request->status, $request->notes);

            Log::info('Payment report reviewed', [
                'report_id' => $report->id,
                'admin_id' => $admin->id,
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Informe actualizado correctamente',
                'data' => $report->load(['usuario', 'reviewer'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating payment report', [
                'error' => $e->getMessage(),
                'report_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el informe'
            ], 500);
        }
    }

    /**
     * Obtener informes del usuario autenticado
     */
    public function myReports()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $reports = PaymentReport::where('user_id', $user->id)
                ->with('reviewer')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reports
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching user payment reports', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los informes'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de informes (admin)
     */
    public function stats()
    {
        try {
            $stats = [
                'total' => PaymentReport::count(),
                'pending' => PaymentReport::pending()->count(),
                'approved' => PaymentReport::approved()->count(),
                'rejected' => PaymentReport::rejected()->count(),
                'recent' => PaymentReport::where('created_at', '>=', now()->subDays(7))->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching payment report stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }
}
