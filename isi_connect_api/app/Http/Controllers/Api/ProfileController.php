<?php
// app/Http/Controllers/Api/ProfileController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; // Pour gérer les fichiers

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur authentifié.
     */
    public function show(Request $request)
    {
        // On récupère l'utilisateur authentifié grâce au token,
        // et on charge son profil (grâce à notre relation !)
        $user = $request->user()->load('profile');

        return response()->json([
            'user' => $user
        ], 200);
    }

    /**
     * Mettre à jour le profil de l'utilisateur authentifié.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile; // Récupère le profil via la relation

        // --- 1. Validation des données ---
        // On valide les champs du profil que l'utilisateur envoie
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255', // Champ de la table 'users'
            'bio' => 'nullable|string|max:1000',
            'job_title' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'is_visible' => 'sometimes|boolean',
            
            // On peut aussi valider l'email, mais c'est plus complexe
            // car il doit être unique.
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id), // Doit être unique, SAUF pour cet utilisateur
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation de la photo
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // --- 2. Mise à jour ---
        
        // On met à jour le 'name' et 'email' sur la table 'users'
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;
        $user->save();

        // On met à jour le reste sur la table 'profiles'
        if ($request->has('bio')) $profile->bio = $request->bio;
        if ($request->has('job_title')) $profile->job_title = $request->job_title;
        if ($request->has('company_name')) $profile->company_name = $request->company_name;
        if ($request->has('city')) $profile->city = $request->city;
        if ($request->has('linkedin_url')) $profile->linkedin_url = $request->linkedin_url;
        if ($request->has('is_visible')) $profile->is_visible = $request->is_visible;
        $profile->save();

        // --- 2.5 Gestion de l'image (si envoyée) ---
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($profile->profile_picture_url) {
                Storage::disk('public')->delete($profile->profile_picture_url);
            }

            // Sauvegarder la nouvelle
            $path = $request->file('image')->store('profiles', 'public');
            $profile->update(['profile_picture_url' => $path]);
        }

        // --- 3. Réponse ---
        // On renvoie l'utilisateur mis à jour avec son profil
        return response()->json([
            'message' => 'Profil mis à jour avec succès !',
            'user' => $user->fresh()->load('profile') // 'fresh()' pour re-charger
        ], 200);
    }
}