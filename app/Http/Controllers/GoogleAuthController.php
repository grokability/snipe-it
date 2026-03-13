<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    /**
     * We need this constructor so that we override the socialite expected config variables,
     * since we want to allow this to be changed via database fields
     */
    public function __construct()
    {
        parent::__construct();
        $setting = Setting::getSettings();
        config(['services.google.redirect' => config('app.url').'/google/callback']);
        config(['services.google.client_id' => $setting->google_client_id]);
        config(['services.google.client_secret' => $setting->google_client_secret]);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback() : RedirectResponse
    {
        try {
            $socialUser = Socialite::driver('google')->user();
            Log::debug('Google user found in Google Workspace');
        } catch (InvalidStateException $exception) {
            Log::debug('Google user NOT found in Google Workspace');
            return redirect()->route('login')
                ->withErrors(
                    [
                        'username' => [
                           trans('auth/general.google_login_failed')
                        ],
                    ]
                );
        }


        $user = User::where('username', $socialUser->getEmail())->first();


        if ($user) {
            Log::debug('Google user '.$socialUser->getEmail().' found in Snipe-IT');
            $user->update([
                'avatar'   => $socialUser->avatar,
            ]);

            Auth::login($user, true);
            return redirect()->route('home');
        }

        Log::debug('Google user '.$socialUser->getEmail().' NOT found in Snipe-IT');
        return redirect()->route('login')
            ->withErrors(
                [
                    'username' => [
                        trans('auth/general.google_login_failed'),
                    ],
                ]
            );
    }
}