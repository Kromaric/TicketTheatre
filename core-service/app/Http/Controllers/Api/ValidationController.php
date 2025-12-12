<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ValidationController extends Controller
{
    // Cette route est appelée par le Auth Service (via HTTP)
    public function validateCredentials(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // 1. Vérification du mot de passe
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // 2. Succès : renvoie toutes les données de profil (Source de vérité)
        return response()->json([
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
        ], 200);
    }
}
