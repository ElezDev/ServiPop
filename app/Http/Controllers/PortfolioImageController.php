<?php

namespace App\Http\Controllers;

use App\Models\PortfolioImage;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortfolioImageController extends Controller
{
    public function index(ServiceProvider $serviceProvider)
    {
        return $serviceProvider->portfolioImages;
    }

    public function store(Request $request, ServiceProvider $serviceProvider)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
            'description' => 'nullable|string',
        ]);

        // Guardar la imagen en el almacenamiento
        $imagePath = $request->file('image')->store('portfolio_images', 'public');

        // Crear el registro en la base de datos
        $portfolioImage = $serviceProvider->portfolioImages()->create([
            'image_url' => Storage::url($imagePath),
            'description' => $request->input('description'),
        ]);

        return $portfolioImage;
    }

    public function show(PortfolioImage $portfolioImage)
    {
        return $portfolioImage;
    }

    public function destroy(PortfolioImage $portfolioImage)
    {
        // Eliminar la imagen del almacenamiento
        Storage::disk('public')->delete($portfolioImage->image_url);

        // Eliminar el registro de la base de datos
        $portfolioImage->delete();

        return response()->noContent();
    }
}