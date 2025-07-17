<?php

namespace App\Models\Recipients;

use App\Models\Setting;

class AdminRecipient extends Recipient
{

    protected $email;
    public function __construct()
    {
        $settings = Setting::getSettings();
        $this->email = trim($settings->admin_cc_email);
    }
    
    public function getEmail()
    {
        return $this->email;
    }
}
