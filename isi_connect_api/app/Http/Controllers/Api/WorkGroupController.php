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
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $groups = WorkGroup::with(['creator:id,name', 'members.user:id,name', 'members.user.profile:user_id,profile_picture_url'])
                           ->withCount('members')
                           ->latest()
                           ->get()
                           ->filter(function ($group) use ($userId) {
                               // Groupes publics visibles par tous, groupes privés visibles seulement par les membres
                               if (!$group->is_private) return true;
                               return $group->members->contains('user_id', $userId);
                           })
                           ->values();

        return response()->json($groups, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_private'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $groupData = [
            'creator_id'  => $request->user()->id,
            'name'        => $request->name,
            'description' => $request->description,
            'is_private'  => $request->boolean('is_private', false),
        ];

        if ($request->hasFile('image')) {
            if (!Storage::disk('public')->exists('groups')) {
                Storage::disk('public')->makeDirectory('groups');
            }
            $groupData['image_path'] = $request->file('image')->store('groups', 'public');
        }

        $group = WorkGroup::create($groupData);

        $group->members()->create([
            'user_id' => $request->user()->id,
            'role'    => 'leader',
        ]);

        return response()->json([
            'message' => 'GROUPE CRÉÉ.',
            'group'   => $group->load(['creator:id,name', 'members.user:id,name'])
        ], 201);
    }

    public function join(Request $request, WorkGroup $workGroup)
    {
        $userId = $request->user()->id;

        // Groupe privé : on ne peut pas rejoindre soi-même
        if ($workGroup->is_private) {
            return response()->json(['message' => 'Ce groupe est privé. Vous devez être invité par le créateur.'], 403);
        }

        if ($workGroup->members()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'DÉJÀ MEMBRE.'], 400);
        }

        $workGroup->members()->create(['user_id' => $userId, 'role' => 'member']);

        return response()->json(['message' => 'HUB REJOINT.'], 200);
    }

    public function leave(Request $request, WorkGroup $workGroup)
    {
        $userId = $request->user()->id;

        if ($workGroup->creator_id === $userId) {
            return response()->json(['message' => 'Le créateur ne peut pas quitter le groupe.'], 400);
        }

        $workGroup->members()->where('user_id', $userId)->delete();

        return response()->json(['message' => 'DÉCONNEXION DU HUB.'], 200);
    }

    public function addMember(Request $request, WorkGroup $workGroup)
    {
        if ($request->user()->id !== $workGroup->creator_id) {
            return response()->json(['message' => 'Seul le créateur peut ajouter des membres.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($workGroup->members()->where('user_id', $request->user_id)->exists()) {
            return response()->json(['message' => 'DÉJÀ MEMBRE.'], 400);
        }

        $workGroup->members()->create(['user_id' => $request->user_id, 'role' => 'member']);

        return response()->json(['message' => 'MEMBRE AJOUTÉ.'], 200);
    }

    public function removeMember(Request $request, WorkGroup $workGroup, $userId)
    {
        if ($request->user()->id !== $workGroup->creator_id) {
            return response()->json(['message' => 'Seul le créateur peut retirer des membres.'], 403);
        }

        if ((int)$userId === $workGroup->creator_id) {
            return response()->json(['message' => 'Impossible de retirer le créateur.'], 400);
        }

        $workGroup->members()->where('user_id', $userId)->delete();

        return response()->json(['message' => 'MEMBRE RETIRÉ.'], 200);
    }

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
