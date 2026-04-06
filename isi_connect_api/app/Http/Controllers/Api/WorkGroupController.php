<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkGroup;
use App\Models\WorkGroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class WorkGroupController extends Controller
{
    /**
     * Get all work groups.
     */
    public function index()
    {
        $groups = WorkGroup::with(['creator:id,name', 'members.user:id,name', 'members.user.profile:user_id,profile_picture_url'])
                           ->withCount('members')
                           ->latest()
                           ->get();

        return response()->json($groups, 200);
    }

    /**
     * Create a new work group.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $groupData = [
            'creator_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
        ];

        if ($request->hasFile('image')) {
            // S'assurer que le dossier existe
            if (!Storage::disk('public')->exists('groups')) {
                Storage::disk('public')->makeDirectory('groups');
            }
            $path = $request->file('image')->store('groups', 'public');
            $groupData['image_path'] = $path;
        }

        $group = WorkGroup::create($groupData);

        // Creator is also a member/leader
        $group->members()->create([
            'user_id' => $request->user()->id,
            'role' => 'leader',
        ]);

        return response()->json([
            'message' => 'GROUPE CRÉÉ.',
            'group' => $group->load(['creator:id,name', 'members.user:id,name'])
        ], 201);
    }

    /**
     * Join a group.
     */
    public function join(Request $request, WorkGroup $workGroup)
    {
        $userId = $request->user()->id;
        
        // Already a member?
        if ($workGroup->members()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'DÉJÀ MEMBRE.'], 400);
        }

        $workGroup->members()->create([
            'user_id' => $userId,
            'role' => 'member',
        ]);

        return response()->json(['message' => 'HUB REJOINT.'], 200);
    }

    /**
     * Leave a group.
     */
    public function leave(Request $request, WorkGroup $workGroup)
    {
        $userId = $request->user()->id;
        
        if ($workGroup->creator_id === $userId) {
            return response()->json(['message' => 'Le créateur ne peut pas quitter le groupe.'], 400);
        }

        $workGroup->members()->where('user_id', $userId)->delete();

        return response()->json(['message' => 'DÉCONNEXION DU HUB.'], 200);
    }

    /**
     * Add a member to a group (Creator only).
     */
    public function addMember(Request $request, WorkGroup $workGroup)
    {
        // Seul le créateur peut ajouter directement d'autres membres
        if ($request->user()->id !== $workGroup->creator_id) {
            return response()->json(['message' => 'Protocole refusé : Seul le créateur peut ajouter des membres.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userId = $request->user_id;

        if ($workGroup->members()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'SYNCHRONISATION ÉCHOUÉE : DÉJÀ MEMBRE.'], 400);
        }

        $workGroup->members()->create([
            'user_id' => $userId,
            'role' => 'member',
        ]);

        return response()->json(['message' => 'MEMBRE AJOUTÉ À LA MATRICE DU GROUPE.'], 200);
    }

    /**
     * Delete a group.
     */
    public function destroy(Request $request, WorkGroup $workGroup)
    {
        if ($request->user()->id !== $workGroup->creator_id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        if ($workGroup->image_path) {
            Storage::disk('public')->delete($workGroup->image_path);
        }

        $workGroup->delete();
        return response()->json(['message' => 'HUB SUPPRIMÉ.'], 200);
    }
}
