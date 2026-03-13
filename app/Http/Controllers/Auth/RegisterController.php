<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        abort(404, 'Page not found');
    }

    public function register()
    {
        abort(404, 'Page not found');
    }
}
