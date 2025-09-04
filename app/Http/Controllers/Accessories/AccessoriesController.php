<?php

namespace App\Http\Controllers\Accessories;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Accessory;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Contracts\View\View;
use \Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/** This controller handles all actions related to Accessories for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class AccessoriesController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the accessories listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see AccessoriesController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     */
    public function index() : View
    {
        $this->authorize('index', Accessory::class);
        return view('accessories.index');
    }

    /**
     * Returns a view with a form to create a new Accessory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function create() : View
    {
        $this->authorize('create', Accessory::class);
        $category_type = 'accessory';

        return view('accessories/edit')->with('category_type', $category_type)
          ->with('item', new Accessory);
    }

    /**
     * Validate and save new Accessory from form post
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param ImageUploadRequest $request
     */
    public function store(ImageUploadRequest $request) : RedirectResponse
    {
        $this->authorize(Accessory::class);
        
        // create a new model instance
        $accessory = new Accessory();

        // Update the accessory data
        $accessory->name                    = request('name');
        $accessory->category_id             = request('category_id');
        $accessory->location_id             = request('location_id');
        $accessory->min_amt                 = request('min_amt');
        $accessory->company_id              = Company::getIdForCurrentUser(request('company_id'));
        $accessory->order_number            = request('order_number');
        $accessory->manufacturer_id         = request('manufacturer_id');
        $accessory->model_number            = request('model_number');
        $accessory->purchase_date           = request('purchase_date');
        $accessory->purchase_cost           = request('purchase_cost');
        $accessory->qty                     = request('qty');
        $accessory->created_by              = auth()->id();
        $accessory->supplier_id             = request('supplier_id');
        $accessory->notes                   = request('notes');

        if ($request->has('use_cloned_image')) {
            $cloned_model_img = Accessory::select('image')->find($request->input('clone_image_from_id'));
            if ($cloned_model_img) {
                $new_image_name = 'clone-'.date('U').'-'.$cloned_model_img->image;
                $new_image = 'accessories/'.$new_image_name;
                Storage::disk('public')->copy('accessories/'.$cloned_model_img->image, $new_image);
                $accessory->image = $new_image_name;
            }

        } else {
            $accessory = $request->handleImages($accessory);
        }

        if($request->get('redirect_option') === 'back'){
            session()->put(['redirect_option' => 'index']);
        } else {
            session()->put(['redirect_option' => $request->get('redirect_option')]);
        }

        // Was the accessory created?
        if ($accessory->save()) {
            // Redirect to the new accessory  page
            return Helper::getRedirectOption($request, $accessory->id, 'Accessories')
                ->with('success', trans('admin/accessories/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($accessory->getErrors());
    }

    /**
     * Return view for the Accessory update form, prepopulated with existing data
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $accessoryId
     */
    public function edit(Accessory $accessory) : View | RedirectResponse
    {
        $this->authorize('update', Accessory::class);
        return view('accessories.edit')->with('item', $accessory)->with('category_type', 'accessory');
    }

    /**
     * Returns a view that presents a form to clone an accessory.
     *
     * @author [J. Vinsmoke]
     * @param int $accessoryId
     * @since [v6.0]
     */
    public function getClone(Accessory $accessory) : View | RedirectResponse
    {

        $this->authorize('create', Accessory::class);
        $cloned = clone $accessory;
        $accessory_to_clone = $accessory;
        $cloned->id = null;
        $cloned->deleted_at = '';

        return view('accessories/edit')
            ->with('cloned_model', $accessory_to_clone)
            ->with('item', $cloned);
        
    }

    /**
     * Save edited Accessory from form post
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param ImageUploadRequest $request
     * @param  int $accessoryId
     */
    public function update(ImageUploadRequest $request, Accessory $accessory) : RedirectResponse
    {
        if ($accessory = Accessory::withCount('checkouts as checkouts_count')->find($accessory->id)) {

            $this->authorize($accessory);

            $validator = Validator::make($request->all(), [
                "qty" => "required|numeric|min:$accessory->checkouts_count"
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }



            // Update the accessory data
            $accessory->name = request('name');
            $accessory->location_id = request('location_id');
            $accessory->min_amt = request('min_amt');
            $accessory->category_id = request('category_id');
            $accessory->company_id = Company::getIdForCurrentUser(request('company_id'));
            $accessory->manufacturer_id = request('manufacturer_id');
            $accessory->order_number = request('order_number');
            $accessory->model_number = request('model_number');
            $accessory->purchase_date = request('purchase_date');
            $accessory->purchase_cost = request('purchase_cost');
            $accessory->qty = request('qty');
            $accessory->supplier_id = request('supplier_id');
            $accessory->notes = request('notes');

            $accessory = $request->handleImages($accessory);

            session()->put(['redirect_option' => $request->get('redirect_option')]);

            if ($accessory->save()) {
                return Helper::getRedirectOption($request, $accessory->id, 'Accessories')
                    ->with('success', trans('admin/accessories/message.update.success'));
            }
        } else {
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
        }

        return redirect()->back()->withInput()->withErrors($accessory->getErrors());
    }

    /**
     * Delete the given accessory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $accessoryId
     */
    public function destroy($accessoryId) : RedirectResponse
    {
        if (is_null($accessory = Accessory::withCount('checkouts as checkouts_count')->find($accessoryId))) {
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.not_found'));
        }

        $this->authorize($accessory);


        if ($accessory->checkouts_count > 0) {
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/general.delete_disabled'));
        }

        if ($accessory->image) {
            try {
                Storage::disk('public')->delete('accessories'.'/'.$accessory->image);
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $accessory->delete();

        return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.delete.success'));
    }


    /**
     * Returns a view that invokes the ajax table which  contains
     * the content for the accessory detail view, which is generated in getDataView.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $accessoryID
     * @see AccessoriesController::getDataView() method that generates the JSON response
     * @since [v1.0]
     */
    public function show(Accessory $accessory) : View | RedirectResponse
    {
        $accessory->loadCount('checkouts as checkouts_count');

        $accessory->load(['adminuser' => fn($query) => $query->withTrashed()]);

        $this->authorize('view', $accessory);
        return view('accessories.view', compact('accessory'));
    }
}
