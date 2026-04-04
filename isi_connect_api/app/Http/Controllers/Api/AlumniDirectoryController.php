<?php
// app/Http/Controllers/Api/AlumniDirectoryController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // <-- On a besoin du modèle User

class AlumniDirectoryController extends Controller
{
    /**
     * Afficher l'annuaire des anciens élèves.
     */
    public function index(Request $request)
    {
        // On veut les utilisateurs qui ont le rôle 'alumni'
        // ET dont le profil est visible
        $query = User::where('role', 'alumni')
                    ->whereHas('profile', function ($q) {
                        $q->where('is_visible', true);
                    });

        // --- Bonus Créatif : Ajout de Filtres ---
        // Si l'utilisateur ajoute ?promotion=2022 à l'URL
        if ($request->has('promotion')) {
            $query->where('promotion_year', $request->promotion);
        }

        // Si l'utilisateur ajoute ?search=Moussa à l'URL
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('profile', function ($profileQuery) use ($searchTerm) {
                      $profileQuery->where('job_title', 'like', '%' . $searchTerm . '%')
                                   ->orWhere('company_name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        // On charge les profils associés et on pagine les résultats
        // (ex: 15 résultats par page)
        $alumni = $query->with('profile')->paginate(15);

        return response()->json($alumni, 200);
    }

    public function show(string $id)
    {
        try {
            // On cherche l'utilisateur par son ID
            $user = User::with('profile') // On charge son profil
                        ->where('role', 'alumni')
                        ->findOrFail($id); // 'findOrFail' renvoie une erreur 404 si non trouvé

            // On vérifie si son profil est visible
            if (!$user->profile || !$user->profile->is_visible) {
                return response()->json(['message' => 'Profil non trouvé ou privé.'], 404);
            }

            return response()->json($user, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }
    }

    /**
     * Obtenir des profils aléatoires pour la page d'accueil (vitrine).
     */
    public function showcase()
    {
        $alumni = User::where('role', 'alumni')
            ->whereHas('profile', function ($q) {
                // Must be both visible to directory and featured in showcase
                $q->where('is_visible', true)->where('is_featured_in_showcase', true);
            })
            ->with('profile')
            ->inRandomOrder()
            ->take(4)
            ->get();

        return response()->json($alumni, 200);
    }

    /**
     * Admin uniquement : Met en vitrine ou retire un profil de la vitrine.
     */
    public function toggleFeature(Request $request, string $id)
    {
        // On s'assure que seul un profil admin peut effectuer l'action
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Action non autorisée. Réservé aux administrateurs.'], 403);
        }

        try {
            $user = User::with('profile')->findOrFail($id);

            if (!$user->profile) {
                return response()->json(['message' => "L'utilisateur n'a pas de profil."], 404);
            }

            // Basculer l'état
            $user->profile->is_featured_in_showcase = !$user->profile->is_featured_in_showcase;
            $user->profile->save();

            return response()->json([
                'message' => $user->profile->is_featured_in_showcase ? 'Profil ajouté à la vitrine.' : 'Profil retiré de la vitrine.',
                'is_featured' => $user->profile->is_featured_in_showcase
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }
    }
}