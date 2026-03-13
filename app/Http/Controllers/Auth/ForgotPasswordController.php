<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,1', ['except' => 'showLinkRequestForm']);
    }

    /**
     * Get the e-mail subject line to be used for the reset link email.
     * Overriding method "getEmailSubject()" from trait "use ResetsPasswords"
     * @return string
     */
    public function getEmailSubject()
    {
        return property_exists($this, 'subject') ? $this->subject : \Lang::get('mail.reset_link');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        /**
         * Let's set a max character count here to prevent potential
         * buffer overflow issues with attackers sending very large
         * payloads through. The addition of the string rule prevents attackers
         * sending arrays through and causing 500s
         */
        $request->validate([
            'username' => ['required', 'max:255', 'string'],
        ]);

        /**
         * If we find a matching email with an activated user, we will
         * send the password reset link to the user.
         *
         * Once we have attempted to send the link, we will examine the response
         * then see the message we need to show to the user. Finally, we'll send out a proper response.
         */
        
        $response = null;

        try {
            $response = $this->broker()->sendResetLink(
                array_merge(
                    $request->only('username'),
                    ['activated' => '1'],
                    ['ldap_import' => '0']
                )
            );
        } catch(\Exception $e) {
            Log::info('Password reset attempt: User '.$request->input('username').'failed with exception: '.$e );
        }

        // Prevent timing attack to enumerate users.
        usleep(500000 + random_int(0, 1500000));

        if ($response === \Password::RESET_LINK_SENT) {
            Log::info('Password reset attempt: User '.$request->input('username').' WAS found, password reset sent');
        } else {
            Log::info('Password reset attempt: User matching username '.$request->input('username').' NOT FOUND or user is inactive');
        }

        /**
         * If an error was returned by the password broker, we will get this message
         * translated so we can notify a user of the problem. We'll redirect back
         * to where the users came from so they can attempt this process again.
         *
         * HOWEVER, we do not want to translate the message if the user isn't found
         * or isn't active, since that would allow an attacker to walk through
         * a dictionary attack and figure out registered user email addresses.
         *
         * Instead we tell the user we've sent an email even though we haven't.
         * It's bad UX, but better security. The compromises we sometimes have to make.
        */

        // Regardless of response, we do not want to disclose the status of a user account,
        // so we give them a generic "If this exists, we're TOTALLY gonna email you" response
        return redirect()->route('login')->with('success', trans('passwords.sent'));
        }
}
