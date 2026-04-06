<?php
// app/Http/Controllers/Api/JobPostingController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting; // <-- Utiliser notre nouveau modèle
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class JobPostingController extends Controller
{
    /**
     * Afficher toutes les offres d'emploi.
     * (Tout le monde peut voir les offres)
     */
    public function index()
    {
        // On charge les offres, de la plus récente à la plus ancienne,
        // avec les infos de l'utilisateur qui l'a postée.
        $jobs = JobPosting::with('user:id,name') // Optimisation: ne charge que l'ID et le nom
                            ->latest() // 'latest()' trie par 'created_at' DESC
                            ->paginate(10); // 10 par page

        return response()->json($jobs, 200);
    }

    /**
     * Créer une nouvelle offre d'emploi.
     * (Seul un utilisateur connecté peut le faire)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'type' => 'required|string|in:CDI,Stage,Freelance,CDD,Alternance', // Types autorisés
            'apply_url' => 'nullable|string|max:255',
            'apply_email' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // On crée l'offre en l'associant à l'utilisateur connecté
        // On prépare les données (sans l'objet File)
        $jobData = [
            'title' => $request->title,
            'company_name' => $request->company_name,
            'description' => $request->description,
            'location' => $request->location,
            'type' => $request->type,
            'apply_url' => $request->apply_url,
            'apply_email' => $request->apply_email,
        ];
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('jobs', 'public');
            $jobData['image_path'] = $path;
        }

        $job = $request->user()->jobPostings()->create($jobData);

        // Note: jobPostings() n'existe pas encore sur le modèle User.
        // Nous allons l'ajouter à l'étape suivante !

        return response()->json([
            'message' => 'Offre créée avec succès !',
            'job' => $job
        ], 201);
    }

    /**
     * Afficher une offre d'emploi spécifique.
     */
    public function show(JobPosting $jobPosting)
    {
        // On charge l'offre avec les détails de l'auteur
        return response()->json($jobPosting->load('user.profile'), 200);
    }

    /**
     * Supprimer une offre d'emploi.
     */
    public function destroy(Request $request, JobPosting $jobPosting)
    {
        // --- Sécurité : Vérification ---
        // L'utilisateur connecté est-il l'auteur de l'offre ?
        // (Nous ajouterons un rôle 'admin' plus tard)
        if ($request->user()->id !== $jobPosting->user_id) {
            return response()->json(['message' => 'Action non autorisée.'], 403); // 403 = Forbidden
        }

        $jobPosting->delete();

        return response()->json(['message' => 'Offre supprimée avec succès.'], 200);
    }
}