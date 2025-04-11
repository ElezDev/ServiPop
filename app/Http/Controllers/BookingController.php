<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'service_id' => 'required|exists:services,id',
        'scheduled_at' => 'required|date',
        'address' => 'required|string|max:255',
        'notes' => 'nullable|string',
    ]);

    $service = Service::with('serviceProvider')->findOrFail($validated['service_id']);
    $durationInMinutes = $this->parseServiceDuration($service->duration);
    $startTime = now()->parse($validated['scheduled_at']);
    $endTime = $startTime->copy()->addMinutes($durationInMinutes);

    $this->validateProviderAvailability($service->service_provider_id, $startTime, $endTime);

    $booking = Booking::create([
        'user_id' => Auth::id(),
        'service_id' => $validated['service_id'],
        'provider_id' => $service->service_provider_id,
        'status' => 'pending',
        'scheduled_at' => $validated['scheduled_at'],
        'end_at' => $endTime,
        'duration' => $durationInMinutes,
        'price' => $service->price,
        'payment_status' => 'pending',
        'address' => $validated['address'],
        'notes' => $validated['notes'] ?? null,
    ]);

    $providerUser = User::findOrFail($service->serviceProvider->user_id);
    $this->sendBookingNotification($booking, $providerUser);

    return response()->json([
        'message' => 'Booking creado exitosamente',
        'data' => $booking
    ], 201);
}
    
    protected function sendBookingNotification(Booking $booking,  $provider)
    {

        $notification = $provider->notifications()->create([
            'sender_id' => Auth::id(),
            'booking_id' => $booking->id,
            'type' => 'booking_created',
            'title' => 'Nueva reserva recibida',
            'message' => 'Tienes una nueva reserva para el servicio: ' . $booking->service->title,
        ]);

    
        if ($provider->device_token) {
            $fcmData = [
                'title' => 'Nueva reserva recibida',
                'body' => 'Tienes una nueva reserva para el servicio: ' . $booking->service->title,
                'data' => [
                    'type' => 'booking_created',
                    'booking_id' => $booking->id,
                    'notification_id' => $notification->id,
                ]
            ];
    
            FCMService::send(
                $fcmData['title'],
                $fcmData['body'],
                $provider->device_token,
            );
        }
    }

    protected function parseServiceDuration($duration)
    {
        if (is_numeric($duration)) {
            return (int) $duration;
        }

        if (preg_match('/(\d+)\s*(hora|hrs?)/i', $duration, $matches)) {
            return (int) $matches[1] * 60;
        }

        if (preg_match('/(\d+)\s*(minuto|mins?)/i', $duration, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/(\d+)h\s*(\d+)min/i', $duration, $matches)) {
            return (int) $matches[1] * 60 + (int) $matches[2];
        }

        throw new \Exception("Formato de duración no reconocido: " . $duration);
    }


    protected function validateProviderAvailability($providerId, $startTime, $endTime)
    {
        $conflictingBookings = Booking::where('provider_id', $providerId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('scheduled_at', [$startTime, $endTime->copy()->subMinute()])
                    ->orWhereBetween('end_at', [$startTime->copy()->addMinute(), $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('scheduled_at', '<', $startTime)
                            ->where('end_at', '>', $endTime);
                    });
            })
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        if ($conflictingBookings) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'El proveedor ya tiene un servicio programado en este horario'
            ]);
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
