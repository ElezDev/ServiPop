<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return Service::with('categories')->get();
    }

    public function show(Service $service)
    {
        return $service->load('categories');
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_provider_id' => 'required|exists:service_providers,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'nullable|string',
            'categories' => 'nullable|array', // IDs de las categorías
            'categories.*' => 'exists:categories,id',
        ]);

        $service = Service::create($request->except('categories'));

        if ($request->has('categories')) {
            $service->categories()->attach($request->input('categories'));
        }

        return $service->load('categories');
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'service_provider_id' => 'sometimes|exists:service_providers,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'duration' => 'nullable|string',
            'categories' => 'nullable|array', 
            'categories.*' => 'exists:categories,id',
        ]);

        $service->update($request->except('categories'));

        if ($request->has('categories')) {
            $service->categories()->sync($request->input('categories'));
        }

        return $service->load('categories');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return response()->noContent();
    }
}