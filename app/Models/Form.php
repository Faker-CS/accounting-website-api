<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\HelperForms;

class Form extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'service_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function documents()
    {
        return $this->hasMany(UserDocuments::class);
    }
    public function userDocuments()
    {
        return $this->hasMany(UserDocuments::class, 'form_id');
    }

    public function helperForms()
    {
        return $this->hasMany(HelperForms::class);
    }
    
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    
}