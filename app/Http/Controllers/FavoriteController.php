<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the user's favorite services.
     */
    public function index()
    {
        $favorites = Auth::user()->favorites()
            ->with(['service', 'service.serviceProvider.user',])
            ->orderBy('is_checked', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($favorites);
    }

    /**
     * Store a newly created favorite in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id'
        ]);

        $user = Auth::user();

        // Verificar si ya existe el favorito
        if ($user->favorites()->where('service_id', $request->service_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Este servicio ya está en tus favoritos'
            ], 409);
        }

        $favorite = $user->favorites()->create([
            'service_id' => $request->service_id,
            'is_checked' => $request->input('is_checked', false) // Opcional: permitir marcar al crear
        ]);

        return response()->json([
            'success' => true,
            'data' => $favorite->load('service'),
            'message' => 'Servicio agregado a favoritos'
        ], 201);
    }

    /**
     * Display the specified favorite.
     */
    public function show(Favorite $favorite)
    {
        // Verificar que el favorito pertenece al usuario
        if ($favorite->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $favorite->load(['service', 'service.serviceProvider'])
        ]);
    }

    /**
     * Remove the specified favorite from storage.
     */
    public function destroy(Favorite $favorite)
    {
        // Verificar que el favorito pertenece al usuario
        if ($favorite->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Favorito eliminado correctamente'
        ]);
    }

    /**
     * Mark favorite as checked
     */
    public function markAsChecked(Favorite $favorite)
    {
        if ($favorite->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $favorite->markAsChecked();

        return response()->json([
            'success' => true,
            'is_checked' => true,
            'message' => 'Favorito marcado como seleccionado'
        ]);
    }

    /**
     * Unmark favorite
     */
    public function unmarkAsChecked(Favorite $favorite)
    {
        if ($favorite->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $favorite->unmarkAsChecked();

        return response()->json([
            'success' => true,
            'is_checked' => false,
            'message' => 'Favorito desmarcado'
        ]);
    }

    /**
     * Toggle checked status
     */
    public function toggleChecked(Favorite $favorite)
    {
        if ($favorite->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $favorite->toggleChecked();

        return response()->json([
            'success' => true,
            'is_checked' => $favorite->is_checked,
            'message' => 'Estado de favorito actualizado'
        ]);
    }

    /**
     * Get only checked favorites
     */
    public function checkedFavorites()
    {
        $favorites = Auth::user()->favorites()
            ->checked()
            ->with(['service', 'service.serviceProvider'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    /**
     * Check if a service is favorite
     */
    public function checkFavorite($serviceId)
    {
        $favorite = Auth::user()->favorites()
            ->where('service_id', $serviceId)
            ->first();

        return response()->json([
            'is_favorite' => !is_null($favorite),
            'is_checked' => $favorite ? $favorite->is_checked : false,
            'favorite_id' => $favorite ? $favorite->id : null
        ]);
    }

    /**
     * Bulk update checked status
     */
    public function bulkUpdateCheckedStatus(Request $request)
    {
        $request->validate([
            'favorites' => 'required|array',
            'favorites.*.id' => 'required|exists:favorites,id,user_id,' . Auth::id(),
            'favorites.*.is_checked' => 'required|boolean'
        ]);

        foreach ($request->favorites as $fav) {
            Favorite::where('id', $fav['id'])
                ->where('user_id', Auth::id())
                ->update(['is_checked' => $fav['is_checked']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estados actualizados correctamente'
        ]);
    }
}