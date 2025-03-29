<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use App\Models\LocationStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;
/**
 * This controller handles all actions related to Locations for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class LocationsController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     */
    public function index() : View
    {
        // Grab all the locations
        $this->authorize('view', Location::class);
        // Show the page
        return view('locations/index');
    }
    public function closedIndex(): View
    {
        $this->authorize('view', Location::class);
    
        // Prikaz stranice sa podacima
        return view('locations/closed');
    }
    public function deletedIndex(): View
    {
        $this->authorize('view', Location::class);
    
        // Prikaz stranice sa obrisanim lokacijama
        return view('locations/deleted');
    }
    /**
     * Returns a form view used to create a new location.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::postCreate() method that validates and stores the data
     * @since [v1.0]
     */
    public function create() : View
    {
        $this->authorize('create', Location::class);
        $statuses = LocationStatus::all();
        return view('locations/edit')
            ->with('item', new Location)
            ->with('statuses', $statuses);
    }

    /**
     * Validates and stores a new location.
     *
     * @todo Check if a Form Request would work better here.
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::getCreate() method that makes the form
     * @since [v1.0]
     * @param ImageUploadRequest $request
     */
    public function store(ImageUploadRequest $request) : RedirectResponse
    {
        $this->authorize('create', Location::class);
        $location = new Location();
        $location->name = $request->input('name');
        $location->parent_id = $request->input('parent_id', null);
        $location->currency = $request->input('currency', '$');
        $location->address = $request->input('address');
        $location->address2 = $request->input('address2');
        $location->city = $request->input('city');
        $location->state = $request->input('state');
        $location->country = $request->input('country');
        $location->zip = $request->input('zip');
        $location->ldap_ou = $request->input('ldap_ou');
        $location->manager_id = $request->input('manager_id');
        $location->created_by = auth()->id();
        $location->phone = request('phone');
        $location->fax = request('fax');
        $location->notes = $request->input('notes');
        $location->status_id = $request->input('status_id', 1);

        $location = $request->handleImages($location);

        if ($location->save()) {
            return redirect()->route('locations.index')->with('success', trans('admin/locations/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($location->getErrors());
    }

    /**
     * Makes a form view to edit location information.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::postCreate() method that validates and stores
     * @param int $locationId
     * @since [v1.0]
     */
    public function edit(Location $location) : View | RedirectResponse
    {
        $this->authorize('update', Location::class);
        
        if (is_null($location = Location::find($location->id))) {
            return redirect()->route('locations.index')->with('error', trans('admin/locations/message.does_not_exist'));
        }
    
        $statuses = LocationStatus::all();  // Dohvati sve statuse iz baze
    
        return view('locations.edit')
            ->with('item', $location)
            ->with('statuses', $statuses);  // Prosledi statuse u view
    }

    /**
     * Validates and stores updated location data from edit form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::getEdit() method that makes the form view
     * @param ImageUploadRequest $request
     * @param int $locationId
     * @since [v1.0]
     */

     public function update(ImageUploadRequest $request, Location $location) : RedirectResponse
     {
         $this->authorize('update', Location::class);
 
         $location->name = $request->input('name');
         $location->parent_id = $request->input('parent_id', null);
         $location->currency = $request->input('currency', '$');
         $location->address = $request->input('address');
         $location->address2 = $request->input('address2');
         $location->city = $request->input('city');
         $location->state = $request->input('state');
         $location->country = $request->input('country');
         $location->zip = $request->input('zip');
         $location->phone = request('phone');
         $location->fax = request('fax');
         $location->ldap_ou = $request->input('ldap_ou');
         $location->manager_id = $request->input('manager_id');
         $location->notes = $request->input('notes');
         $location->status_id = $request->input('status_id');
 
         $location = $request->handleImages($location);
         $oldStatus = $location->status ? $location->status->name : 'N/A';
         if ($location->save()) {
             return redirect()->route('locations.index')->with('success', trans('admin/locations/message.update.success'));
         }
 
         return redirect()->back()->withInput()->withInput()->withErrors($location->getErrors());
     }

     public function updateStatus(Request $request, Location $location)
     {
         // Validacija statusa
         $request->validate([
             'status_id' => 'required|exists:location_statuses,id',
         ]);
     
         // Čuvanje starog statusa za logovanje
         $oldStatus = $location->status->name;
     
         // Ažuriranje statusa lokacije
         $location->status_id = $request->input('status_id');
         $location->save();
     
         // Zapisivanje promene u log
         $newStatus = $location->status->name;
         Log::info("Status lokacije '{$location->name}' promenjen sa '{$oldStatus}' na '{$newStatus}'.");
     
         // Redirekcija nazad sa porukom o uspehu
         return redirect()->back()->with('success', 'Status lokacije je uspešno promenjen.');
     }

    /**
     * Validates and deletes selected location.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $locationId
     * @since [v1.0]
     */
    public function destroy($locationId) : RedirectResponse
    {
        $this->authorize('delete', Location::class);
        if (is_null($location = Location::find($locationId))) {
            return redirect()->to(route('locations.index'))->with('error', trans('admin/locations/message.does_not_exist'));
        }
        if ($location->users()->count() > 0) {
            return redirect()->to(route('locations.index'))->with('error', trans('admin/locations/message.assoc_users'));
        } elseif ($location->children()->count() > 0) {
            return redirect()->to(route('locations.index'))->with('error', trans('admin/locations/message.assoc_child_loc'));
        } elseif ($location->assets()->count() > 0) {
            return redirect()->to(route('locations.index'))->with('error', trans('admin/locations/message.assoc_assets'));
        } elseif ($location->assignedassets()->count() > 0) {
            return redirect()->to(route('locations.index'))->with('error', trans('admin/locations/message.assoc_assets'));
        }

        if ($location->image) {
            try {
                Storage::disk('public')->delete('locations/'.$location->image);
            } catch (\Exception $e) {
                Log::error($e);
            }
        }
        // Pre nego što obrišete lokaciju, postavite status na "Obrisana"
        $status = LocationStatus::where('name', 'Obrisana')->first();
        
        if ($status) {
            $this->status_id = $status->id;
            $this->save();
        }

        $location->delete();

        return redirect()->to(route('locations.index'))->with('success', trans('admin/locations/message.delete.success'));
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations detail page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $id
     * @since [v1.0]
     */
    public function show(Location $location) : View | RedirectResponse
    {
        $location = Location::withCount('assignedAssets as assigned_assets_count')
            ->withCount('assets as assets_count')
            ->withCount('rtd_assets as rtd_assets_count')
            ->withCount('children as children_count')
            ->withCount('users as users_count')
            ->withTrashed()
            ->find($location->id);

        if (isset($location->id)) {
            return view('locations/view', compact('location'));
        }

        return redirect()->route('locations.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }

    public function print_assigned($id) : View | RedirectResponse
    {

        if ($location = Location::where('id', $id)->first()) {
            $parent = Location::where('id', $location->parent_id)->first();
            $manager = User::where('id', $location->manager_id)->first();
            $users = User::where('location_id', $id)->with('company', 'department', 'location')->get();
            $assets = Asset::where('assigned_to', $id)->where('assigned_type', Location::class)->with('model', 'model.category')->get();
            return view('locations/print')->with('assets', $assets)->with('users', $users)->with('location', $location)->with('parent', $parent)->with('manager', $manager);

        }

        return redirect()->route('locations.index')->with('error', trans('admin/locations/message.does_not_exist'));



    }


    /**
     * Returns a view that presents a form to clone a location.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $locationId
     * @since [v6.0.14]
     */
    public function getClone($locationId = null) : View | RedirectResponse
    {
        $this->authorize('create', Location::class);

        // Check if the asset exists
        if (is_null($location_to_clone = Location::find($locationId))) {
            // Redirect to the asset management page
            return redirect()->route('licenses.index')->with('error', trans('admin/locations/message.does_not_exist'));
        }

        $location = clone $location_to_clone;

        // unset these values
        $location->id = null;
        $location->image = null;

        return view('locations/edit')
            ->with('item', $location);
    }


    /**
     * Restore a given Asset Model (mark as un-deleted)
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $id
     */
    public function postRestore($id) : RedirectResponse
    {
        $this->authorize('create', Location::class);

        if ($location = Location::withTrashed()->find($id)) {

            if ($location->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.location')]));
            }

            if ($location->restore()) {
                $logaction = new Actionlog();
                $logaction->item_type = Location::class;
                $logaction->item_id = $location->id;
                $logaction->created_at = date('Y-m-d H:i:s');
                $logaction->created_by = auth()->id();
                $logaction->logaction('restore');

                return redirect()->route('locations.index')->with('success', trans('admin/locations/message.restore.success'));
            }

            // Check validation
            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.location'), 'error' => $location->getErrors()->first()]));
        }

        return redirect()->back()->with('error', trans('admin/models/message.does_not_exist'));

    }
    public function print_all_assigned($id) : View | RedirectResponse
    {
        if ($location = Location::where('id', $id)->first()) {
            $parent = Location::where('id', $location->parent_id)->first();
            $manager = User::where('id', $location->manager_id)->first();
            $users = User::where('location_id', $id)->with('company', 'department', 'location')->get();
            $assets = Asset::where('location_id', $id)->with('model', 'model.category')->get();
            return view('locations/print')->with('assets', $assets)->with('users', $users)->with('location', $location)->with('parent', $parent)->with('manager', $manager);

        }
        return redirect()->route('locations.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }


    /**
     * Returns a view that allows the user to bulk delete locations
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.3.1]
     */
    public function postBulkDelete(Request $request) : View | RedirectResponse
    {
        $locations_raw_array = $request->input('ids');

        // Make sure some IDs have been selected
        if ((is_array($locations_raw_array)) && (count($locations_raw_array) > 0)) {
            $locations = Location::whereIn('id', $locations_raw_array)
                ->withCount('assignedAssets as assigned_assets_count')
                ->withCount('assets as assets_count')
                ->withCount('rtd_assets as rtd_assets_count')
                ->withCount('children as children_count')
                ->withCount('users as users_count')->get();

                $valid_count = 0;
                foreach ($locations as $location) {
                    if ($location->isDeletable()) {
                        $valid_count++;
                    }
                }
                return view('locations/bulk-delete', compact('locations'))->with('valid_count', $valid_count);
        }

        return redirect()->route('models.index')
            ->with('error', 'You must select at least one model to edit.');
    }

    /**
     * Checks that locations can be deleted and deletes them if they can
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.3.1]

     */
    public function postBulkDeleteStore(Request $request) : RedirectResponse
    {
        $locations_raw_array = $request->input('ids');

        if ((is_array($locations_raw_array)) && (count($locations_raw_array) > 0)) {
            $locations = Location::whereIn('id', $locations_raw_array)
                ->withCount('assignedAssets as assigned_assets_count')
                ->withCount('assets as assets_count')
                ->withCount('rtd_assets as rtd_assets_count')
                ->withCount('children as children_count')
                ->withCount('users as users_count')->get();

            $success_count = 0;
            $error_count = 0;

            foreach ($locations as $location) {

                // Can we delete this location?
                if ($location->isDeletable()) {
                    $location->delete();
                    $success_count++;
                } else {
                    $error_count++;
                }
            }

            Log::debug('Success count: '.$success_count);
            Log::debug('Error count: '.$error_count);
            // Complete success
            if ($success_count == count($locations_raw_array)) {
                return redirect()
                    ->route('locations.index')
                    ->with('success', trans_choice('general.bulk.delete.success', $success_count,
                        ['object_type' => trans_choice('general.location_plural', $success_count), 'count' => $success_count]
                    ));
            }

            // Partial success
            if ($error_count > 0) {
                return redirect()
                    ->route('locations.index')
                    ->with('warning', trans('general.bulk.delete.partial',
                        ['success' => $success_count, 'error' => $error_count, 'object_type' => trans('general.locations')]
                    ));
                }
            }


        // Nothing was selected - return to the index
        return redirect()
            ->route('locations.index')
            ->with('error', trans('general.bulk.nothing_selected',
                ['object_type' => trans('general.locations')]
            ));

    }
    public function print_contract($locationId, string $contractType)
    {
	    \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
	    $this->authorize('update', Location::class);
        switch ($contractType) {
            case "1": $filename = 'private_uploads/eula-pdfs/ugovor.docx'; $tip_ugovora = "Ugovor o reversu 2023 v9";
            break;
            case "2": $filename = 'private_uploads/eula-pdfs/anex.docx'; $tip_ugovora = "Aneks ugovora";
            break;
            case "3": $filename = 'private_uploads/eula-pdfs/raskid.docx'; $tip_ugovora = "Raskid reversa";
            break;
            case "4": $filename = 'private_uploads/eula-pdfs/aneks2017.docx'; $tip_ugovora = "Aneks ugovora 2017";
	        break;
        }
        if ($location = Location::where('id', $locationId)->first()) {
        
            $basePath = Storage::disk('local')->path('');
            
            $path = $basePath . DIRECTORY_SEPARATOR . $filename;
            

            $contract_template = new \PhpOffice\PhpWord\TemplateProcessor($path);

            $parent = Location::where('id', $location->parent_id)->first();
            $parent_name = $parent->name ?? "Ime Zastupnika";
            $parent_city = $parent->city ?? "Grad Zastupnika";
            if ($parent){
                $parent_manager = User::where('id', $parent->manager_id)->first();
                $director = $parent_manager->full_name ?? "Direktor";
                $parent_information = array(
                    'parent_name' => $parent->state ?? "Ime Zastupnika",
                    'parent_city' => $parent->city ?? "Grad Zastupnika",
                    'parent_address' => $parent->address ?? "Adresa Zastupnika",
                    'parent_mb' => $parent->ldap_ou ?? "Maticni Broj",
                    'parent_pib' => $parent->phone ?? "PIB",
                    'parent_manager' => $parent->fax ?? "Direktor");
            }
            else{
                return redirect()->back()->with('error','Nije postavljen zastupnik lokacije');
            }
            
            $assets = Asset::where('assigned_to', $locationId)->where('assigned_type', Location::class)->with('model', 'model.category')->get();

            $pun_naziv = $location->name;
            $datum_kreiranja = $location->created_at ? $location->created_at->format('d.m.Y') : 'DATUM';

            if (preg_match("/\d{7}/",$pun_naziv,$sifra_lista)){
            $oj = $sifra_lista[0];
		    }
		    else{
		    $oj = "";
		    }
            if ($location->fax){
                $location_cn = '(' . $location->fax . ')';
            }
            else{
                $location_cn = '';
            }
            $location_information = array(
                'location_address' => $location->address ? : 'ADRESA',
                'location_city' => $location->city ? : 'GRAD',
                'location_oj' => $location->ldap_ou ? : $oj,
                'location_cn' => $location_cn,
                'register_num' => $location->currency ? : 'BROJ-UGOVORA'
            );
            
            $asset_list = array();
            $asset_counter = 1;
            foreach ($assets as $key => $asset){

                $asset_list[$key] = array(
                'id' => $asset_counter,
                'category' => ($asset->model) && ($asset->model->category) ? $asset->model->category->name : '',
                'manufacturer' => (($asset->model) && ($asset->model->manufacturer)) ? $asset->model->manufacturer->name : '',
                'model_nr' => ($asset->model) ? $asset->model->model_number : '',
                'serial_number' => $asset->serial,
                'price' => $asset->purchase_cost
                );
                $asset_counter++;
            }
            $contract_template->cloneRowAndSetValues('category', $asset_list);
            $contract_template->setValues($parent_information);
            $contract_template->setValues($location_information);
            $outputPath = storage_path('app/output.docx');
            $contract_template->saveAs($outputPath);
    
            // Download the generated DOCX file
            $out_filename = $location->name . " " . $tip_ugovora . ".docx";
            return response()->download($outputPath, $out_filename)->deleteFileAfterSend(true);
           

        }

        return redirect()->route('locations.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }
}
