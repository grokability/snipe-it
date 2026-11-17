<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Middleware\CheckForTwoFactor;
use App\Http\Transformers\ActionlogsTransformer;
use App\Http\Transformers\ProfileTransformer;
use App\Models\Asset;
use App\Models\CheckoutRequest;
use App\Models\CustomField;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;

class ProfileController extends Controller
{
    /**
     * The token repository implementation.
     *
     * @var TokenRepository
     */
    protected $tokenRepository;

    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(TokenRepository $tokenRepository, ValidationFactory $validation)
    {
        $this->validation = $validation;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Display a listing of requested assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v4.3.0]
     */
    public function requestedAssets(): array
    {
        // Only surface OPEN (pending) requests. `state` is the
        // source of truth for lifecycle - canceled_at + fulfilled_at
        // still populate alongside their state transitions (WHEN,
        // paired with the WHAT in state) but consumers should
        // always filter on state, not on the datetime columns. A
        // whereNull('canceled_at') filter would miss fulfilled
        // rows entirely, which have no canceled_at set but are
        // just as done.
        $checkoutRequests = CheckoutRequest::where('user_id', '=', auth()->id())
            ->pending()
            ->get();

        $results = [];
        $show_field = [];
        $showable_fields = [];
        $results['total'] = $checkoutRequests->count();

        $all_custom_fields = CustomField::all(); // used as a 'cache' of custom fields throughout this page load
        foreach ($all_custom_fields as $field) {
            if (($field->field_encrypted == '0') && ($field->show_in_requestable_list == '1')) {
                $showable_fields[] = $field->db_column_name();
            }
        }

        foreach ($checkoutRequests as $checkoutRequest) {

            // Make sure the asset and request still exist
            if ($checkoutRequest && $checkoutRequest->itemRequested()) {
                $assets = [
                    'id' => (int) $checkoutRequest->id,
                    'image' => e($checkoutRequest->itemRequested()->present()->getImageUrl()),
                    'name' => e($checkoutRequest->itemRequested()->display_name),
                    'type' => e($checkoutRequest->itemType()),
                    'qty' => (int) $checkoutRequest->quantity,
                    'location' => ($checkoutRequest->location()) ? e($checkoutRequest->location()->name) : null,
                    'expected_checkin' => Helper::getFormattedDateObject($checkoutRequest->itemRequested()->expected_checkin, 'datetime'),
                    'request_date' => Helper::getFormattedDateObject($checkoutRequest->created_at, 'datetime'),
                    // Self-cancel URL. POSTing to /account/request/{type}/{id}
                    // as the request owner toggles the open request into
                    // canceled state via isRequestedBy() -> cancelRequest()
                    // in ViewAssetsController::getRequestItem. Emitted per
                    // row so the JS formatter doesn't need to reconstruct
                    // the URL from type + id (and stay in sync with the
                    // route's itemType regex, which can drift).
                    'cancel_url' => route('account/request-item', [
                        'itemType' => $checkoutRequest->itemType(),
                        'itemId' => $checkoutRequest->requestable_id,
                    ]),
                ];

                foreach ($showable_fields as $showable_field_name) {
                    $show_field['custom_fields.'.$showable_field_name] = $checkoutRequest->itemRequested()->{$showable_field_name};
                }

                // Merge the plain asset data and the custom fields data
                $results['rows'][] = array_merge($assets, $show_field);
            }

        }

        return $results;
    }

    /**
     * Paginated listing of the authenticated user's own assigned assets.
     * Powers the /account/view-assets#assets table and offers the same
     * server-side search, sort, and column selection as the admin asset
     * table, scoped to the caller via the assigned_to + assigned_type
     * pair.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function assets(Request $request, ProfileTransformer $transformer): array
    {
        $allowed_columns = [
            'id',
            'name',
            'asset_tag',
            'serial',
            'model_number',
            'last_checkout',
            'last_checkin',
            'expected_checkin',
            'created_at',
            'updated_at',
            'purchase_date',
            'last_audit_date',
            'next_audit_date',
            'asset_eol_date',
        ];

        $assets = Asset::where('assets.assigned_to', auth()->id())
            ->where('assets.assigned_type', User::class)
            ->AssetsForShow()
            ->with(
                'model.category',
                'model.manufacturer',
                'location',
                'defaultLoc',
                'status',
                'company',
                'supplier',
            );

        if ($request->filled('search')) {
            $assets->TextSearch($request->input('search'));
        }

        $offset = ($request->filled('offset') && $request->input('offset') >= 0) ? (int) $request->input('offset') : 0;
        $limit = $request->filled('limit') ? (int) $request->input('limit') : 50;
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns, true) ? $request->input('sort') : 'asset_tag';

        $total = $assets->count();
        $assets = $assets->orderBy('assets.'.$sort, $order)->skip($offset)->take($limit)->get();

        return $transformer->transformAssets($assets, $total);
    }

    /**
     * Delete an API token
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v6.0.5]
     */
    public function createApiToken(Request $request): JsonResponse
    {

        if (! Gate::allows('self.api')) {
            abort(403);
        }

        // Refuse to issue tokens for a session that hasn't cleared 2FA, even
        // if it picked up a Passport cookie elsewhere. Closes the bypass
        // chain "password → /two-factor cookie → mint token → use forever".
        if (! CheckForTwoFactor::isComplete($request)) {
            abort(403, trans('auth/message.two_factor.enter_two_factor_code'));
        }

        $accessTokenName = $request->input('name', 'Auth Token');

        if ($accessToken = auth()->user()->createToken($accessTokenName)->accessToken) {

            // Get the ID so we can return that with the payload
            $token = DB::table('oauth_access_tokens')->where('user_id', '=', auth()->id())->where('name', '=', $accessTokenName)->orderBy('created_at', 'desc')->first();
            $accessTokenData['id'] = $token->id;
            $accessTokenData['token'] = $accessToken;
            $accessTokenData['name'] = $accessTokenName;

            return response()->json(Helper::formatStandardApiResponse('success', $accessTokenData, trans('account/general.personal_api_keys_success', ['key' => $accessTokenName])));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, 'Token could not be created.'));

    }

    /**
     * Delete an API token
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v6.0.5]
     */
    public function deleteApiToken($tokenId): Response
    {

        if (! Gate::allows('self.api')) {
            abort(403);
        }

        if (! CheckForTwoFactor::isComplete(request())) {
            abort(403, trans('auth/message.two_factor.enter_two_factor_code'));
        }

        $token = $this->tokenRepository->findForUser(
            $tokenId, auth()->user()->getAuthIdentifier()
        );

        if (is_null($token)) {
            return new Response('', 404);
        }

        $token->revoke();

        return new Response('', Response::HTTP_NO_CONTENT);

    }

    /**
     * Bearer-authenticated self-logout. Revokes the access token that
     * authenticated THIS request and any refresh token that was issued
     * alongside it, so a client cannot silently renew after logging
     * out. Does not touch the user's other tokens (other devices,
     * personal-access tokens, tokens under different OAuth clients) -
     * scope is exactly the current session.
     *
     * Deliberately distinct from Passport's UI-shaped
     * DELETE /oauth/tokens/{id} route, which is session-cookie
     * authenticated for the Passport-Vue self-service page and can't
     * answer a bearer-only client. Passport does not ship a
     * bearer-self-revoke endpoint because RFC 7009 revocation uses
     * client credentials rather than bearer auth, so this
     * "log me out of this session" affordance is a Snipe-IT addition.
     */
    public function logout(): Response
    {
        $token = auth()->user()?->token();

        // auth:api on the route guarantees a bearer-authenticated user,
        // but session-cookie / personal-access flows leave token() null,
        // and the HasApiTokens trait signature returns the ScopeAuthorizable
        // interface. Only AccessToken carries the underlying DB row we need
        // to revoke, so other implementers like TransientToken skip the path.
        if (! $token instanceof AccessToken) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        $token->revoke();

        Passport::refreshToken()->newQuery()
            ->where('access_token_id', $token->id)
            ->update(['revoked' => true]);

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Show user's API tokens
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v6.0.5]
     */
    public function showApiTokens(): JsonResponse
    {

        if (! Gate::allows('self.api')) {
            abort(403);
        }

        if (! CheckForTwoFactor::isComplete(request())) {
            abort(403, trans('auth/message.two_factor.enter_two_factor_code'));
        }

        $tokens = $this->tokenRepository->forUser(auth()->user()->getAuthIdentifier());
        $token_values = $tokens->load('client')->filter(function ($token) {
            return $token->client->personal_access_client && ! $token->revoked;
        })->values();

        return response()->json(Helper::formatStandardApiResponse('success', $token_values, null));

    }

    /**
     * Display the EULAs accepted by the user.
     *
     * @param  ActionlogsTransformer  $transformer
     * @return JsonResponse
     *
     *@since [v8.1.16]
     *
     * @author [Godfrey Martinez] [<gmartinez@grokability.com>]
     */
    public function eulas(ProfileTransformer $transformer, Request $request)
    {

        if (($request->filled('user_id')) && ($request->input('user_id') != 0)) {

            $eula_user = User::find($request->input('user_id'));

            if (($eula_user) && (Setting::getSettings()->manager_view_enabled) && (auth()->user()->isManagerOf($eula_user))) {
                $eulas = $eula_user->eulas;
            } else {
                return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/users/message.user_not_found')));
            }
        } else {
            $eulas = auth()->user()->eulas;
        }

        return response()->json($transformer->transformFiles($eulas, $eulas->count()));

    }
}
