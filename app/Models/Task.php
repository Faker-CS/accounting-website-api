<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'form_id',
        'title',
        'description',
        'reporter_id',
        'assignee_id',
        'due_date',
        'priority',
        'status'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function formHelpers()
    {
        return $this->form->helperForms->map->user;
    }
}
