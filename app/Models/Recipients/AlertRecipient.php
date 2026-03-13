<?php

namespace App\Models\Recipients;

class AlertRecipient extends Recipient
{
    public function __construct(string $email)
    {
        $this->email = trim($email);
    }
}
