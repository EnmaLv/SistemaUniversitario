<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $sender = \Illuminate\Support\Facades\DB::table('users')->where('id', $this->message->sender_id)->first();
        return [
            'type_id' => 'new_message',
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $sender ? $sender->name : 'Usuario',
            'body' => $this->message->body,
            'url' => route('chat.index') . '?user=' . $this->message->sender_id,
        ];
    }
}
