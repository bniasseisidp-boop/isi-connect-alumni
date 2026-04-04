<?php
// app/Http/Controllers/Api/EventController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event; // <-- Utiliser notre modèle Event
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; // Pour la validation des dates

class EventController extends Controller
{
    /**
     * Afficher tous les événements.
     * (On affiche d'abord les événements à venir)
     */
    public function index()
    {
        $events = Event::with('user:id,name') // Optimisation
                        ->where('starts_at', '>=', Carbon::now()) // Seulement les événements futurs
                        ->orderBy('starts_at', 'asc') // Du plus proche au plus lointain
                        ->paginate(10);

        return response()->json($events, 200);
    }

    /**
     * Créer un nouvel événement.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'starts_at' => 'required|date|after:now', // Doit être dans le futur
            'ends_at' => 'nullable|date|after:starts_at', // Doit être après le début
            'cover_image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // On crée l'événement en l'associant à l'utilisateur connecté
        $event = $request->user()->events()->create($validator->validated());

        return response()->json([
            'message' => 'Événement créé avec succès !',
            'event' => $event
        ], 201);
    }

    /**
     * Afficher un événement spécifique.
     */
    public function show(Event $event)
    {
        // On charge l'événement avec les détails de l'auteur
        return response()->json($event->load('user:id,name'), 200);
    }

    /**
     * Supprimer un événement.
     * (Seul l'auteur ou un admin peut le faire)
     */
    public function destroy(Request $request, Event $event)
    {
        // Sécurité : Seul l'auteur peut supprimer
        if ($request->user()->id !== $event->user_id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Événement supprimé avec succès.'], 200);
    }
}