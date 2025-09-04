<?php

namespace App\Http\Controllers\Kits;

use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\PredefinedKit;
use App\Models\User;
use App\Services\PredefinedKitCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * This controller handles all access kits management:
 * list, add/remove/change
 *
 * @author [D. Minaev.] [<dmitriy.minaev.v@gmail.com>]
 */
class CheckoutKitController extends Controller
{
    public $kitService;
    use CheckInOutRequest;

    public function __construct(PredefinedKitCheckoutService $kitService)
    {
        $this->kitService = $kitService;
    }

    /**
     * Show Bulk Checkout Page
     *
     * @author [D. Minaev.] [<dmitriy.minaev.v@gmail.com>]
     * @return \Illuminate\Contracts\View\View View to checkout
     */
    public function showCheckout(PredefinedKit $kit)
    {
        $this->authorize('checkout', Asset::class);
        return view('kits/checkout')->with('kit', $kit);
    }

    /**
     * Validate and process the new Predefined Kit data.
     *
     * @author [D. Minaev.] [<dmitriy.minaev.v@gmail.com>]
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $kit_id)
    {
        $user_id = e($request->get('user_id'));
        if (is_null($user = User::find($user_id))) {
            return redirect()->back()->with('error', trans('admin/users/message.user_not_found'));
        }

        $kit = new PredefinedKit();
        $kit->id = $kit_id;

        $checkout_result = $this->kitService->checkout($request, $kit, $user);
        if (Arr::has($checkout_result, 'errors') && count($checkout_result['errors']) > 0) {
            return redirect()->back()->with('error', trans('admin/kits/general.checkout_error'))->with('error_messages', $checkout_result['errors']);
        }

        return redirect()->back()->with('success', trans('admin/kits/general.checkout_success'))
            ->with('assets', Arr::get($checkout_result, 'assets', null))
            ->with('accessories', Arr::get($checkout_result, 'accessories', null))
            ->with('consumables', Arr::get($checkout_result, 'consumables', null));
    }
}
