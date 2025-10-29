<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        // 2. Appel du Core Service pour validation et récupération du profil (communication inter-services)
        // Utilisation du nom du conteneur Docker: core-service-core-service-app-1
        $response = Http::post('http://core-service-core-service-app-1:80/api/validate-credentials', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        // 3. Vérification de la réponse du Core Service
        if ($response->failed() || $response->status() !== 200) {
            // Renvoie une erreur de validation si l'appel échoue (mauvais identifiants ou service non disponible)
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis ne correspondent pas à nos enregistrements, ou le service de vérification est indisponible.'],
            ]);
        }

        // Les données de profil vérifiées (incluant l'ID, le rôle, etc.)
        $userData = $response->json();

        // 4. Récupération ou création de l'utilisateur LOCAL (dans db_auth)
        // Nous avons besoin de cette entrée locale pour générer le token Sanctum.
        // On cherche par ID (qui est la clé partagée).
        $user = User::where('id', $userData['id'])->first();

        if (!$user) {
             // Si l'utilisateur Core est nouveau et n'existe pas localement (Auth), on le crée
             // ATTENTION: Le mot de passe ici est un placeholder car Auth ne doit PAS le connaître.
             $user = User::create([
                 'id' => $userData['id'],
                 'email' => $userData['email'],
                 'password' => 'NOPASS',
             ]);
        }

        // 5. Suppression des anciens jetons et génération du nouveau jeton Sanctum
        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        // 6. Réponse : renvoie le token et les données de profil COMPLÈTES du Core Service
        return response()->json([
            'token' => $token,
            'user' => $userData, // Données de profil complètes (nom, rôle, téléphone, etc.)
        ], 200);
    }
}
