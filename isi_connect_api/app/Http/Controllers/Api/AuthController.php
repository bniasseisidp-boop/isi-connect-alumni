<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Enregistrer un nouvel utilisateur (Alumni).
     */
    public function register(Request $request)
    {
        // --- 1. Validation des données reçues ---
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'promotion_year' => 'required|integer|min:1980|max:' . date('Y'),
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Renvoie les erreurs
        }

        // --- 2. Création de l'utilisateur ---
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Cryptage !
            'promotion_year' => $request->promotion_year,
            'role' => 'alumni', // Rôle par défaut
        ]);

        // --- 3. Création du profil vide associé ---
        $user->profile()->create([
            'bio' => 'Bienvenue sur ISI-Connect ! Mettez à jour votre profil.',
            'is_visible' => true,
        ]);

        // --- 4. Réponse ---
        return response()->json([
            'message' => 'Utilisateur enregistré avec succès !',
            'user' => $user->load('profile')
        ], 201);
    } // <-- C'EST L'ACCOLADE QUI MANQUAIT PROBABLEMENT

    /**
     * Connecter un utilisateur et renvoyer un token API.
     */
    public function login(Request $request)
    {
        // --- 1. Validation ---
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // --- 2. Tentative de connexion ---
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Identifiants invalides'
            ], 401);
        }

        // --- 3. C'est correct ! On crée le token ---
        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        // --- 4. On renvoie la réponse ---
        return response()->json([
            'message' => 'Connexion réussie !',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'must_change_password' => (bool) $user->must_change_password,
            'user' => $user->load('profile')
        ], 200);
    }

    /**
     * Mettre à jour le mot de passe (forcé ou volontaire).
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->save();

        return response()->json([
            'message' => 'Mot de passe mis à jour avec succès.',
            'must_change_password' => false
        ]);
    }


    /**
     * Mot de passe oublié : Générer un code temporaire et envoyer par email.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Génération d'un code temporaire aléatoire
        $tempPassword = Str::random(8);
        
        // Mise à jour de l'utilisateur
        $user->password = Hash::make($tempPassword);
        $user->must_change_password = true;
        $user->save();

        // Envoi de l'email via API Brevo (plus fiable que SMTP sur VPS)
        try {
            $htmlContent = view('emails.password-reset', [
                'name' => $user->name, 
                'email' => $user->email, 
                'password' => $tempPassword
            ])->render();

            $response = Http::withHeaders([
                'api-key' => env('BREVO_API_KEY'),
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => env('MAIL_FROM_NAME', 'ISI Suptech Alumni'),
                    'email' => env('MAIL_FROM_ADDRESS')
                ],
                'to' => [
                    ['email' => $user->email, 'name' => $user->name]
                ],
                'subject' => 'Récupération de votre compte ISI Connect',
                'htmlContent' => $htmlContent
            ]);

            if (!$response->successful()) {
                throw new \Exception('Brevo API error: ' . $response->body());
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail sending failed via API: ' . $e->getMessage());
            return response()->json([
                'message' => 'L\'email n\'a pas pu être envoyé. Détail technique : ' . $e->getMessage(),
                'error' => 'mail_failed'
            ], 500);
        }

        return response()->json([
            'message' => 'Code de récupération envoyé par email.'
        ]);
    }


    public function logout(Request $request)
    {
        // Supprime le token que l'utilisateur a utilisé 
        // pour s'authentifier
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie avec succès !'
        ], 200);
    }

} // <-- Accolade de fin de la classe