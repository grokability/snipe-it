<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class WelcomeNotification extends Notification
{
    use Queueable;

    public $expire_date;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public User $user)
    {
        $this->user->token = Password::broker('invites')->createToken($user);
        $this->user->expire_date = now()->addMinutes((int) config('auth.passwords.invites.expire', 2880))->format('F j, Y, g:i a');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {

        return (new MailMessage())
            ->subject(trans('mail.welcome', ['name' => $this->user->first_name.' '.$this->user->last_name]))
            ->markdown('notifications.Welcome', $this->user->toArray());
    }
}
