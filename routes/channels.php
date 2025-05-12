<?php
use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('conversation.{id}', function ($user, $id) {
    // Check if the user is part of the conversation
    
    return true; // Add your condition here
});

