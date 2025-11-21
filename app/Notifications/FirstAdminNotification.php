<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

#[AllowDynamicProperties]
class FirstAdminNotification extends Notification
{
    use Queueable;

    private $_data = [];

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $content)
    {
        $this->_data['email'] = $content['email'];
        $this->_data['first_name'] = $content['first_name'];
        $this->_data['last_name'] = $content['last_name'];
        $this->_data['username'] = $content['username'];
        $this->_data['password'] = $content['password'];
        $this->_data['url'] = config('app.url');
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
        return (new MailMessage)
            ->subject('👋 '.trans('mail.welcome', ['name' => $this->_data['first_name'].' '.$this->_data['last_name']]))
            ->markdown('notifications.FirstAdmin', $this->_data)
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader(
                    'X-System-Sender', 'Snipe-IT'
                );
            });
    }
}
