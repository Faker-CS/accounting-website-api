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

    /**
     * Relation participants (doit toujours retourner 2 utilisateurs en mode ONE_TO_ONE)
     */
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

    /**
     * Vérifie s'il existe une conversation ONE_TO_ONE entre deux utilisateurs
     */
    public static function findOneToOne($userId1, $userId2)
    {
        return self::where('type', 'ONE_TO_ONE')
            ->whereHas('participants', function ($query) use ($userId1) {
                $query->where('user_id', $userId1);
            })
            ->whereHas('participants', function ($query) use ($userId2) {
                $query->where('user_id', $userId2);
            })
            ->first();
    }
}
