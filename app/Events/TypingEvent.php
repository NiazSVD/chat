<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class TypingEvent implements ShouldBroadcast
{
    use Dispatchable;

    public $sender_id;
    public $receiver_id;
    public $is_typing;

    public function __construct($sender_id, $receiver_id, $is_typing)
    {
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
        $this->is_typing = $is_typing;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->receiver_id);
    }

    public function broadcastAs()
    {
        return 'user.typing';
    }
}
