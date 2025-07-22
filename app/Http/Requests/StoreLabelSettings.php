<?php

namespace App\Http\Requests;


use App\Models\Labels\Label;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreLabelSettings extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('superuser');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $names = Label::find()?->map(function ($label) {
            return $label->getName();
        })->values()->toArray();

        if (empty($this->input('label2_template'))) {
            $this->merge([
                'label2_template' => 'DefaultLabel',
            ]);
        }

        return [
            'labels_per_page'                     => 'numeric',
            'labels_width'                        => 'numeric|min:0.1',
            'labels_height'                       => 'numeric|min:0.1',
            'labels_pmargin_left'                 => 'numeric|nullable',
            'labels_pmargin_right'                => 'numeric|nullable',
            'labels_pmargin_top'                  => 'numeric|nullable',
            'labels_pmargin_bottom'               => 'numeric|nullable',
            'labels_display_bgutter'              => 'numeric|nullable',
            'labels_display_sgutter'              => 'numeric|nullable',
            'labels_fontsize'                     => 'numeric|min:5',
            'labels_pagewidth'                    => 'numeric|nullable',
            'labels_pageheight'                   => 'numeric|nullable',
            'qr_text'                             => 'max:31|nullable',
            'label2_template'                     => [
                'required',
                Rule::in($names),
            ],
        ];
    }
}
