<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'type',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    // Relationships
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

    // Helper method to check if user is in the conversation
    public function hasUser($userId)
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }
}
