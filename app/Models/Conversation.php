<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'user_one_id',
        'user_two_id',
    ];

    protected $casts = [
        'type' => 'string', // Cast for enum
    ];

    // Relationships
    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }
    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }


    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_user')
                    ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // Helper methods to get other participants
    public function getOtherParticipant($userId)
    {
        if ($this->type === 'user-user') {
            return $this->user_one_id == $userId ? $this->userTwo : $this->userOne;
        } else {
            return $this->company;
        }
    }

    // Helper method to check if user is in the conversation
    public function hasUser($userId)
    {
        return $this->user_one_id == $userId || $this->user_two_id == $userId;
    }
}
