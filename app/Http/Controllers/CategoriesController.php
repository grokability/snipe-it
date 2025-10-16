<?php

namespace App\Http\Controllers;

use App\Actions\Categories\DestroyCategoryAction;
use App\Exceptions\ItemStillHasAccessories;
use App\Exceptions\ItemStillHasAssetModels;
use App\Exceptions\ItemStillHasAssets;
use App\Exceptions\ItemStillHasChildren;
use App\Exceptions\ItemStillHasComponents;
use App\Exceptions\ItemStillHasConsumables;
use App\Exceptions\ItemStillHasLicenses;
use App\Helpers\Helper;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;

/**
 * This class controls all actions related to Categories for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class CategoriesController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the categories listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see CategoriesController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     */
    public function index() : View
    {
        // Show the page
        $this->authorize('view', Category::class);

        return view('categories/index');
    }

    /**
     * Returns a form view to create a new category.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see CategoriesController::store() method that stores the data
     * @since [v1.0]
     */
    public function create() : View
    {
        // Show the page
        $this->authorize('create', Category::class);

        return view('categories/edit')->with('item', new Category)
            ->with('category_types', Helper::categoryTypeList());
    }

    /**
     * Validates and stores the new category data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see CategoriesController::create() method that makes the form.
     * @since [v1.0]
     * @param ImageUploadRequest $request
     */
    public function store(ImageUploadRequest $request) : RedirectResponse
    {
        $this->authorize('create', Category::class);
        $category = new Category();
        $category->name = $request->input('name');
        $category->category_type = $request->input('category_type');
        $category->eula_text = $request->input('eula_text');
        $category->use_default_eula = $request->input('use_default_eula', '0');
        $category->require_acceptance = $request->input('require_acceptance', '0');
        $category->alert_on_response = $request->input('alert_on_response', '0');
        $category->checkin_email = $request->input('checkin_email', '0');
        $category->notes = $request->input('notes');
        $category->created_by = auth()->id();

        $category = $request->handleImages($category);
        if ($category->save()) {
            return redirect()->route('categories.index')->with('success', trans('admin/categories/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($category->getErrors());
    }

    /**
     * Returns a view that makes a form to update a category.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see CategoriesController::postEdit() method saves the data
     * @param int $categoryId
     * @since [v1.0]
     */
    public function edit(Category $category) : RedirectResponse | View
    {
        $this->authorize('update', Category::class);
        return view('categories/edit')->with('item', $category)
        ->with('category_types', Helper::categoryTypeList());
    }

    /**
     * Validates and stores the updated category data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see CategoriesController::getEdit() method that makes the form.
     * @param ImageUploadRequest $request
     * @param int $categoryId
     * @since [v1.0]
     */
    public function update(ImageUploadRequest $request, Category $category) : RedirectResponse
    {
        $this->authorize('update', Category::class);
        $category->name = $request->input('name');

        // Don't allow the user to change the category_type once it's been created
        if (($request->filled('category_type') && ($category->itemCount() > 0))) {
            $request->validate(['category_type' => 'in:'.$category->category_type]);
        }
        
        $category->category_type = $request->input('category_type', $category->category_type);

        $category->fill($request->all());

        $category->eula_text = $request->input('eula_text');
        $category->use_default_eula = $request->input('use_default_eula', '0');
        $category->require_acceptance = $request->input('require_acceptance', '0');
        $category->alert_on_response = $request->input('alert_on_response', '0');
        $category->checkin_email = $request->input('checkin_email', '0');
        $category->notes = $request->input('notes');

        $category = $request->handleImages($category);

        if ($category->save()) {
            // Redirect to the new category page
            return redirect()->route('categories.index')->with('success', trans('admin/categories/message.update.success'));
        }
        // The given data did not pass validation
        return redirect()->back()->withInput()->withErrors($category->getErrors());
    }

    /**
     * Validates and marks a category as deleted.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $categoryId
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', Category::class);
        try {
            DestroyCategoryAction::run($category);
        } catch (ItemStillHasChildren $e) {
            return redirect()->route('categories.index')->with('error', trans('general.bulk_delete_associations.general_assoc_warning', ['item' => trans('general.category')]));
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('categories.index')->with('error', trans('admin/categories/message.delete.error'));
        }

        return redirect()->route('categories.index')->with('success', trans('admin/categories/message.delete.success'));
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the categories detail view, which is generated in getDataView.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see CategoriesController::getDataView() method that generates the JSON response
     * @param $id
     * @since [v1.8]
     */
    public function show(Category $category) : View | RedirectResponse
    {
        $this->authorize('view', Category::class);

            if ($category->category_type == 'asset') {
                $category_type = 'hardware';
                $category_type_route = 'assets';
            } elseif ($category->category_type == 'accessory') {
                $category_type = 'accessories';
                $category_type_route = 'accessories';
            } else {
                $category_type = $category->category_type;
                $category_type_route = $category->category_type.'s';
            }

            return view('categories/view', compact('category'))
                ->with('category_type', $category_type)
                ->with('category_type_route', $category_type_route);
    }
}
