<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected $username = 'username';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:10,1');
    }

    protected function rules()
    {
        return [
            'token' => 'required',
            'username' => 'required',
            'password' => 'confirmed|'.Setting::passwordComplexityRulesSaving('store'),
        ];
    }

    protected function credentials(Request $request)
    {
        return $request->only(
            'username', 'password', 'password_confirmation', 'token'
        );
    }

    public function showResetForm(Request $request, $token = null)
    {

        $credentials =  $request->only('email', 'token');

        if (is_null($this->broker()->getUser($credentials))) {
            Log::debug('Password reset form FAILED - this token is not valid.');
            return redirect()->route('password.request')->with('error', trans('passwords.token'));
        }

        return view('auth.passwords.reset')->with(
            [
                'token' => $token,
                'username' => $request->input('username'),
            ]
        );
    }

    public function reset(Request $request)
    {

        $broker = $this->broker();

        $messages = [
            'password.not_in' => trans('validation.disallow_same_pwd_as_user_fields'),
        ];

        $request->validate($this->rules());

        Log::debug('Checking if '.$request->input('username').' exists');
        // Check to see if the user even exists - we'll treat the response the same to prevent user sniffing
        if ($user = User::where('username', '=', $request->input('username'))->where('activated', '1')->whereNotNull('email')->first()) {
            Log::debug($user->username.' exists');


            // handle the password validation rules set by the admin settings
            if (strpos(Setting::passwordComplexityRulesSaving('store'), 'disallow_same_pwd_as_user_fields') !== false) {
                $request->validate(
                    [
                        'password' => 'required|notIn:["'.$user->email.'","'.$user->username.'","'.$user->first_name.'","'.$user->last_name.'"',
                    ], $messages);
            }

            if ($user->ldap_import != '1') {

                    // set the response
                    $response = $broker->reset(
                        $this->credentials($request), function ($user, $password) {
                        $this->resetPassword($user, $password);
                    });

                    // Check if the password reset above actually worked
                    if ($response == \Password::PASSWORD_RESET) {
                        Log::debug('Password reset for ' . $user->username . ' worked');
                        return redirect()->guest('login')->with('success', trans('passwords.reset'));
                    }

                    Log::debug('Password reset for ' . $user->username . ' FAILED - this user exists but the token is not valid');
                    return redirect()->back()->withInput($request->only('email'))->with('success', trans('passwords.reset'));
            }

        }


        Log::debug('Password reset for '.$request->input('username').' FAILED - user does not exist or does not have an email address - but make it look like it succeeded');
        return redirect()->guest('login')->with('success', trans('passwords.reset'));

    }



}
