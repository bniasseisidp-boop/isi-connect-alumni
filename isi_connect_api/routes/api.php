<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AlumniDirectoryController;
use App\Http\Controllers\Api\JobPostingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ForumThreadController;
use App\Http\Controllers\Api\ForumReplyController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\MessengerController;
use App\Http\Controllers\Api\WorkGroupController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\VideoCallController;


/*
|--------------------------------------------------------------------------
| Routes Publiques
|--------------------------------------------------------------------------
*/

// Authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// Vitrine / Showcase
Route::get('/public/showcase-alumni', [AlumniDirectoryController::class, 'showcase']);

// --- CORRECTION DE L'ORDRE ---
// La route statique (la liste) DOIT être déclarée AVANT la route dynamique (le détail).

Route::group(['middleware' => ['auth:sanctum']], function () {
    // La liste de l'annuaire (DOIT ÊTRE PROTÉGÉE - Mon erreur précédente)
    // Route::get('/alumni', [AlumniDirectoryController::class, 'index']); // On la déplace (wait, in the file it's further down)
    
    // Toggle vitrine
    Route::put('/alumni/{user}/feature', [AlumniDirectoryController::class, 'toggleFeature']);
});

// Le profil public (celui-ci est public)
Route::get('/alumni/{user}', [AlumniDirectoryController::class, 'show']);
// --- FIN DE LA CORRECTION ---


// Offres d'emploi
Route::get('/job-postings', [JobPostingController::class, 'index']);
Route::get('/job-postings/{jobPosting}', [JobPostingController::class, 'show']);

// Événements
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

// Forum (Lecture seule)
Route::get('/forum-threads', [ForumThreadController::class, 'index']);
Route::get('/forum-threads/{forumThread}', [ForumThreadController::class, 'show']);


/*
|--------------------------------------------------------------------------
| Routes Protégées (Nécessitent un Token)
|--------------------------------------------------------------------------
*/
// Stats publiques du dashboard
Route::get('/stats', function () {
    return response()->json([
        'users'    => \App\Models\User::count(),
        'jobs'     => \App\Models\JobPosting::count(),
        'threads'  => \App\Models\ForumThread::count(),
    ]);
});

Route::middleware('auth:sanctum')->group(function () {

    // Heartbeat pour la présence en ligne (met à jour last_seen_at via TrackLastSeen middleware)
    Route::get('/ping', fn() => response()->json(['ok' => true]));

    // --- Auth & Profil ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/user/update-password', [AuthController::class, 'updatePassword']);


    Route::get('/user', fn(Request $request) => $request->user()->load('profile'));
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // --- Annuaire ---
    // La liste complète reste protégée (seuls les membres peuvent la voir)
    // ON LA REPLACE ICI
    Route::get('/alumni', [AlumniDirectoryController::class, 'index']);

    // --- Offres d'emploi (écriture) ---
    Route::post('/job-postings', [JobPostingController::class, 'store']);
    Route::delete('/job-postings/{jobPosting}', [JobPostingController::class, 'destroy']);

    // --- Événements (écriture) ---
    Route::post('/events', [EventController::class, 'store']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
    
    // --- Forum (écriture) ---
    Route::post('/forum-threads', [ForumThreadController::class, 'store']);
    Route::delete('/forum-threads/{forumThread}', [ForumThreadController::class, 'destroy']);
    Route::post('/forum-threads/{forumThread}/replies', [ForumReplyController::class, 'store']);
    Route::delete('/forum-replies/{forumReply}', [ForumReplyController::class, 'destroy']);

    // --- Social / Timeline ---
    Route::get('/posts', [SocialController::class, 'index']);
    Route::post('/posts', [SocialController::class, 'store']);
    Route::delete('/posts/{post}', [SocialController::class, 'destroy']);
    Route::post('/posts/{post}/comment', [SocialController::class, 'comment']);
    Route::post('/posts/{post}/like', [SocialController::class, 'toggleLike']);
    Route::get('/users/search', [SocialController::class, 'searchUsers']);

    // --- Messagerie / Chat ---
    Route::get('/messenger', [MessengerController::class, 'index']);
    Route::get('/messenger/{user}', [MessengerController::class, 'show']);
    Route::post('/messenger/{user}', [MessengerController::class, 'store']); 
    Route::get('/messenger/group/{workGroup}', [MessengerController::class, 'showGroup']);
    Route::post('/messenger/group/{workGroup}', [MessengerController::class, 'storeGroup']);

    // --- Groupes de Travail ---
    Route::get('/work-groups', [WorkGroupController::class, 'index']);
    Route::post('/work-groups', [WorkGroupController::class, 'store']);
    Route::post('/work-groups/{workGroup}/join', [WorkGroupController::class, 'join']);
    Route::post('/work-groups/{workGroup}/members', [WorkGroupController::class, 'addMember']);
    Route::delete('/work-groups/{workGroup}/members/{userId}', [WorkGroupController::class, 'removeMember']);
    Route::post('/work-groups/{workGroup}/leave', [WorkGroupController::class, 'leave']);
    Route::delete('/work-groups/{workGroup}', [WorkGroupController::class, 'destroy']);

    // --- Appels Vidéo WebRTC ---
    Route::get('/video-calls/incoming', [VideoCallController::class, 'incoming']);
    Route::post('/video-calls', [VideoCallController::class, 'initiate']);
    Route::get('/video-calls/{roomId}', [VideoCallController::class, 'show']);
    Route::post('/video-calls/{roomId}/answer', [VideoCallController::class, 'answer']);
    Route::post('/video-calls/{roomId}/ice', [VideoCallController::class, 'addIce']);
    Route::get('/video-calls/{roomId}/ice', [VideoCallController::class, 'getIce']);
    Route::post('/video-calls/{roomId}/end', [VideoCallController::class, 'end']);

    // --- Administration ---
    Route::get('/admin/users', [AdminController::class, 'indexUsers']);
    Route::put('/admin/users/{user}/role', [AdminController::class, 'updateRole']);
    Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser']);
    
    // Modération Admin
    Route::delete('/admin/jobs/{jobPosting}', [AdminController::class, 'deleteJob']);
    Route::delete('/admin/events/{event}', [AdminController::class, 'deleteEvent']);
    Route::delete('/admin/forum-threads/{forumThread}', [AdminController::class, 'deleteThread']);
    Route::delete('/admin/posts/{post}', [AdminController::class, 'deletePost']);

    // Invitations & Imports
    Route::post('/admin/invite', [AdminController::class, 'inviteMember']);
    Route::post('/admin/import', [AdminController::class, 'importMembers']);

    // Statistiques
    Route::get('/admin/stats', [AdminController::class, 'stats']);
});

// ROUTE D'URGENCE TEMPORAIRE POUR RESET LE MOT DE PASSE ADMIN
Route::get('/emergency-reset', function () {
    \App\Models\User::where('email', 'multibrainmusic1@gmail.com')->update(['password' => bcrypt('admin123')]);
    return response()->json(['message' => 'Mot de passe administrateur réinitialisé à: admin123']);
});
