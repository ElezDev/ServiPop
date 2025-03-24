<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
 
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'lastname' => 'nullable|string|max:255',
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
    
          
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $imagePath = $request->file('avatar')->store('avatars', 'public');
                $avatarPath = '/storage/' . $imagePath;
            }
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'avatar' => $avatarPath, 
                'phone' => $request->phone,
                'address' => $request->address,
                'lastname' => $request->lastname,
            ]);
    
    
    
            $token = JWTAuth::fromUser($user);
    
            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Error de base de datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error en la base de datos al crear el usuario y la persona'], 500);
        } catch (\Exception $e) {
            Log::error('Error al crear la persona: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo crear el usuario y la persona'], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $credentials = $request->only('email', 'password');
    
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
    
        if (!auth()->check()) {
            return response()->json(['error' => 'user_not_authenticated'], 401);
        }
    
        $expiresIn = JWTAuth::factory()->getTTL() * 60; 
    
        $refreshToken = JWTAuth::attempt($credentials, [
            'token_type' => 'refresh',
            'ttl' => config('jwt.refresh_ttl'),
        ]);
    
        return response()->json([
            'token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => $expiresIn,
        ]);
    }

    public function refreshToken(Request $request)
    {
        try {
            $refreshToken = $request->input('refresh_token');
    
            $newToken = JWTAuth::setToken($refreshToken)->refresh();
    
            return response()->json([
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_refresh_token', 'message' => $e->getMessage()], 500);
        }
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function user()
    {
        return response()->json(auth()->user());
    }
}