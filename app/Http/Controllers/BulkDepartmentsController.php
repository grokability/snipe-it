<?php

namespace App\Http\Controllers;

use App\Actions\Departments\DestroyDepartmentAction;
use App\Exceptions\ItemStillHasUsers;
use App\Models\Department;
use Illuminate\Http\Request;

class BulkDepartmentsController extends Controller
{
    public function destroy(Request $request)
    {
        $this->authorize('delete', Department::class);

        $errors = [];
        $success_count = 0;

        foreach ($request->ids as $id) {
            $department = Department::find($id);
            if (is_null($department)) {
                $errors[] = trans('admin/departments/message.does_not_exist');

                continue;
            }
            try {
                DestroyDepartmentAction::run(department: $department);
                $success_count++;
            } catch (ItemStillHasUsers $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_users_no_count', ['item_name' => $department->name, 'item' => trans('general.department')]);
            } catch (\Exception $e) {
                report($e);
                $errors[] = trans('general.something_went_wrong');
            }
        }

        if (count($errors) > 0) {
            if ($success_count > 0) {
                return redirect()->route('departments.index')
                    ->with('success', trans_choice('admin/departments/message.delete.partial_success', $success_count, ['count' => $success_count]))
                    ->with('multi_error_messages', $errors);
            }

            return redirect()->route('departments.index')->with('multi_error_messages', $errors);
        }

        return redirect()->route('departments.index')
            ->with('success', trans_choice('admin/departments/message.delete.bulk_success', $success_count, ['count' => $success_count]));
    }
}
