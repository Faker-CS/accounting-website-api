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
use App\Models\HelperForms;
use App\Models\Form;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify user is part of the conversation
        $conversation = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($request->conversation_id);

        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => $user->id,
            'body' => $request->body,
            'seen' => false,
        ]);

        // Load the sender relationship for the broadcast
        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message,
            'conversation' => $conversation
        ]);
    }

    public function getConversations()
    {
        $user = Auth::user();
        $conversations = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with([
                'participants',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            ->latest()
            ->get();

        // Format the response to match frontend expectations
        $formattedConversations = [
            'allIds' => $conversations->pluck('id')->toArray(),
            'byId' => $conversations->mapWithKeys(function ($conversation) {
                return [$conversation->id => [
                    'id' => $conversation->id,
                    'type' => $conversation->type,
                    'participants' => $conversation->participants->map(function ($participant) {
                        return [
                            'id' => $participant->id,
                            'name' => $participant->name,
                            'avatarUrl' => $participant->photoURL,
                            'role' => $participant->role,
                            'status' => 'online'
                        ];
                    })->toArray(),
                    'messages' => $conversation->messages->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'body' => $message->body,
                            'createdAt' => $message->created_at,
                            'senderId' => $message->sender_id,
                            'seen' => $message->seen
                        ];
                    })->toArray(),
                    'unreadCount' => $conversation->messages->where('seen', false)->count()
                ]];
            })->toArray()
        ];

        return response()->json($formattedConversations, 200);
    }

    public function getConversationById($id)
    {
        $user = Auth::id();
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
        $user = Auth::user();
        \Log::info('Getting contacts for user:', ['user_id' => $user->id, 'roles' => $user->getRoleNames()]);
        
        $contacts = [];

        // Get contacts based on user role
        if ($user->hasRole('comptable')) {
            // Comptable can chat with all aide-comptables and companies
            $contacts = User::role(['aide-comptable', 'entreprise'])->get();
            \Log::info('Comptable contacts:', ['count' => $contacts->count()]);
        } elseif ($user->hasRole('aide-comptable')) {
            // Aide-comptable can chat with their responsible companies and main comptable
            $formIds = HelperForms::where('user_id', $user->id)->pluck('form_id');
            $responsibleCompanies = User::role('entreprise')
                ->whereHas('forms', function($query) use ($formIds) {
                    $query->whereIn('id', $formIds);
                })
                ->get();
            $mainComptable = User::role('comptable')->first();
            
            $contacts = collect([$mainComptable])
                ->merge($responsibleCompanies)
                ->filter();
            \Log::info('Aide-comptable contacts:', [
                'responsible_companies' => $responsibleCompanies->count(),
                'main_comptable' => $mainComptable ? 'found' : 'not found',
                'total' => $contacts->count()
            ]);
        } elseif ($user->hasRole('entreprise')) {
            // Company can only chat with their assigned aide-comptable and main comptable
            $formIds = Form::where('user_id', $user->id)->pluck('id');
            $assignedAideComptable = User::role('aide-comptable')
                ->whereHas('helperForms', function($query) use ($formIds) {
                    $query->whereIn('form_id', $formIds);
                })
                ->first();
            $mainComptable = User::role('comptable')->first();
            
            $contacts = collect([$assignedAideComptable, $mainComptable])
                ->filter();
            \Log::info('Entreprise contacts:', [
                'assigned_aide_comptable' => $assignedAideComptable ? 'found' : 'not found',
                'main_comptable' => $mainComptable ? 'found' : 'not found',
                'total' => $contacts->count()
            ]);
        }

        // Format contacts for frontend
        $formattedContacts = $contacts->map(function ($contact) {
            $role = $contact->roles->first();
            $photoUrl = $contact->photo ? asset('storage/' . $contact->photo) : null;
            
            $formattedContact = [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'avatarUrl' => $photoUrl,
                'role' => $role ? $role->name : 'no role',
                'status' => 'online',
                'phoneNumber' => $contact->phoneNumber,
                'lastActivity' => now(),
            ];

            \Log::info('Formatted contact:', $formattedContact);
            return $formattedContact;
        });

        \Log::info('Final formatted contacts:', ['count' => $formattedContacts->count()]);
        
        return response()->json([
            'contacts' => $formattedContacts
        ]);
    }

    public function createConversation(Request $request)
    {
        $user = Auth::user();

        // Debug log incoming request
        \Log::info('createConversation request', [
            'recipient_ids' => $request->input('recipient_ids', []),
            'recipient_id' => $request->input('recipient_id'),
            'user_id' => $user->id,
        ]);

        // Accepter uniquement recipient_id (ONE_TO_ONE)
        $recipientId = $request->input('recipient_id');
        if (!$recipientId) {
            return response()->json(['error' => 'Vous devez sélectionner un destinataire.'], 422);
        }
        if ($recipientId == $user->id) {
            return response()->json(['error' => 'Vous ne pouvez pas discuter avec vous-même.'], 422);
        }

        // Vérifier si une conversation ONE_TO_ONE existe déjà
        $existingConversation = Conversation::where('type', 'ONE_TO_ONE')
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereHas('participants', function ($query) use ($recipientId) {
                $query->where('user_id', $recipientId);
            })
            ->with(['participants', 'messages'])
            ->first();

        if ($existingConversation) {
            return response()->json(['conversation' => $existingConversation]);
        }

        // Create new conversation
        $conversation = Conversation::create([
            'type' => 'ONE_TO_ONE',
            'name' => null,
        ]);
        $conversation->participants()->attach([$user->id, $recipientId]);

        // If a message is provided, create it
        $message = null;
        if ($request->filled('message')) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'body' => $request->message,
                'seen' => false,
            ]);
        }

        // Load participants and messages for the response
        $conversation->load(['participants', 'messages']);

        return response()->json([
            'conversation' => $conversation,
            'message' => $message,
        ]);
    }

    private function validateChatPermission($user, $recipient)
    {
        if ($user->hasRole('comptable')) {
            // Comptable can chat with all aide-comptables and companies
            return $recipient->hasRole('aide-comptable') || $recipient->hasRole('entreprise');
        }

        if ($user->hasRole('aide-comptable')) {
            // Aide-comptable can chat with their responsible companies and main comptable
            if ($recipient->hasRole('comptable')) {
                return true;
            }
            if ($recipient->hasRole('entreprise')) {
                return $recipient->forms()
                    ->whereHas('helperForms', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->exists();
            }
            return false;
        }

        if ($user->hasRole('entreprise')) {
            // Company can only chat with their assigned aide-comptable and main comptable
            if ($recipient->hasRole('comptable')) {
                return true;
            }
            if ($recipient->hasRole('aide-comptable')) {
                return $user->forms()
                    ->whereHas('helperForms', function($query) use ($recipient) {
                        $query->where('user_id', $recipient->id);
                    })
                    ->exists();
            }
            return false;
        }

        return false;
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

    /**
     * Add a participant to a group conversation (admin only)
     */
    public function addParticipant(Request $request, $conversationId)
    {
        return response()->json(['error' => 'Ajout de participants non supporté en mode ONE_TO_ONE.'], 403);
    }
    public function removeParticipant(Request $request, $conversationId)
    {
        return response()->json(['error' => 'Suppression de participants non supportée en mode ONE_TO_ONE.'], 403);
    }
    public function updateReadReceipt($conversationId)
    {
        return response()->json(['error' => 'Read receipt non supporté en mode ONE_TO_ONE.'], 403);
    }
    public function getReadReceipts($conversationId)
    {
        return response()->json(['error' => 'Read receipt non supporté en mode ONE_TO_ONE.'], 403);
    }

    /**
     * Send a message with optional file attachment
     */
    public function sendMessageWithAttachment(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
            'body' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $conversation = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($request->conversation_id);
        $attachmentPath = null;
        $attachmentType = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('uploads/chat', 'public');
            $attachmentType = $file->getClientMimeType();
        }
        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => $user->id,
            'body' => $request->body,
            'seen' => false,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
        ]);
        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();
        return response()->json([
            'message' => $message,
            'conversation' => $conversation
        ]);
    }
}
