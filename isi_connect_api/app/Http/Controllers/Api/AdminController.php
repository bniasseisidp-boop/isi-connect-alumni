<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\JobPosting;
use App\Models\Event;
use App\Models\ForumThread;
use App\Models\ForumReply;
use App\Models\Post;
use App\Models\WorkGroup;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserInvited;
use Illuminate\Support\Str;


class AdminController extends Controller
{
    /**
     * Middleware check: ensures the user is an admin.
     * We'll call this in the constructor or just check in each method.
     */
    private function checkAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Action non autorisée. Réservé aux administrateurs.');
        }
    }

    /**
     * Liste tous les utilisateurs de la plateforme.
     */
    public function indexUsers(Request $request)
    {
        $this->checkAdmin($request);

        $query = User::with('profile');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Mettre à jour le rôle d'un utilisateur.
     */
    public function updateRole(Request $request, User $user)
    {
        $this->checkAdmin($request);

        $request->validate([
            'role' => 'required|in:admin,alumni,student'
        ]);

        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'Rôle mis à jour avec succès.', 'user' => $user]);
    }

    /**
     * Supprimer un utilisateur.
     */
    public function deleteUser(Request $request, User $user)
    {
        $this->checkAdmin($request);

        // On évite que l'admin se supprime lui-même par erreur
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte admin.'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    }

    /**
     * Modération : Supprimer une offre d'emploi.
     */
    public function deleteJob(Request $request, JobPosting $jobPosting)
    {
        $this->checkAdmin($request);
        $jobPosting->delete();
        return response()->json(['message' => 'L\'offre a été supprimée par l\'administrateur.']);
    }

    /**
     * Modération : Supprimer un événement.
     */
    public function deleteEvent(Request $request, Event $event)
    {
        $this->checkAdmin($request);
        $event->delete();
        return response()->json(['message' => 'L\'événement a été supprimé par l\'administrateur.']);
    }

    /**
     * Modération : Supprimer un sujet du forum.
     */
    public function deleteThread(Request $request, ForumThread $forumThread)
    {
        $this->checkAdmin($request);
        $forumThread->delete();
        return response()->json(['message' => 'Le sujet a été supprimé par l\'administrateur.']);
    }

    /**
     * Modération : Supprimer un post social.
     */
    public function deletePost(Request $request, Post $post)
    {
        $this->checkAdmin($request);
        $post->delete();
        return response()->json(['message' => 'Le post a été supprimé par l\'administrateur.']);
    }

    /**
     * Inviter un membre individuellement.
     */
    public function inviteMember(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'promotion_year' => 'required|integer|min:1980',
            'work_group_id' => 'nullable|exists:work_groups,id'
        ]);

        $defaultPassword = 'ISI-' . $request->promotion_year . '-' . Str::random(6);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($defaultPassword),
            'promotion_year' => $request->promotion_year,
            'role' => 'alumni',
            'must_change_password' => true,
        ]);

        $user->profile()->create([
            'bio' => 'Nouvel utilisateur invité par l\'administration.',
            'is_visible' => true,
        ]);

        if ($request->work_group_id) {
            $user->workGroups()->attach($request->work_group_id, ['role' => 'member']);
        }

        // Email (Captured in log)
        Mail::to($user->email)->send(new UserInvited($user->name, $user->email, $defaultPassword));

        return response()->json([
            'message' => 'Invitation envoyée avec succès à ' . $user->email,
            'user' => $user->load('profile')
        ]);
    }

    /**
     * Importer des membres en masse via CSV.
     */
    public function importMembers(Request $request)
    {
        $this->checkAdmin($request);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'work_group_id' => 'nullable|exists:work_groups,id'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header - using semicolon delimiter
        fgetcsv($handle, 0, ';');

        $importedCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {

            // Expected format: email, name, promotion_year
            if (count($row) < 3) continue;

            $email = trim($row[0]);
            $name = trim($row[1]);
            $promo = trim($row[2]);

            if (User::where('email', $email)->exists()) {
                $errors[] = "L'email $email existe déjà.";
                continue;
            }

            $defaultPassword = 'ISI-Connect-' . Str::random(6);

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($defaultPassword),
                'promotion_year' => $promo,
                'role' => 'alumni',
                'must_change_password' => true,
            ]);

            $user->profile()->create([
                'bio' => 'Utilisateur importé en masse.',
                'is_visible' => true,
            ]);

            if ($request->work_group_id) {
                $user->workGroups()->attach($request->work_group_id, ['role' => 'member']);
            }

            Mail::to($user->email)->send(new UserInvited($user->name, $user->email, $defaultPassword));
            $importedCount++;
        }

        fclose($handle);

        return response()->json([
            'message' => "$importedCount membres importés avec succès.",
            'errors' => $errors
        ]);
    }

    /**
     * Statistiques globales pour le dashboard admin.

     */
    public function stats(Request $request)
    {
        $this->checkAdmin($request);

        return response()->json([
            'users_total' => User::count(),
            'users_alumni' => User::where('role', 'alumni')->count(),
            'users_admins' => User::where('role', 'admin')->count(),
            'jobs_total' => JobPosting::count(),
            'events_total' => Event::count(),
            'threads_total' => ForumThread::count(),
            'posts_total' => Post::count(),
        ]);
    }
}
