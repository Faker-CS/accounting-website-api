<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class HelperAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $demandeId;
    protected $comptableName;

    public function __construct($demandeId, $comptableName)
    {
        $this->demandeId = $demandeId;
        $this->comptableName = $comptableName;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'New Assignment',
            'message' => "You have been assigned to a demande by {$this->comptableName}",
            'type' => 'helper_assigned',
            'serviceLink' => "/demandes/{$this->demandeId}",
            'isUnRead' => true
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'New Assignment',
            'message' => "You have been assigned to a demande by {$this->comptableName}",
            'type' => 'helper_assigned',
            'serviceLink' => "/demandes/{$this->demandeId}",
            'isUnRead' => true
        ]);
    }
} 