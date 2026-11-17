<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\Saml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * This controller provides the endpoint for SAML communication and metadata.
 *
 * @author Johnson Yi <jyi.dev@outlook.com>
 *
 * @since 5.0.0
 */
class SamlController extends Controller
{
    /**
     * @var Saml
     */
    protected $saml;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(Saml $saml)
    {
        $this->saml = $saml;

        $this->middleware('guest', ['except' => ['metadata', 'sls']]);
    }

    /**
     * Return SAML SP metadata for Snipe-IT
     *
     * /saml/metadata
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     */
    public function metadata(Request $request)
    {
        $metadata = $this->saml->getSPMetadata();

        if (empty($metadata)) {
            Log::debug('SAML metadata is empty - return a 403');

            return response()->view('errors.403', [], 403);
        }

        return response()->streamDownload(function () use ($metadata) {
            echo $metadata;
        }, 'snipe-it-metadata.xml', ['Content-Type' => 'text/xml']);
    }

    /**
     * Begin the SP-Initiated SSO by sending AuthN to the IdP.
     *
     * /login/saml
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @return RedirectResponse
     */
    public function login(Request $request)
    {
        $auth = $this->saml->getAuth();
        $ssoUrl = $auth->login(session()->get('url.intended'), [], false, false, false, false);

        return redirect()->away($ssoUrl);
    }

    /**
     * Receives, parses the assertion from IdP and flashes SAML data
     * back to the LoginController for authentication.
     *
     * /saml/acs
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @return RedirectResponse
     */
    public function acs(Request $request)
    {
        // RelayState is attacker-controllable via a hostile IdP or a
        // crafted SAML POST binding. Sanitize before storing so any
        // downstream consumer that skips Helper::getRedirectOption
        // (LoginController::authenticated, RedirectIfAuthenticated,
        // DashboardController, etc. all call redirect()->intended()
        // directly, which does no host validation) can't be steered
        // off-site. sameOriginUrl() also strips CR/LF and rejects
        // non-http(s) schemes. Runs before getAuth() so the sanitize
        // step is unconditional even if SAML happens to be disabled at
        // request time.
        if ($relayState = Helper::sameOriginUrl($request->post('RelayState'))) {
            session()->put('url.intended', $relayState);
        }

        $saml = $this->saml;
        $auth = $saml->getAuth();
        $saml_exception = false;
        try {
            $auth->processResponse();
        } catch (\Exception $e) {
            Log::warning('Exception caught in SAML login: '.$e->getMessage());
            $saml_exception = true;
        }
        $errors = $auth->getErrors();

        if (! empty($errors) || $saml_exception) {
            Log::warning('There was an error with SAML ACS: '.implode(', ', $errors));
            Log::warning('Reason: '.$auth->getLastErrorReason());

            return redirect()->route('login')->with('error', trans('auth/message.signin.error'));
        }

        $samlData = $saml->extractData();

        return redirect()->route('login')->with('saml_login', $samlData);
    }

    /**
     * Receives LogoutRequest/LogoutResponse from IdP and flashes
     * back to the LoginController for logging out.
     *
     * /saml/sls
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @return RedirectResponse
     */
    public function sls(Request $request)
    {
        $auth = $this->saml->getAuth();
        $retrieveParametersFromServer = $this->saml->getSetting('retrieveParametersFromServer', false);
        $saml_exception = false;

        // Two possble SLS entry points carry different trust properties for
        // the URL processSLO returns:
        //   - SAMLRequest (IdP-initiated SLO): the toolkit built a
        //     LogoutResponse, and returned `getSLOResponseUrl()` from
        //     the SP settings as the redirect target. By design that
        //     URL points away from Snipe-IT to the configured IdP and must
        //     NOT pass through same-origin sanitization, otherwise
        //     the Response is dropped and the LoginController's SLO
        //     branch starts a brand-new LogoutRequest to the IdP
        //     instead of completing the
        //     one in progress.
        //   - SAMLResponse (WE initiated SLO): the toolkit returns
        //     the RelayState value, which is attacker-controllable
        //     when a hostile IdP reflects it. That is the original
        //     sanitization target of GHSA-579m-9gf4-28jp.
        $isIdpInitiated = $request->has('SAMLRequest');

        try {
            $sloUrl = $auth->processSLO(true, null, $retrieveParametersFromServer, null, true);
        } catch (\Exception $e) {
            Log::warning('Exception caught in SAML single-logout: '.$e->getMessage());
            $saml_exception = true;
        }
        $errors = $auth->getErrors();

        if (! empty($errors) || $saml_exception) {
            Log::warning('There was an error with SAML SLS: '.implode(', ', $errors));
            Log::warning('Reason: '.$auth->getLastErrorReason());

            return view('errors.403');
        }

        $safeSloUrl = $isIdpInitiated
            ? Helper::sameHostAsConfiguredIdpUrl($sloUrl, $auth->getSLOResponseUrl())
            : Helper::sameOriginUrl($sloUrl);

        return redirect()->route('logout.get')->with([
            'saml_logout' => true,
            'saml_slo_redirect_url' => $safeSloUrl,
            'saml_slo_idp_initiated' => $isIdpInitiated,
        ]);
    }
}
