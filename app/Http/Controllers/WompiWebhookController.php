<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class WompiWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $event = $request->event;
        $data = $request->data;
        
        if ($event === 'transaction.updated') {
            $transaction = Transaction::where('wompi_id', $data['id'])->first();
            
            if ($transaction) {
                $transaction->update([
                    'status' => $data['status'],
                    'wompi_response' => json_encode($data),
                ]);
                
                // Aquí puedes agregar lógica adicional según el estado
                if ($data['status'] === 'APPROVED') {
                    // Pago aprobado, activar servicio, etc.
                }
            }
        }
        
        return response()->json(['success' => true]);
    }
}