<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;
    protected $with = ['sender', 'receiver'];

    protected $fillable = [
        'conversation_id', 
        'sender_id',
        'body',
        'content_type',
        'seen',
        'attachment_path',
        'attachment_type',
    ];

    protected $casts = [
        'seen' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'attachment_path' => 'string',
        'attachment_type' => 'string'
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Scopes
    public function scopeInConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    public function scopeFromUser($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    public function scopeToUser($query, $userId)
    {
        return $query->where('receiver_id', $userId)
            ->orWhereNull('receiver_id');
    }

    public function scopeUnseen($query)
    {
        return $query->where('seen', false);
    }

    public function scopeBroadcast($query)
    {
        return $query->whereNull('receiver_id');
    }

    public function scopePrivate($query)
    {
        return $query->whereNotNull('receiver_id');
    }

    // Helper Methods
    public function isBroadcast()
    {
        return is_null($this->receiver_id);
    }

    public function isPrivate()
    {
        return !$this->isBroadcast();
    }

    public function markAsSeen()
    {
        $this->update(['seen' => true]);
    }

    public function intendedFor(User $user)
    {
        // Message is for this user if:
        // 1. It's a broadcast message (null receiver)
        // 2. Or specifically addressed to this user
        return $this->isBroadcast() || $this->receiver_id === $user->id;
    }

    public function isFrom(User $user)
    {
        return $this->sender_id === $user->id;
    }

    /**
     * Get the other participant in the conversation
     * (for private messages only)
     */
    public function getOtherParticipantAttribute()
    {
        if ($this->isBroadcast()) {
            return null;
        }

        return $this->sender_id === auth()->id()
            ? $this->receiver
            : $this->sender;
    }
}
