<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessengerController extends Controller
{
    /**
     * Get all conversations for the user.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        
        $conversations = Conversation::where('user_one_id', $userId)
                                     ->orWhere('user_two_id', $userId)
                                     ->with(['userOne:id,name', 'userOne.profile:user_id,profile_picture_url', 'userTwo:id,name', 'userTwo.profile:user_id,profile_picture_url', 'messages' => function($q) {
                                         $q->latest()->limit(1);
                                     }])
                                     ->get();

        return response()->json($conversations, 200);
    }

    /**
     * Get messages for a specific conversation or user.
     */
    public function show(Request $request, $otherUserId)
    {
        $myId = $request->user()->id;
        
        // Trouver ou créer la conversation
        $conversation = Conversation::where(function($q) use ($myId, $otherUserId) {
            $q->where('user_one_id', $myId)->where('user_two_id', $otherUserId);
        })->orWhere(function($q) use ($myId, $otherUserId) {
            $q->where('user_one_id', $otherUserId)->where('user_two_id', $myId);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => min($myId, $otherUserId),
                'user_two_id' => max($myId, $otherUserId)
            ]);
        }

        $messages = $conversation->messages()->with('sender:id,name')->oldest()->get();

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages
        ], 200);
    }

    /**
     * Send a message.
     */
    public function store(Request $request, $otherUserId)
    {
        $myId = $request->user()->id;
        
        $validator = Validator::make($request->all(), [
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Trouver la conversation
        $conversation = Conversation::where(function($q) use ($myId, $otherUserId) {
            $q->where('user_one_id', $myId)->where('user_two_id', $otherUserId);
        })->orWhere(function($q) use ($myId, $otherUserId) {
            $q->where('user_one_id', $otherUserId)->where('user_two_id', $myId);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => min($myId, $otherUserId),
                'user_two_id' => max($myId, $otherUserId)
            ]);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $myId,
            'body' => $request->body,
        ]);

        return response()->json($message->load('sender:id,name'), 201);
    }
}
