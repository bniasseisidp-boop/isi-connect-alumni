<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\WorkGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class MessengerController extends Controller
{
    /**
     * Get all conversations for the user (Private & Groups).
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        
        // 1. Direct Conversations
        $directConversations = Conversation::whereNull('work_group_id')
                                     ->where(function($q) use ($userId) {
                                         $q->where('user_one_id', $userId)
                                           ->orWhere('user_two_id', $userId);
                                     })
                                     ->with(['userOne:id,name', 'userOne.profile:user_id,profile_picture_url', 
                                             'userTwo:id,name', 'userTwo.profile:user_id,profile_picture_url', 
                                             'messages' => function($q) {
                                                 $q->latest()->limit(1);
                                             }])
                                     ->get();

        // 2. Group Conversations
        $groupConversations = Conversation::whereNotNull('work_group_id')
                                     ->whereIn('work_group_id', $request->user()->workGroups()->pluck('work_groups.id'))
                                     ->with(['workGroup:id,name,image_path', 'messages' => function($q) {
                                         $q->latest()->limit(1);
                                     }])
                                     ->get();

        return response()->json([
            'direct' => $directConversations,
            'groups' => $groupConversations
        ], 200);
    }

    /**
     * Get messages for a private conversation.
     */
    public function show(Request $request, $otherUserId)
    {
        $myId = $request->user()->id;
        
        $conversation = Conversation::whereNull('work_group_id')
            ->where(function($q) use ($myId, $otherUserId) {
                $q->where(function($sq) use ($myId, $otherUserId) {
                    $sq->where('user_one_id', $myId)->where('user_two_id', $otherUserId);
                })->orWhere(function($sq) use ($myId, $otherUserId) {
                    $sq->where('user_one_id', $otherUserId)->where('user_two_id', $myId);
                });
            })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => min($myId, $otherUserId),
                'user_two_id' => max($myId, $otherUserId)
            ]);
        }

        $messages = $conversation->messages()->with('sender:id,name,profile_picture_url')->oldest()->get();

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages
        ], 200);
    }

    /**
     * Get messages for a group conversation.
     */
    public function showGroup(Request $request, WorkGroup $workGroup)
    {
        // Security: must be a member
        if (!$workGroup->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Non membre du groupe.'], 403);
        }

        $conversation = Conversation::firstOrCreate(['work_group_id' => $workGroup->id]);
        $messages = $conversation->messages()->with('sender:id,name,profile_picture_url')->oldest()->get();

        return response()->json([
            'conversation' => $conversation,
            'group' => $workGroup,
            'messages' => $messages
        ], 200);
    }

    /**
     * Send a private message (supports files).
     */
    public function store(Request $request, $otherUserId)
    {
        $myId = $request->user()->id;
        
        $conversation = Conversation::whereNull('work_group_id')
            ->where(function($q) use ($myId, $otherUserId) {
                $q->where(function($sq) use ($myId, $otherUserId) {
                    $sq->where('user_one_id', $myId)->where('user_two_id', $otherUserId);
                })->orWhere(function($sq) use ($myId, $otherUserId) {
                    $sq->where('user_one_id', $otherUserId)->where('user_two_id', $myId);
                });
            })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => min($myId, $otherUserId),
                'user_two_id' => max($myId, $otherUserId)
            ]);
        }

        return $this->saveMessage($request, $conversation);
    }

    /**
     * Send a group message (supports files).
     */
    public function storeGroup(Request $request, WorkGroup $workGroup)
    {
        if (!$workGroup->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Non membre.'], 403);
        }

        $conversation = Conversation::firstOrCreate(['work_group_id' => $workGroup->id]);
        return $this->saveMessage($request, $conversation);
    }

    /**
     * Helper to save message with media support.
     */
    private function saveMessage(Request $request, Conversation $conversation)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'nullable|string',
            'type' => 'required|string|in:text,image,video,voice',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $msgData = [
            'sender_id' => $request->user()->id,
            'body' => $request->body,
            'type' => $request->type,
        ];

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('messenger', 'public');
            $msgData['file_path'] = $path;
        }

        $message = $conversation->messages()->create($msgData);

        return response()->json($message->load('sender:id,name'), 201);
    }
}
