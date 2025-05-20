<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelperForms extends Model
{
    protected $table = "helper_forms";
    protected $fillable = [
        'user_id',
        'form_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
