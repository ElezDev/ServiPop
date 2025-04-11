<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Actualiza el token del dispositivo para notificaciones push
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string|max:300'
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        try {
            $user->device_token = $request->device_token;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Token de dispositivo actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el token: ' . $e->getMessage()
            ], 500);
        }
    }
}