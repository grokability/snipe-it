<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;

class BaseMailable extends Mailable
{
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Auto-Response-Suppress' => 'OOF, DR, RN, NRN, AutoReply',
                'X-System-Sender' => 'Snipe-IT',
            ]
        );
    }
}