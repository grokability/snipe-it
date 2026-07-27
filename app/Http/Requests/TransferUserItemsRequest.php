<?php

namespace App\Http\Requests;

use App\Models\Asset;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TransferUserItemsRequest extends Request
{
    public function authorize(): bool
    {
        $sourceUser = $this->route('user');

        return $sourceUser
            && Gate::allows('view', $sourceUser)
            && Gate::allows('checkin', Asset::class)
            && Gate::allows('checkout', Asset::class);
    }

    public function rules(): array
    {
        return [
            'target_user_id' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'asset_ids' => ['nullable', 'array'],
            'asset_ids.*' => ['integer'],
            'accessory_checkout_ids' => ['nullable', 'array'],
            'accessory_checkout_ids.*' => ['integer'],
            'license_seat_ids' => ['nullable', 'array'],
            'license_seat_ids.*' => ['integer'],
            'note' => ['required', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $sourceUser = $this->route('user');
            if ($sourceUser && (int) $this->input('target_user_id') === (int) $sourceUser->id) {
                $validator->errors()->add(
                    'target_user_id',
                    trans('admin/users/general.transfer.target_same_as_source')
                );
            }

            $assetIds = (array) $this->input('asset_ids', []);
            $accessoryIds = (array) $this->input('accessory_checkout_ids', []);
            $licenseSeatIds = (array) $this->input('license_seat_ids', []);

            if (empty($assetIds) && empty($accessoryIds) && empty($licenseSeatIds)) {
                $validator->errors()->add(
                    'asset_ids',
                    trans('admin/users/general.transfer.nothing_selected')
                );
            }
        });
    }
}
