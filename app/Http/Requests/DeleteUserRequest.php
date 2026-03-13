<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Gate;


class DeleteUserRequest extends FormRequest
{

    protected $redirectRoute = 'users.index';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('delete', User::class);
    }

    public function prepareForValidation(): void
    {
        $user_to_delete = User::withTrashed()->with('managesUsers')->find(request()->route('user'));

        if ($user_to_delete) {
            $this->merge([
                'user' => request()->route('user'),
                'admin_id' => auth()->id(),
                'managed_users' => $user_to_delete->managesUsers()->count(),
                'managed_locations' => $user_to_delete->managedLocations()->count(),
                'assigned_assets' => $user_to_delete->assets()->count(),
                'assigned_licenses' => $user_to_delete->licenses()->count(),
                'assigned_accessories' => $user_to_delete->accessories()->count(),
                'deleted_at' => $user_to_delete->deleted_at,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user' =>  Rule::notIn([auth()->id()]),
            'managed_users' =>  Rule::in([0]),
            'managed_locations' => Rule::in([0]),
            'assigned_assets' => Rule::in([0]),
            'assigned_licenses' => Rule::in([0]),
            'assigned_accessories' => Rule::in([0]),
            'deleted_at' => Rule::in([null]),
        ];
    }

    public function messages(): array
    {

        $user_to_delete = User::withTrashed()->with('managesUsers')->find(request()->route('user'));

        $messages = [];

        if ($user_to_delete) {

            $messages = array_merge([

                'user.exists' => trans('admin/users/message.user_not_found'),

                // Cannot delete yourself
                'user.not_in' => trans('admin/users/message.error.cannot_delete_yourself'),

                // managed users is not 0
                'managed_users.in' => trans_choice('admin/users/message.error.delete_has_users_var', $user_to_delete->managesUsers()->count(), ['count' => $user_to_delete->managesUsers()->count()]),

                // managed locations is not 0
                'managed_locations.in' => trans_choice('admin/users/message.error.delete_has_locations_var', $user_to_delete->managedLocations()->count(), ['count' => $user_to_delete->managedLocations()->count()]),


                // assigned_assets is not 0
                'assigned_assets.in' => trans_choice('admin/users/message.error.delete_has_assets_var', $user_to_delete->assets()->count(), ['count' => $user_to_delete->assets()->count()]),

                // assigned licenses is not 0
                'assigned_licenses.in' => trans_choice('admin/users/message.error.delete_has_licenses_var', $user_to_delete->licenses()->count(), ['count' => $user_to_delete->licenses()->count()]),

                // assigned accessories is not 0
                'assigned_accessories.in' => trans_choice('admin/users/message.error.delete_has_accessories_var', $user_to_delete->accessories()->count(), ['count' => $user_to_delete->accessories()->count()]),

                'deleted_at.in' => trans('admin/users/message.user_deleted_warning'),

            ], $messages);
        }

        return $messages;
    }
}
