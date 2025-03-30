<?php

namespace App\Http\Controllers;

use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceProviders = ServiceProvider::with('user')->get();
        return response()->json($serviceProviders);
    }

   /**
 * Store a newly created resource in storage.
 */
public function store(Request $request)
{
    // Verificar si el usuario ya tiene un proveedor de servicios registrado
    if (ServiceProvider::where('user_id', Auth::id())->exists()) {
        return response()->json([
            'message' => 'Ya eres proveedor de servicios',
            'error' => 'User can only register once as a service provider'
        ], 409); // 409 Conflict es apropiado para este caso
    }

    $validator = Validator::make($request->all(), [
        'service_type' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'address' => 'nullable|string|max:255',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'rating' => 'nullable|numeric|between:0,5'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $data = $validator->validated();
    $data['user_id'] = Auth::id();

    $serviceProvider = ServiceProvider::create($data);

    return response()->json([
        'message' => 'Service provider created successfully',
        'data' => $serviceProvider
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $serviceProvider = ServiceProvider::with('user')->find($id);
        
        if (!$serviceProvider) {
            return response()->json(['message' => 'Service provider not found'], 404);
        }

        return response()->json($serviceProvider);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $serviceProvider = ServiceProvider::find($id);
        
        if (!$serviceProvider) {
            return response()->json(['message' => 'Service provider not found'], 404);
        }

        // Verificar que el usuario autenticado es el dueño del registro
        if ($serviceProvider->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'service_type' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'rating' => 'nullable|numeric|between:0,5'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $serviceProvider->update($validator->validated());

        return response()->json([
            'message' => 'Service provider updated successfully',
            'data' => $serviceProvider
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $serviceProvider = ServiceProvider::find($id);
        
        if (!$serviceProvider) {
            return response()->json(['message' => 'Service provider not found'], 404);
        }

        // Verificar que el usuario autenticado es el dueño del registro
        if ($serviceProvider->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $serviceProvider->delete();

        return response()->json(['message' => 'Service provider deleted successfully']);
    }
}