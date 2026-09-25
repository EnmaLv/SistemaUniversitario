<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastOn(): array
    {
        $conversation = \Illuminate\Support\Facades\DB::table('conversations')->where('id', $this->message->conversation_id)->first();
        $recipientId = ($conversation->user_one_id == $this->message->sender_id)
            ? $conversation->user_two_id
            : $conversation->user_one_id;

        return [
            new PrivateChannel('chat.' . $this->message->conversation_id),
            new PrivateChannel('App.Models.Usuario.' . $recipientId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'body' => $this->message->body,
            'sender_id' => $this->message->sender_id,
            'time' => \Carbon\Carbon::parse($this->message->created_at)->format('h:i A'),
            'conversation_id' => $this->message->conversation_id
        ];
    }
}
