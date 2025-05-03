<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WompiService
{
    protected $baseUrl;
    protected $publicKey;
    protected $privateKey;

    public function __construct()
    {
        $isSandbox = config('app.wompi_env') === 'sandbox';
        
        $this->baseUrl = $isSandbox 
            ? 'https://sandbox.wompi.co/v1' 
            : 'https://production.wompi.co/v1';
            
        $this->publicKey = $isSandbox
            ? config('app.wompi_sandbox_public_key')
            : config('app.wompi_production_public_key');
            
        $this->privateKey = $isSandbox
            ? config('app.wompi_sandbox_private_key')
            : config('app.wompi_production_private_key');
    }

    public function getAcceptanceToken()
    {
        $response = Http::get("{$this->baseUrl}/merchants/{$this->publicKey}");
        
        if ($response->successful()) {
            return $response->json()['data']['presigned_acceptance']['acceptance_token'];
        }
        
        throw new \Exception("Error getting Wompi acceptance token: " . $response->body());
    }

    public function createTransaction(array $data)
    {
        $acceptanceToken = $this->getAcceptanceToken();
        
        $payload = [
            'amount_in_cents' => $data['amount'] * 100,
            'currency' => 'COP',
            'customer_email' => $data['customer_email'],
            'reference' => $data['reference'],
            'acceptance_token' => $acceptanceToken,
            'payment_method' => [
                'type' => 'CARD',
                'token' => $data['token'],
                'installments' => $data['installments'] ?? 1,
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->privateKey}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/transactions", $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Error creating Wompi transaction: " . $response->body());
    }

    public function getTransactionStatus($transactionId)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->privateKey}",
        ])->get("{$this->baseUrl}/transactions/{$transactionId}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Error getting Wompi transaction status: " . $response->body());
    }

    public function createCardToken(array $cardData)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->publicKey}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/tokens/cards", [
            'number' => $cardData['card_number'],
            'cvc' => $cardData['cvc'],
            'exp_month' => $cardData['exp_month'],
            'exp_year' => $cardData['exp_year'],
            'card_holder' => $cardData['card_holder'],
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Error creating Wompi card token: " . $response->body());
    }
}