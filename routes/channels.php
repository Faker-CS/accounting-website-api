<?php
use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('conversation.{id}', function ($user, $id) {
    // Check if the user is part of the conversation

    return true; // Add your condition here
});
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    // \Log::debug('Channel authorization check', [
    //     'authenticated_user' => $user->id,
    //     'requested_channel' => $userId
    // ]);
    return (int) $user->id === (int) $userId;
});
