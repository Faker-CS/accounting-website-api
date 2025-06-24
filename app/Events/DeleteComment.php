<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class DeleteComment implements ShouldBroadcast
{
    use SerializesModels;

    public $commentId;
    public $taskId;

    /**
     * Create a new event instance.
     *
     * @param int $commentId
     * @param int $taskId
     */
    public function __construct($commentId, $taskId)
    {
        $this->commentId = $commentId;
        $this->taskId = $taskId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('task.' . $this->taskId);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'DeleteComment';
    }
}