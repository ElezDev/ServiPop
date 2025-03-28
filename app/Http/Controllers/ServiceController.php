<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceProvider;
use App\Models\PortfolioImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        return Service::with('categories','portfolioImages')->get();
    }



    public function store(Request $request)
    {
        $serviceProvider = ServiceProvider::where('user_id', Auth::id())->first();
    
        if (!$serviceProvider) {
            return response()->json([
                'message' => 'El usuario no está registrado como proveedor de servicios'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'portfolio_images' => 'nullable|max:4',
            'portfolio_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $serviceData = $request->except(['categories', 'portfolio_images']);
        $serviceData['service_provider_id'] = $serviceProvider->id;
    
        $service = Service::create($serviceData);
    
        if ($request->has('categories')) {
            $service->categories()->attach($request->input('categories'));
        }
    
        if ($request->hasFile('portfolio_images')) {
            $currentDate = now()->format('Y-m-d'); 
            
            foreach ($request->file('portfolio_images') as $image) {
                $imagePath = $image->store('portfolio_images', 'public');
                $autoDescription = "Servicio: {$service->title} - {$currentDate}";
                PortfolioImage::create([
                    'service_provider_id' => $serviceProvider->id,
                    'service_id' => $service->id,
                    'image_url' => Storage::url($imagePath),
                    'description' => $autoDescription,
                ]);
            }
        }
    
        return $service->load('categories', 'portfolioImages');
    }

    public function update(Request $request, Service $service)
    {
        $serviceProvider = ServiceProvider::where('user_id', Auth::id())->first();
    
        if (!$serviceProvider || $service->service_provider_id != $serviceProvider->id) {
            return response()->json([
                'message' => 'No tienes permiso para modificar este servicio'
            ], 403);
        }
    
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'duration' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'portfolio_images' => 'nullable|array|max:4',
            'portfolio_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            // Eliminamos la validación de image_descriptions
        ]);
    
        $service->update($request->except(['categories', 'portfolio_images']));
    
        if ($request->has('categories')) {
            $service->categories()->sync($request->input('categories'));
        }
    
        // Procesar nuevas imágenes de portafolio
        if ($request->hasFile('portfolio_images')) {
            $currentDate = now()->format('Y-m-d');
            
            foreach ($request->file('portfolio_images') as $image) {
                $imagePath = $image->store('portfolio_images', 'public');
                
                $autoDescription = "Servicio: {$service->title} - {$currentDate}";
                
                PortfolioImage::create([
                    'service_provider_id' => $serviceProvider->id,
                    'service_id' => $service->id,
                    'image_url' => Storage::url($imagePath),
                    'description' => $autoDescription,
                ]);
            }
        }
    
        return $service->load('categories', 'portfolioImages');
    }

    public function destroy(Service $service)
    {
        // Verificar que el servicio pertenece al proveedor del usuario autenticado
        $serviceProvider = ServiceProvider::where('user_id', Auth::id())->first();

        if (!$serviceProvider || $service->service_provider_id != $serviceProvider->id) {
            return response()->json([
                'message' => 'No tienes permiso para eliminar este servicio'
            ], 403);
        }

        // Eliminar imágenes de portafolio asociadas (el onDelete cascade en la BD ya se encarga)
        $service->delete();
        return response()->noContent();
    }
}