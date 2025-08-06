<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;

    protected $guard_name = 'api';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phoneNumber',
        'city',
        'state',
        'address',
        'zipCode',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'phoneNumber' => 'string',
    ];

    // JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }


    // Relation to conversations
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')
            ->withTimestamps();
    }
    
    // Relation to companies (aide comptable entreprises)
    public function companies()
    {
        return $this->hasMany(CompanyUser::class);
    }

    //gerant d'entreprise
    public function company(){
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function helperForms()
    {
        return $this->hasMany(HelperForms::class);
    }
    
    public function forms()
    {
        return $this->hasMany(Form::class);
    }
}