<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable): array // send to database
    {
        return [
            'type' =>'comment',
            'title' => $this->comment->user->profile->name . ' has New Comment on Your Post',
            'message' => 'Someone has commented on your post.',
            'comment_id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
            'comment_content' => $this->comment->content,
            'actor_id' => $this->comment->user->id,
            'actor_name' => $this->comment->user->profile->name,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
