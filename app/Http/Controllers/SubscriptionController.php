<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\WompiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    protected $wompiService;

    public function __construct(WompiService $wompiService)
    {
        $this->wompiService = $wompiService;
    }

    public function subscribe(Request $request, Subscription $subscription)
    {
        $user = $request->user();
        $reference = 'SUB-' . Str::random(10);
        
        try {
            // Verificar si el proveedor ya tiene más de 2 servicios
            $serviceCount = $user->service_provider->services()->count();
            if ($serviceCount <= 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'No necesitas suscripción para menos de 3 servicios',
                ], 400);
            }
            
            // Crear token de tarjeta en Wompi
            $tokenResponse = $this->wompiService->createCardToken([
                'card_number' => $request->card_number,
                'cvc' => $request->cvc,
                'exp_month' => $request->exp_month,
                'exp_year' => $request->exp_year,
                'card_holder' => $request->card_holder,
            ]);
            
            // Crear transacción en Wompi
            $wompiResponse = $this->wompiService->createTransaction([
                'amount' => $subscription->price,
                'customer_email' => $user->email,
                'reference' => $reference,
                'token' => $tokenResponse['data']['id'],
                'installments' => $request->installments ?? 1,
            ]);
            
            // Crear suscripción en la base de datos
            $userSubscription = $user->subscriptions()->create([
                'subscription_id' => $subscription->id,
                'wompi_id' => $wompiResponse['data']['id'],
                'status' => 'active',
                'ends_at' => now()->addMonth(), // o según el billing_cycle
            ]);
            
            return response()->json([
                'success' => true,
                'subscription' => $userSubscription,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}