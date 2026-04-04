<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SocialController extends Controller
{
    /**
     * Get the social timeline.
     */
    public function index()
    {
        $posts = Post::with(['user:id,name', 'user.profile:user_id,profile_picture_url', 'comments.user:id,name', 'comments.user.profile:user_id,profile_picture_url'])
                     ->withCount('likes')
                     ->latest()
                     ->paginate(15);

        return response()->json($posts, 200);
    }

    /**
     * Create a new social post.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'nullable|string|max:5000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $postData = [
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $postData['image_path'] = $path;
        }

        $post = Post::create($postData);

        return response()->json([
            'message' => 'PUBLICATION RÉUSSIE.',
            'post' => $post->load(['user:id,name', 'user.profile:user_id,profile_picture_url'])
        ], 201);
    }

    /**
     * Comment on a post.
     */
    public function comment(Request $request, Post $post)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        return response()->json([
            'message' => 'COMMENTAIRE DÉPLOYÉ.',
            'comment' => $comment->load(['user:id,name', 'user.profile:user_id,profile_picture_url'])
        ], 201);
    }

    /**
     * Like or Unlike a post.
     */
    public function toggleLike(Request $request, Post $post)
    {
        $userId = $request->user()->id;
        $like = $post->likes()->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            return response()->json(['message' => 'LIKE RETIRÉ.', 'liked' => false], 200);
        }

        $post->likes()->create(['user_id' => $userId]);
        return response()->json(['message' => 'POST ADORÉ.', 'liked' => true], 200);
    }

    /**
     * Delete a post.
     */
    public function destroy(Request $request, Post $post)
    {
        if ($request->user()->id !== $post->user_id) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();
        return response()->json(['message' => 'PUBLICATION SUPPRIMÉE.'], 200);
    }

    /**
     * Search for users to add to a group.
     */
    public function searchUsers(Request $request)
    {
        $query = $request->query('query');
        if (!$query || strlen($query) < 2) {
            return response()->json([], 200);
        }

        $users = \App\Models\User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->with('profile:user_id,profile_picture_url')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users, 200);
    }
}
