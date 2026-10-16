<?php

namespace App\Livewire;

use App\Helpers\Helper;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CustomFieldEditor extends Component
{
    // Field identifier — null when creating, set when editing.
    #[Locked]
    public ?int $fieldId = null;

    // Whether this is an edit (vs create). Controls validation uniqueness
    // and format-lock behaviour.
    #[Locked]
    public bool $isEdit = false;

    // Core field properties wired to the form.
    public string $name = '';

    public string $element = 'text';

    public string $format = 'ANY';

    public string $custom_format = '';

    public bool $field_encrypted = false;

    public string $field_values = '';

    public string $help_text = '';

    public bool $is_unique = false;

    public bool $show_in_email = false;

    public bool $display_in_user_view = false;

    public bool $show_in_listview = false;

    public bool $show_in_requestable_list = false;

    public bool $display_checkin = false;

    public bool $display_checkout = false;

    public bool $display_audit = false;

    public bool $auto_add_to_fieldsets = false;

    // Array of fieldset IDs to associate. Keys are fieldset IDs, values are
    // the fieldset ID (matching the associate_fieldsets[id]=id POST pattern).
    public array $associate_fieldsets = [];

    // Signature accepts an optional CustomField for the edit route
    // (fields/{field}/edit) and no argument for the create route
    // (fields/create). Livewire resolves the route parameter via Laravel's
    // model binding and passes it here.
    public function mount(?CustomField $field = null): void
    {
        if ($field?->exists) {
            $this->authorize('update', CustomField::class);
            $this->fieldId = $field->id;
            $this->isEdit = true;
            $this->name = $field->name ?? '';
            $this->element = $field->element ?? 'text';
            $this->format = $field->format ?? 'ANY';
            $this->custom_format = '';
            if (stripos((string) $field->format, 'regex') === 0
                && $field->format !== CustomField::PREDEFINED_FORMATS['MAC']
            ) {
                $this->format = 'CUSTOM REGEX';
                $this->custom_format = $field->format;
            }
            $this->field_encrypted = (bool) $field->field_encrypted;
            $this->field_values = $field->field_values ?? '';
            $this->help_text = $field->help_text ?? '';
            $this->is_unique = (bool) $field->is_unique;
            $this->show_in_email = (bool) $field->show_in_email;
            $this->display_in_user_view = (bool) $field->display_in_user_view;
            $this->show_in_listview = (bool) $field->show_in_listview;
            $this->show_in_requestable_list = (bool) $field->show_in_requestable_list;
            $this->display_checkin = (bool) $field->display_checkin;
            $this->display_checkout = (bool) $field->display_checkout;
            $this->display_audit = (bool) $field->display_audit;
            $this->auto_add_to_fieldsets = (bool) $field->auto_add_to_fieldsets;
            $this->associate_fieldsets = $field->fieldset->pluck('id')->mapWithKeys(
                fn ($id) => [$id => $id]
            )->toArray();
        } else {
            $this->authorize('create', CustomField::class);
        }
    }

    // Returns the list of element keys that are allowed given the current format.
    #[Computed]
    public function allowedElementKeys(): array
    {
        return CustomField::allowedElementKeysForFormat($this->format);
    }

    // Whether the format dropdown is locked entirely (readonly). Only true
    // when editing a field that ALREADY has DATE/DATETIME format on disk,
    // because those are backed by native date columns and can't be altered
    // without a schema change. Other format changes are allowed but the
    // UI warns the user first (see showFormatChangeWarning).
    #[Computed]
    public function isFormatLockedForEdit(): bool
    {
        if (! $this->isEdit || ! $this->fieldId) {
            return false;
        }

        $orig = CustomField::find($this->fieldId)?->getOriginalFormat();

        return in_array($orig, ['DATE', 'DATETIME']);
    }

    // Whether to show a warning that changing format may invalidate
    // existing asset values under the new validation rule. Shown when
    // editing an existing (non-locked) field where the user has picked a
    // format different from what's persisted.
    #[Computed]
    public function showFormatChangeWarning(): bool
    {
        if (! $this->isEdit || ! $this->fieldId) {
            return false;
        }

        $orig = CustomField::find($this->fieldId)?->getOriginalFormat();

        return $orig !== null && $orig !== $this->format;
    }

    // Whether the current element is forced by the format (no user choice).
    #[Computed]
    public function elementForcedByFormat(): bool
    {
        return in_array($this->format, ['DATE', 'DATETIME']);
    }

    // Whether encryption is allowed for the current element/format combo.
    // Layers the UI-only rule (no encryption toggle on edit) on top of the
    // model's canEncryptFor() base compatibility check.
    #[Computed]
    public function canEncrypt(): bool
    {
        if ($this->isEdit) {
            return false;
        }

        return CustomField::canEncryptFor($this->element, $this->format);
    }

    // Whether the field_values textarea should be shown.
    #[Computed]
    public function showFieldValues(): bool
    {
        return CustomField::elementRequiresFieldValues($this->element);
    }

    // Whether the custom regex input should be shown.
    #[Computed]
    public function showCustomRegex(): bool
    {
        return $this->format === 'CUSTOM REGEX';
    }

    // Whether to show the help note explaining encrypt use with date_picker/datetime_picker.
    #[Computed]
    public function showFormatPickerNote(): bool
    {
        return in_array($this->element, ['date_picker', 'datetime_picker'])
            && $this->format === 'ANY';
    }

    // Whether to show the encrypt-disabled note inside the encryption section.
    #[Computed]
    public function showEncryptDisabledNote(): bool
    {
        return in_array($this->format, ['DATE', 'DATETIME']);
    }

    // Full list of all element options for use in the view.
    public function elementOptions(): array
    {
        return [
            'text' => trans('admin/custom_fields/general.types.text'),
            'listbox' => trans('admin/custom_fields/general.types.listbox'),
            'multi-listbox' => trans('admin/custom_fields/general.types.multi-listbox'),
            'textarea' => trans('admin/custom_fields/general.types.textarea'),
            'markdown-textarea' => trans('admin/custom_fields/general.types.markdown-textarea'),
            'checkbox' => trans('admin/custom_fields/general.types.checkbox'),
            'radio' => trans('admin/custom_fields/general.types.radio'),
            'date_picker' => trans('admin/custom_fields/general.types.date_picker'),
            'datetime_picker' => trans('admin/custom_fields/general.types.datetime_picker'),
        ];
    }

    // Livewire lifecycle: fires when any property is updated.
    public function updated(string $property, mixed $value): void
    {
        if ($property === 'format') {
            $this->enforceElementForFormat();
        }

        if ($property === 'field_encrypted' && $value) {
            // Encryption disables these display options.
            $this->show_in_email = false;
            $this->display_in_user_view = false;
            $this->is_unique = false;
            $this->show_in_requestable_list = false;
        }

        // Clear field_encrypted if element becomes checkbox/radio.
        if ($property === 'element' && in_array($value, ['multi-listbox', 'checkbox', 'radio'])) {
            $this->field_encrypted = false;
        }
    }

    // When format changes, force element to a valid choice.
    protected function enforceElementForFormat(): void
    {
        $allowed = $this->allowedElementKeys;

        if ($this->format === 'DATE') {
            $this->element = 'date_picker';
            $this->field_encrypted = false;

            return;
        }

        if ($this->format === 'DATETIME') {
            $this->element = 'datetime_picker';
            $this->field_encrypted = false;

            return;
        }

        // If current element is no longer in the allowed set, fall back to first.
        if (! in_array($this->element, $allowed)) {
            $this->element = $allowed[0] ?? 'text';
        }

        // If element was date_picker/datetime_picker but format is now not DATE/DATETIME,
        // reset to text.
        if (in_array($this->element, ['date_picker', 'datetime_picker'])
            && ! in_array($this->format, ['DATE', 'DATETIME'])
        ) {
            $this->element = 'text';
        }
    }

    // Validate and persist the field, then redirect.
    public function save(): void
    {
        $this->authorize($this->isEdit ? 'update' : 'create', CustomField::class);

        // Build validation rules matching CustomFieldRequest.
        $nameRule = $this->isEdit ? 'required' : 'required|unique:custom_fields,name';

        $rules = [
            'name' => $nameRule,
            'element' => 'required|in:text,listbox,multi-listbox,textarea,markdown-textarea,checkbox,radio,date_picker,datetime_picker',
            'format' => 'nullable|string|max:191',
            'custom_format' => 'valid_regex',
        ];

        $validator = Validator::make(
            [
                'name' => $this->name,
                'element' => $this->element,
                'format' => $this->format,
                'custom_format' => $this->custom_format,
                'associate_fieldsets' => $this->associate_fieldsets,
            ],
            $rules
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $msg) {
                    $this->addError($field, $msg);
                }
            }

            return;
        }

        // Resolve the effective format value.
        $effectiveFormat = $this->format;
        if ($this->format === 'CUSTOM REGEX' && $this->custom_format !== '') {
            $effectiveFormat = $this->custom_format;
        }

        // checkbox/radio always use ANY format.
        if (in_array($this->element, ['multi-listbox', 'checkbox', 'radio'])) {
            $effectiveFormat = 'ANY';
        }

        if ($this->isEdit) {
            $field = CustomField::findOrFail($this->fieldId);
            $field->name = trim($this->name);
            $field->element = $this->element;
            $field->field_values = $this->field_values;
            $field->help_text = $this->help_text;
            $field->show_in_listview = $this->show_in_listview;
            $field->auto_add_to_fieldsets = $this->auto_add_to_fieldsets;
            $field->display_checkin = $this->display_checkin;
            $field->display_checkout = $this->display_checkout;
            $field->display_audit = $this->display_audit;

            // Only update display options when not encrypted.
            if (! $field->field_encrypted) {
                $field->show_in_email = $this->show_in_email;
                $field->display_in_user_view = $this->display_in_user_view;
                $field->is_unique = $this->is_unique;
                $field->show_in_requestable_list = $this->show_in_requestable_list;
            }

            $field->format = $effectiveFormat;
        } else {
            $showInEmail = $this->field_encrypted ? false : $this->show_in_email;
            $displayInUserView = $this->field_encrypted ? false : $this->display_in_user_view;

            $field = new CustomField([
                'name' => trim($this->name),
                'element' => $this->element,
                'help_text' => $this->help_text,
                'field_values' => $this->field_values,
                'field_encrypted' => $this->field_encrypted ? 1 : 0,
                'show_in_email' => $showInEmail ? 1 : 0,
                'is_unique' => $this->is_unique ? 1 : 0,
                'display_in_user_view' => $displayInUserView ? 1 : 0,
                'auto_add_to_fieldsets' => $this->auto_add_to_fieldsets ? 1 : 0,
                'show_in_listview' => $this->show_in_listview ? 1 : 0,
                'show_in_requestable_list' => $this->show_in_requestable_list ? 1 : 0,
                'display_checkin' => $this->display_checkin ? 1 : 0,
                'display_checkout' => $this->display_checkout ? 1 : 0,
                'display_audit' => $this->display_audit ? 1 : 0,
            ]);

            // Assigned directly rather than through mass assignment because
            // created_by is intentionally not in $fillable — we don't want
            // it settable from arbitrary request payloads.
            $field->created_by = auth()->id();
            $field->format = $effectiveFormat;
        }

        if ($field->save()) {
            // Sync fieldset associations. Filter to truthy values then take the keys
            // which are the fieldset IDs.
            $fieldsetIds = array_keys(array_filter($this->associate_fieldsets));
            $field->fieldset()->sync($fieldsetIds);

            $message = $this->isEdit
                ? trans('admin/custom_fields/message.field.update.success')
                : trans('admin/custom_fields/message.field.create.success');

            $this->redirect(route('fields.index'), navigate: false);
            session()->flash('success', $message);

            return;
        }

        // ValidatingTrait::save() returns false on validation failure
        // without throwing, stashing the actual messages on
        // $field->getErrors(). Surface those directly against their
        // attributes so the admin sees "The name has already been
        // taken" (or whatever) instead of a generic "please try
        // again" with no additional context.
        $errors = $field->getErrors();
        if ($errors->isNotEmpty()) {
            foreach ($errors->messages() as $attribute => $messages) {
                foreach ($messages as $message) {
                    $this->addError($attribute, $message);
                }
            }

            return;
        }

        $this->addError(
            'name',
            $this->isEdit
                ? trans('admin/custom_fields/message.field.update.error')
                : trans('admin/custom_fields/message.field.create.error')
        );
    }

    public function render()
    {
        return view('livewire.custom-field-editor', [
            'fieldsets' => CustomFieldset::orderBy('name')->get(),
            'predefinedFormats' => Helper::predefined_formats(),
            'elementOptions' => $this->elementOptions(),
        ])->layout('layouts.livewire-default', [
            'title' => trans('admin/custom_fields/general.custom_fields'),
            'helpText' => trans('admin/custom_fields/general.about_fieldsets_text'),
            'helpPosition' => 'right',
        ]);
    }
}
