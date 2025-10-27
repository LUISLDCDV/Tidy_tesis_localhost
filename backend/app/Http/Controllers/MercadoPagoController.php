<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsuarioCuenta;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MercadoPagoController extends Controller
{
    public function subscriptionSuccess(Request $request)
    {
        try {
            $collectionId = $request->get('collection_id');
            $status = $request->get('status');
            $preapprovalId = $request->get('preapproval_id');
            $userId = $request->get('external_reference');

            if ($status === 'approved' && $userId) {
                $user = User::find($userId);
                if ($user && $user->usuarioCuenta) {
                    $cuenta = $user->usuarioCuenta;
                    $cuenta->update([
                        'is_premium' => true,
                        'premium_expires_at' => Carbon::now()->addMonth(),
                        'mercadopago_subscription_id' => $preapprovalId
                    ]);

                    // Guardar registro de pago
                    Payment::create([
                        'user_id' => $userId,
                        'payment_id' => $collectionId ?? 'COL-' . uniqid(),
                        'collection_id' => $collectionId,
                        'subscription_id' => $preapprovalId,
                        'status' => $status,
                        'payment_type' => 'subscription',
                        'amount' => $request->get('transaction_amount', 0),
                        'currency' => 'ARS',
                        'plan_type' => 'monthly',
                        'description' => 'Suscripción Premium - Tidy',
                        'metadata' => json_encode($request->all()),
                        'paid_at' => Carbon::now()
                    ]);

                    Log::info('Usuario actualizado a premium y pago registrado', [
                        'user_id' => $userId,
                        'subscription_id' => $preapprovalId,
                        'collection_id' => $collectionId
                    ]);

                    return redirect(env('FRONTEND_URL', 'https://tidy-personal.web.app') . '/premium-success');
                }
            }

            return redirect(env('FRONTEND_URL', 'https://tidy-personal.web.app') . '/premium-error');
        } catch (\Exception $e) {
            Log::error('Error en subscriptionSuccess: ' . $e->getMessage());
            return redirect(env('FRONTEND_URL', 'https://tidy-personal.web.app') . '/premium-error');
        }
    }

    public function webhook(Request $request)
    {
        try {
            Log::info('Webhook MercadoPago recibido', $request->all());

            $type = $request->input('type');
            $data = $request->input('data');

            if ($type === 'subscription' && isset($data['id'])) {
                $this->handleSubscriptionWebhook($data['id']);
            }

            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('Error en webhook MercadoPago: ' . $e->getMessage());
            return response()->json(['error' => 'Error processing webhook'], 500);
        }
    }

    private function handleSubscriptionWebhook($subscriptionId)
    {
        $accessToken = env('MERCADOPAGO_ACCESS_TOKEN');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/preapproval/{$subscriptionId}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $subscription = json_decode($response, true);
            $externalReference = $subscription['external_reference'] ?? null;

            if ($externalReference && $subscription['status'] === 'authorized') {
                $user = User::find($externalReference);
                if ($user && $user->usuarioCuenta) {
                    $cuenta = $user->usuarioCuenta;
                    $cuenta->update([
                        'is_premium' => true,
                        'premium_expires_at' => Carbon::now()->addMonth(),
                        'mercadopago_subscription_id' => $subscriptionId
                    ]);

                    // Guardar registro de pago desde webhook
                    Payment::create([
                        'user_id' => $externalReference,
                        'payment_id' => $subscription['id'] ?? 'WH-' . uniqid(),
                        'collection_id' => $subscription['collector_id'] ?? null,
                        'subscription_id' => $subscriptionId,
                        'status' => 'approved',
                        'payment_type' => 'subscription',
                        'payment_method' => $subscription['payment_method_id'] ?? null,
                        'amount' => $subscription['auto_recurring']['transaction_amount'] ?? 0,
                        'currency' => $subscription['auto_recurring']['currency_id'] ?? 'ARS',
                        'plan_type' => 'monthly',
                        'description' => 'Suscripción Premium - Tidy (Webhook)',
                        'metadata' => json_encode($subscription),
                        'paid_at' => Carbon::now()
                    ]);

                    Log::info('Suscripción activada via webhook y pago registrado', [
                        'user_id' => $externalReference,
                        'subscription_id' => $subscriptionId
                    ]);
                }
            }
        }
    }

    public function createSubscriptionUrl($userId)
    {
        $planId = env('MERCADOPAGO_PREAPPROVAL_PLAN_ID');
        $baseUrl = "https://www.mercadopago.com.ar/subscriptions/checkout";
        $backendUrl = env('APP_URL', 'https://tidyback-production.up.railway.app');

        $params = [
            'preapproval_plan_id' => $planId,
            'external_reference' => $userId,
            'back_url' => $backendUrl . '/api/mercadopago/success'
        ];

        return $baseUrl . '?' . http_build_query($params);
    }
}
