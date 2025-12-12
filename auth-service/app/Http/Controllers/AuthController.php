<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validation des données
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Récupérer l'utilisateur de la base partagée
        $user = User::where('email', $request->email)->first();

        // 3. Vérifier les credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis ne correspondent pas à nos enregistrements.'],
            ]);
        }

        // 4. Vérifier que l'utilisateur est actif
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Votre compte est désactivé.'],
            ]);
        }

        // 5. Supprimer les anciens tokens et en créer un nouveau
        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'phone_number' => $user->phone_number,
                'sex' => $user->sex,
                'date_of_birth' => $user->date_of_birth,
                'avatar' => $user->avatar,
                'is_active' => $user->is_active,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'phone_number' => $user->phone_number,
                'sex' => $user->sex,
                'date_of_birth' => $user->date_of_birth,
                'avatar' => $user->avatar,
                'is_active' => $user->is_active,
            ]
        ]);
    }
}
