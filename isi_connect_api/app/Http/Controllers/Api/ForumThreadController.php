<?php
// app/Http/Controllers/Api/ForumThreadController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumThread; // <-- Notre modèle
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ForumThreadController extends Controller
{
    /**
     * Afficher tous les sujets du forum.
     */
    public function index()
    {
        $threads = ForumThread::with('user:id,name') // Qui l'a créé
                                ->withCount('replies') // Idée créative : compter les réponses
                                ->latest() // Les plus récents en premier
                                ->paginate(15);

        return response()->json($threads, 200);
    }

    /**
     * Créer un nouveau sujet de discussion.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|min:2',
            'body' => 'required|string|min:2',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // On crée le sujet en l'associant à l'utilisateur connecté
        $threadData = [
            'title' => $request->title,
            'body' => $request->body,
        ];
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('forum', 'public');
            $threadData['image_path'] = $path;
        }

        $thread = $request->user()->forumThreads()->create($threadData);

        return response()->json([
            'message' => 'Sujet créé avec succès !',
            'thread' => $thread->load('user:id,name') // Renvoie le sujet avec l'auteur
        ], 201);
    }

    /**
     * Afficher un sujet spécifique ET toutes ses réponses.
     */
    public function show(ForumThread $forumThread)
    {
        // On charge le sujet, son auteur, ses réponses, et l'auteur de chaque réponse
        $forumThread->load([
    'user:id,name',             // Auteur du sujet (colonnes)
    'user.profile',             // Auteur du sujet (profil)
    'replies.user:id,name',     // Auteurs des réponses (colonnes)
    'replies.user.profile'      // Auteurs des réponses (profils)
]);

        return response()->json($forumThread, 200);
    }

    /**
     * Supprimer un sujet de discussion.
     */
    public function destroy(Request $request, ForumThread $forumThread)
    {
        // Sécurité : Seul l'auteur peut supprimer
        if ($request->user()->id !== $forumThread->user_id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $forumThread->delete();

        return response()->json(['message' => 'Sujet supprimé avec succès.'], 200);
    }
}