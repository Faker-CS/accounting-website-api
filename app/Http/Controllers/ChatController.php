<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {

        $request->validate([
            'body' => 'nullable|string',
        ]);

        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => $user->id ?? 1,
            'body' => $request->body,
            'seen' => false,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }

    public function getConversations()
    {
        $user = Auth::user();
        \Log::info('User ID: ', [$user]);
        $conversations = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with([
                'participants',
                'company',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->latest()
            ->get();

        return response()->json($conversations, 200);
    }

    public function getConversationById($id)
    {
        $user = Auth::id()?:1;
        \Log::info('User ID: ', [$user]);

        $conversation = Conversation::where('id', $id)
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->with(['participants', 'messages']) // include sender info
            ->first();

        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        return response()->json($conversation, 200);
    }

    public function getContacts($id)
    {
        $user = User::find($id);

        if ($user->hasRole('comptable')) {
            $aideComptables = User::whereHas('roles', function ($query) {
                $query->where('name', 'aide-comptable');
            })->get();
            $companies = Company::all();
            $contacts = $aideComptables->concat($companies);
        } elseif ($user->hasRole('aide-comptable')) {
            $contacts = Company::where('responsible_id', $user->id)->get();
        } else {
            $contacts = collect([$user->responsible]);
        }

        return response()->json([
            'contacts' => $contacts
        ]);
    }

    public function createConversation(Request $request)
    {
        // \Log::info('Request Data: ', $request->all());
        $requestData = $request->input('conversationData', $request->all());
        $validated = Validator::make($requestData, [
            'type' => 'nullable|in:ONE_TO_ONE,GROUP',
            'participants' => 'required|array|min:1',
            'participants.*.id' => 'required|exists:users,id',
            'messages' => 'required|array|min:1',
            'messages.0.senderId' => 'required|exists:users,id',
            'messages.0.body' => 'nullable|string', 
            'company_id' => 'nullable|exists:companies,id'
        ])->validate();

        $participantIds = array_map(function ($participant) {
            return (int) $participant['id'];
        }, $validated['participants']);

        $currentUserId = Auth::id()?:1;
        


        // Ensure current user is included
        if (!in_array($currentUserId, $participantIds)) {
            $participantIds[] = $currentUserId;
        }

        sort($participantIds);

        // If type is ONE_TO_ONE, prevent duplicate conversations
        if ($validated['type'] === 'ONE_TO_ONE') {
            $existing = Conversation::where('type', 'user-user')
                ->whereHas('participants', function ($query) use ($participantIds) {
                    $query->whereIn('user_id', $participantIds);
                }, '=', count($participantIds))
                ->withCount('participants')
                ->get()
                ->first(function ($conv) use ($participantIds) {
                    return $conv->participants_count === count($participantIds);
                });

            if ($existing) {
                return response()->json($existing->load(['participants','messages']), 200);
            }
        }

        // Create new conversation
        $conversation = Conversation::create([
            'type' => $validated['type'] === 'GROUP' ? 'group' : 'user-user',
            'company_id' => $validated['company_id'] ?? null
        ]);

        // Attach participants
        $userIds = collect($validated['participants'])->pluck('id')->toArray();
        $conversation->participants()->attach(array_unique([...$userIds, $currentUserId]));

        \Log::info('Conversation Created: ', [$conversation->id]);


        // save first message
        if (!empty($validated['messages'])) {
            $messageData = $validated['messages'][0];
            \Log::info('Message Data: ', [$messageData]);
            $conversation->messages()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $messageData['senderId'] ?? $currentUserId,
                'body' => $messageData['body'],
                'content_type' => $messageData['contentType'] ?? 'text'
            ]);
        }
        \Log::info('Validation Errors: ', $requestData->errors()->toArray());

        return response()->json($conversation->load(['participants', 'company', 'messages']), 201);
    }




    public function markAsSeen($conversationId)
    {
        $user = Auth::user();

        $conversation = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($conversationId);

        $conversation->messages()
            ->where('receiver_id', $user->id)
            ->update(['seen' => true]);

        return response()->json([
            'message' => 'Conversation marked as seen.'
        ]);
    }
}
