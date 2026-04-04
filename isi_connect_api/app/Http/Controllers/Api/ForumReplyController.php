<?php
// app/Http/Controllers/Api/ForumReplyController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumThread; // On a besoin de savoir à quel sujet on répond
use App\Models\ForumReply; // Et du modèle de réponse
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForumReplyController extends Controller
{
    /**
     * Stocker une nouvelle réponse pour un sujet donné.
     */
    public function store(Request $request, ForumThread $forumThread)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // On crée la réponse en l'associant au sujet (via l'URL)
        // et à l'utilisateur (via le token)
        $reply = $forumThread->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        // On renvoie la nouvelle réponse avec les infos de son auteur
        return response()->json([
            'message' => 'Réponse ajoutée avec succès !',
            'reply' => $reply->load('user:id,name', 'user.profile') // <-- LIGNE CORRIGÉE
        ], 201);
    }

    /**
     * Supprimer une réponse.
     */
    public function destroy(Request $request, ForumReply $forumReply)
    {
        // Sécurité : Seul l'auteur de la réponse peut la supprimer
        if ($request->user()->id !== $forumReply->user_id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $forumReply->delete();

        return response()->json(['message' => 'Réponse supprimée avec succès.'], 200);
    }
}