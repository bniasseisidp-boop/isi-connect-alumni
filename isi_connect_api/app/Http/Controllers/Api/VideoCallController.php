<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VideoCall;
use App\Models\CallIceCandidate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoCallController extends Controller
{
    // Initiates a call
    public function initiate(Request $request)
    {
        $request->validate([
            'callee_id'     => 'nullable|exists:users,id',
            'work_group_id' => 'nullable|exists:work_groups,id',
            'offer'         => 'required|string',
        ]);

        // End any active call from this user
        VideoCall::where('caller_id', $request->user()->id)
                 ->whereIn('status', ['waiting', 'active'])
                 ->update(['status' => 'ended']);

        $call = VideoCall::create([
            'caller_id'     => $request->user()->id,
            'callee_id'     => $request->callee_id,
            'work_group_id' => $request->work_group_id,
            'room_id'       => Str::uuid(),
            'status'        => 'waiting',
            'offer'         => $request->offer,
        ]);

        return response()->json(['call' => $call->load('caller:id,name')], 201);
    }

    // Get call status (callee polls this)
    public function show(string $roomId)
    {
        $call = VideoCall::where('room_id', $roomId)
                         ->with(['caller:id,name', 'callee:id,name'])
                         ->firstOrFail();

        return response()->json(['call' => $call]);
    }

    // Accept and send answer
    public function answer(Request $request, string $roomId)
    {
        $request->validate(['answer' => 'required|string']);

        $call = VideoCall::where('room_id', $roomId)->firstOrFail();

        // Only callee (or group member) can answer
        if ($call->callee_id && $call->callee_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $call->update([
            'answer' => $request->answer,
            'status' => 'active',
            'callee_id' => $call->callee_id ?? $request->user()->id,
        ]);

        return response()->json(['call' => $call]);
    }

    // Add ICE candidate
    public function addIce(Request $request, string $roomId)
    {
        $request->validate(['candidate' => 'required|string']);

        $call = VideoCall::where('room_id', $roomId)->firstOrFail();

        $ice = CallIceCandidate::create([
            'call_id'   => $call->id,
            'user_id'   => $request->user()->id,
            'candidate' => $request->candidate,
        ]);

        return response()->json(['ice' => $ice], 201);
    }

    // Get ICE candidates from the OTHER party
    public function getIce(Request $request, string $roomId)
    {
        $call = VideoCall::where('room_id', $roomId)->firstOrFail();

        $candidates = CallIceCandidate::where('call_id', $call->id)
                                      ->where('user_id', '!=', $request->user()->id)
                                      ->get();

        return response()->json(['candidates' => $candidates]);
    }

    // End call
    public function end(string $roomId, Request $request)
    {
        $call = VideoCall::where('room_id', $roomId)->firstOrFail();
        $call->update(['status' => 'ended']);
        return response()->json(['message' => 'Appel terminé.']);
    }

    // Get incoming call for current user
    public function incoming(Request $request)
    {
        $call = VideoCall::where('callee_id', $request->user()->id)
                         ->where('status', 'waiting')
                         ->with('caller:id,name')
                         ->latest()
                         ->first();

        return response()->json(['call' => $call]);
    }
}
