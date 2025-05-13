<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Transaction;
use App\Services\WompiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $wompiService;

    public function __construct(WompiService $wompiService)
    {
        $this->wompiService = $wompiService;
    }

    public function payService(Request $request, Service $service)
    {
        $user = $request->user();
        $reference = 'SERV-' . Str::random(10);
        
        try {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'amount' => $service->price,
                'reference' => $reference,
                'customer_email' => $user->email,
                'status' => 'pending',
            ]);
            
            $tokenResponse = $this->wompiService->createCardToken([
                'card_number' => $request->card_number,
                'cvc' => $request->cvc,
                'exp_month' => $request->exp_month,
                'exp_year' => $request->exp_year,
                'card_holder' => $request->card_holder,
            ]);
            
            $wompiResponse = $this->wompiService->createTransaction([
                'amount' => $service->price,
                'customer_email' => $user->email,
                'reference' => $reference,
                'token' => $tokenResponse['data']['id'],
                'installments' => $request->installments ?? 1,
            ]);
            
            $transaction->update([
                'wompi_id' => $wompiResponse['data']['id'],
                'status' => $wompiResponse['data']['status'],
                'payment_method' => json_encode($wompiResponse['data']['payment_method']),
                'wompi_response' => json_encode($wompiResponse),
            ]);
            
            return response()->json([
                'success' => true,
                'transaction' => $transaction,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}