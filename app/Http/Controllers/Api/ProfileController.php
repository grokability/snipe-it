<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\ProfileTransformer;
use App\Models\CheckoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\TokenRepository;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Facades\Gate;
use App\Models\CustomField;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfileController extends Controller
{

    /**
     * The token repository implementation.
     *
     * @var \Laravel\Passport\TokenRepository
     */
    protected $tokenRepository;

    /**
     * Create a controller instance.
     *
     * @param  \Laravel\Passport\TokenRepository  $tokenRepository
     * @param  \Illuminate\Contracts\Validation\Factory  $validation
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
     * @since [v4.3.0]
     */
    public function requestedAssets() :  array
    {
        $checkoutRequests = CheckoutRequest::where('user_id', '=', auth()->id())->get();

        $results = array();
        $show_field = array();
        $showable_fields = array();
        $results['total'] = $checkoutRequests->count();

        $all_custom_fields = CustomField::all(); //used as a 'cache' of custom fields throughout this page load
        foreach ($all_custom_fields as $field) {
            if (($field->field_encrypted=='0') && ($field->show_in_requestable_list=='1')) {
                $showable_fields[] = $field->db_column_name();
            }
        }

        foreach ($checkoutRequests as $checkoutRequest) {

            // Make sure the asset and request still exist
            if ($checkoutRequest && $checkoutRequest->itemRequested()) {
                $assets = [
                    'image' => e($checkoutRequest->itemRequested()->present()->getImageUrl()),
                    'name' => e($checkoutRequest->itemRequested()->display_name),
                    'type' => e($checkoutRequest->itemType()),
                    'qty' => (int) $checkoutRequest->quantity,
                    'location' => ($checkoutRequest->location()) ? e($checkoutRequest->location()->name) : null,
                    'expected_checkin' => Helper::getFormattedDateObject($checkoutRequest->itemRequested()->expected_checkin, 'datetime'),
                    'request_date' => Helper::getFormattedDateObject($checkoutRequest->created_at, 'datetime'),
                ];

                foreach ($showable_fields as $showable_field_name) {
                    $show_field['custom_fields.'.$showable_field_name] =  $checkoutRequest->itemRequested()->{$showable_field_name};
                }

                // Merge the plain asset data and the custom fields data
                $results['rows'][] = array_merge($assets, $show_field);
            }


        }

        return $results;
    }


    /**
     * Delete an API token
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.0.5]
     */
    public function createApiToken(Request $request) : JsonResponse
    {

        if (!Gate::allows('self.api')) {
            abort(403);
        }

        $accessTokenName = $request->input('name', 'Auth Token');

        if ($accessToken = auth()->user()->createToken($accessTokenName)->accessToken) {

            // Get the ID so we can return that with the payload
            $token = DB::table('oauth_access_tokens')->where('user_id', '=', auth()->id())->where('name','=',$accessTokenName)->orderBy('created_at', 'desc')->first();
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
     * @since [v6.0.5]
     */
    public function deleteApiToken($tokenId) : Response
    {

        if (!Gate::allows('self.api')) {
            abort(403);
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
     * Show user's API tokens
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.0.5]
     */
    public function showApiTokens() : JsonResponse
    {

        if (!Gate::allows('self.api')) {
            abort(403);
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
     *  @param \App\Http\Transformers\ActionlogsTransformer $transformer
     *  @return \Illuminate\Http\JsonResponse
     *@since [v8.1.16]
     * @author [Godfrey Martinez] [<gmartinez@grokability.com>]
     */
    public function eulas(ProfileTransformer $transformer)
    {
        // Only return this user's EULAs
        $eulas = auth()->user()->eulas;
        return response()->json(
            $transformer->transformFiles($eulas, $eulas->count())
        );
    }


}
