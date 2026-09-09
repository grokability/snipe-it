<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Labels\CustomLabels\PreviewSheetLabel;
use App\Models\Labels\CustomLabels\PreviewTapeLabel;
use App\Models\Labels\CustomUserLabel;
use App\Models\Labels\DefaultLabel;
use App\Models\Labels\Label;
use App\Models\Setting;
use App\View\Label as LabelView;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Services\CustomLabelImportValidator;
use Illuminate\Validation\Rule;
use App\Models\Labels\RectangleSheet;
use App\Models\Labels\LabelPreviewAsset;
use App\Models\Labels\LabelGeometryRules;
use App\Models\Labels\CustomLabelFonts;

class LabelsController extends Controller
{
    /**
     * Returns the Label view with test data
     *
     * @author Grant Le Roux <grant.leroux+snipe-it@gmail.com>
     */
    public function show(string $labelName)
    {
        $labelName = str_replace('/', '\\', $labelName);

        if (str_starts_with($labelName, 'custom:')) {

            $id = (int)str_replace('custom:', '', $labelName);
            $customLabel = CustomUserLabel::find($id);

            if (!$customLabel) {
                $template = new DefaultLabel;
            } else {
                $baseTemplateName = data_get(
                    $customLabel->config_snapshot,
                    'template',
                    $customLabel->base_label
                );

                $template = $customLabel->type === 'tape'
                    ? new PreviewTapeLabel
                    : new PreviewSheetLabel;

                if ($baseTemplateName === 'StandardTape') {
                    // Nothing to seed. The custom snapshot contains
                    // the tape dimensions/content/support configuration.
                } else {
                    $baseLabel = CustomUserLabel::makeBaseLabel($baseTemplateName);

                    if ($baseLabel) {
                        $template->seedFromTemplate($baseLabel);
                    }
                }

                $template->applyEditorConfig($customLabel->config_snapshot ?? []);
            }

        } else {

            $template = $labelName === 'DefaultLabel'
                ? new DefaultLabel
                : Label::find($labelName);

        }

        $exampleAsset = LabelPreviewAsset::make();

        $customFieldColumns = CustomField::where('field_encrypted', '=', 0)->pluck('db_column');

        collect(explode(';', Setting::getSettings()->label2_fields))
            ->filter()
            ->each(function ($item) use ($customFieldColumns, $exampleAsset) {
                $pair = explode('=', $item);

                if (array_key_exists(1, $pair)) {
                    if ($customFieldColumns->contains($pair[1])) {
                        $exampleAsset->{$pair[1]} = "{{$pair[0]}}";
                    }
                }
            });

        $settings = Setting::getSettings();
        if (request()->has('settings')) {
            $overrides = request()->input('settings');
            foreach ($overrides as $key => $value) {
                $settings->$key = $value;
            }
        }

        return (new LabelView)
            ->with('assets', collect([$exampleAsset]))
            ->with('settings', $settings)
            ->with('template', $template)
            ->with('bulkedit', false)
            ->with('count', 0);

    }

    public function edit(CustomUserLabel $label, CustomLabelImportValidator $configValidator)
    {
        $config = $label->config_snapshot ?? [];
        $config = $configValidator->normalizeFonts($config);

        try {
            $config = $configValidator->validate(
                json_encode($config)
            );
        } catch (ValidationException $e) {
            return redirect()
                ->route('settings.labels.index')
                ->with(
                    'error',
                    trans('admin/labels/general.invalid_config')
                );
        }

        $selectedType = $label->type
            ?? data_get($config, 'type')
            ?? 'sheet';

        if ($selectedType === 'tape') {
            $previewLabel = (new PreviewTapeLabel())->applyEditorConfig($config);

            $config['dimensions'] = [
                'width' => $previewLabel->getWidth(),
                'height' => $previewLabel->getHeight(),
                'label_gap' => $previewLabel->getLabelGap(),
            ];
        } else {
            $previewLabel = (new PreviewSheetLabel())->applyEditorConfig($config);
        }

        return view('settings.label-edit', [
            'config' => $config,
            'sections' => $previewLabel->getEditorSections(),
            'selectedLabel' => $label->base_label,
            'selectedType' => $selectedType,
            'importedConfig' => null,
            'customLabel' => $label,
            'formMethod' => 'PUT',
            'formAction' => route('settings.labels.update', $label),
        ]);
    }

    public function update(Request $request, CustomUserLabel $label)
    {
        $type = $request->input('type', $label->type ?? 'sheet');
        $isTape = $type === 'tape';

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['sheet', 'tape'])],
            'content' => ['required', 'array'],
            'supports' => ['required', 'array'],
            'content.tag_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
            'content.title_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
            'content.field_label_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
            'content.field_value_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
        ];
        if ($isTape) {
            $rules += [
                'dimensions' => ['required', 'array'],
                'dimensions.width' => ['required', 'numeric', 'gt:0'],
                'dimensions.height' => ['required', 'numeric', 'gt:0'],
                'dimensions.label_gap' => ['nullable', 'numeric', 'min:0'],
            ];
        } else {
            $rules += [
                'page' => ['required', 'array'],
                'grid' => ['required', 'array'],
                'label' => ['required', 'array'],
            ];
            $rules += LabelGeometryRules::sheet();
        }

        $validated = $request->validate($rules);

        $supports = collect($validated['supports'])
            ->map(function ($value, $key) {
                if ($key === 'fields') {
                    return (int)$value;
                }

                return in_array($key, ['asset_tag', 'barcode_1d', 'barcode_2d', 'logo', 'title'], true)
                    ? (bool)$value
                    : $value;
            })
            ->toArray();

        $castNumeric = function ($array) {
            return collect($array)->map(function ($value) {
                return is_numeric($value) ? (float)$value : $value;
            })->toArray();
        };

        if ($isTape && $label->base_label === 'StandardTape') {
            $baseLabel = new PreviewTapeLabel;
        } else {
            $baseLabel = CustomUserLabel::makeBaseLabel(
                $label->base_label
            );
        }

        if (!$baseLabel) {
            return redirect()->back()
                ->with(
                    'error',
                    trans('admin/labels/general.base_label_missing')
                );
        }

        $previewLabelClass = $isTape ? new PreviewTapeLabel : new PreviewSheetLabel;

        $baseWorkingLabel = new $previewLabelClass();
        $baseWorkingLabel->seedFromTemplate($baseLabel);

        $baseConfig = $baseWorkingLabel->getEditorConfigSections();

        $content = $castNumeric($validated['content']);

        if ($isTape) {
            $submittedConfig = [
                'dimensions' => $castNumeric($validated['dimensions']),
                'content' => $content,
                'supports' => $supports,
            ];
        } else {
            $submittedConfig = [
                'page' => $castNumeric($validated['page']),
                'grid' => $castNumeric($validated['grid']),
                'label' => $castNumeric($validated['label']),
                'content' => $content,
                'supports' => $supports,
            ];
        }

        $mergedConfig = array_replace_recursive($baseConfig, $submittedConfig);

        $workingLabel = new $previewLabelClass();
        $workingLabel->seedFromTemplate($baseLabel);
        $workingLabel->applyEditorConfig($mergedConfig);

        $finalConfig = $workingLabel->getEditorConfigSections();

        $configSnapshot = [
            'unit' => 'mm',
            'template' => $label->base_label,
            'type' => $type,
            'name' => $validated['name'],
            ...$finalConfig,
        ];

        $overrides = CustomUserLabel::diffEditorConfig($finalConfig, $baseConfig);

        $label->update([
            'name' => $validated['name'],
            'type' => $type,
            'overrides' => $overrides,
            'config_snapshot' => $configSnapshot,
        ]);

        return redirect()
            ->route('settings.labels.index')
            ->with('success', trans('admin/labels/general.updated_successfully', ['item' => $label->name]));
    }

    public function store(Request $request)
    {
        $type = $request->input('type', 'sheet');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'template' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['sheet', 'tape'])],
            'content' => ['required', 'array'],
            'supports' => ['required', 'array'],
            'content.tag_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
            'content.title_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
            'content.field_label_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
            'content.field_value_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
        ];

        if ($type === 'sheet') {
            $rules += [
                'page' => ['required', 'array'],
                'grid' => ['required', 'array'],
                'label' => ['required', 'array'],
            ];
            $rules += LabelGeometryRules::sheet();
        }
        if ($type === 'tape') {
            $rules += [
                'dimensions' => ['required', 'array'],
                'dimensions.width' => ['required', 'numeric', 'gt:0'],
                'dimensions.height' => ['required', 'numeric', 'gt:0'],
                'dimensions.label_gap' => ['nullable', 'numeric', 'min:0'],
            ];
        }

        $validated = $request->validate($rules);

        $supports = collect($validated['supports'])
            ->map(function ($value, $key) {
                if ($key === 'fields') {
                    return (int)$value;
                }

                return in_array($key, ['asset_tag', 'barcode_1d', 'barcode_2d', 'logo', 'title'], true)
                    ? (bool)$value
                    : $value;
            })
            ->toArray();

        $castNumeric = function ($array) {
            return collect($array)->map(function ($value) {
                return is_numeric($value) ? (float)$value : $value;
            })->toArray();
        };

        $content = $castNumeric($validated['content']);

        $template = $validated['template'] ?? null;

        if ($type === 'tape' && $template === 'StandardTape') {
            $baseLabel = new PreviewTapeLabel;
        } else {
            $baseLabel = CustomUserLabel::makeBaseLabel($template);
        }

        if (!$baseLabel) {
            return redirect()->back()
                ->with('error', trans('admin/labels/general.base_label_missing'));
        }

        if ($type === 'tape') {
            $baseWorkingLabel = new PreviewTapeLabel;
            $workingLabel = new PreviewTapeLabel;

            $submittedConfig = [
                'dimensions' => $castNumeric($validated['dimensions']),
                'content' => $content,
                'supports' => $supports,
            ];
        } else {
            $baseWorkingLabel = new PreviewSheetLabel;
            $workingLabel = new PreviewSheetLabel;

            $submittedConfig = [
                'page' => $castNumeric($validated['page']),
                'grid' => $castNumeric($validated['grid']),
                'label' => $castNumeric($validated['label']),
                'content' => $content,
                'supports' => $supports,
            ];
        }

        $baseWorkingLabel->seedFromTemplate($baseLabel);
        $baseConfig = $baseWorkingLabel->getEditorConfigSections();

        $mergedConfig = array_replace_recursive($baseConfig, $submittedConfig);

        $workingLabel->seedFromTemplate($baseLabel);
        $workingLabel->applyEditorConfig($mergedConfig);

        $finalConfig = $workingLabel->getEditorConfigSections();

        $configSnapshot = [
            'unit' => 'mm',
            'template' => $template,
            'type' => $type,
            'name' => $validated['name'],
            ...$finalConfig,
        ];
        if ($type === 'tape') {
            $configSnapshot['dimensions'] = $submittedConfig['dimensions'];
        }

        $overrides = CustomUserLabel::diffEditorConfig($finalConfig, $baseConfig);

        $customLabel = CustomUserLabel::create([
            'name' => $validated['name'],
            'base_label' => $template,
            'type' => $type,
            'overrides' => $overrides,
            'config_snapshot' => $configSnapshot,
            'is_default' => false,
        ]);

        session()->forget('imported_label_config');

        return redirect()->route('settings.labels.index')
            ->with('success', trans('admin/labels/general.created_successfully', ['item' => $customLabel->name]));
    }

    public function create(Request $request, CustomLabelImportValidator $validator)
    {
        if ($request->filled('custom_label_id') || $request->filled('label') || session()->has('imported_label_config')) {
            return $this->createFromExisting($request, $validator);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:sheet,tape'],
            'page_size' => ['required_if:type,sheet', 'nullable', Rule::in(array_keys(RectangleSheet::supportedPageSizes())),],
            'label_gap' => ['nullable', 'numeric', 'min:0'],
            'rows' => ['required_if:type,sheet', 'numeric', 'min:1'],
            'columns' => ['required_if:type,sheet', 'numeric', 'min:1'],
            'label_width' => ['required', 'numeric', 'gt:0'],
            'label_height' => ['required', 'numeric', 'gt:0'],
        ]);
        //A new label will seed the DefaultLabel values
        if ($validated['type'] === 'sheet') {

            $selectedLabel = 'DefaultLabel';
            $page = RectangleSheet::supportedPageSize($validated['page_size']);
            $label = (new PreviewSheetLabel())->seedFromTemplate(new DefaultLabel());
            $config = $label->toEditorConfig();

            $config['page']['width'] = $page['width'];
            $config['page']['height'] = $page['height'];
            $config['label']['width'] = (float)$validated['label_width'];
            $config['label']['height'] = (float)$validated['label_height'];
            $config['grid']['rows'] = (int)$validated['rows'];
            $config['grid']['columns'] = (int)$validated['columns'];

            $label->applyEditorConfig($config);
        } else {
            $selectedLabel = 'StandardTape';

            $label = new PreviewTapeLabel(
                width: (float)$validated['label_width'],
                height: (float)$validated['label_height'],
                labelGap: (float)($validated['label_gap'] ?? 0),
            );
        }
        $config = $label->toEditorConfig();
        $config['name'] = 'New Label';

        return view('settings.label-edit', [
            'config' => $config,
            'sections' => $label->getEditorSections(),
            'selectedLabel' => $selectedLabel,
            'selectedType' => $validated['type'],
            'importedConfig' => null,
            'customLabel' => null,
            'formMethod' => 'POST',
            'formAction' => route('settings.labels.store'),
        ]);
    }

    public function destroy(CustomUserLabel $label)
    {
        $labelName = $label->name;
        if ($label->is_default) {
            return redirect()->route('settings.labels.index')
                ->with('warning', "$labelName can not be deleted. It is currently a default label.");
        }

        $label->delete();

        return redirect()
            ->route('settings.labels.index')
            ->with('success', $labelName . ' ' . trans('admin/labels/general.label_deleted_successfully'));
    }

    public function customLabelPreview(Request $request, string $labelName)
    {
        $request->validate([
            'content.tag_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
            'content.title_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
            'content.field_label_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
            'content.field_value_font' => ['nullable', 'string', Rule::in(CustomLabelFonts::ALLOWED),],
        ]);
        $labelName = str_replace('/', '\\', $labelName);

        $baseTemplate = match (true) {
            $labelName === 'DefaultLabel' => new DefaultLabel,
            $labelName === 'StandardTape' => null,
            default => Label::find($labelName),
        };

        $isTape =
                $request->input('type') === 'tape'
                || str_starts_with($labelName, 'Tapes\\')
                || $labelName === 'StandardTape';
        $editorConfig = [
            'content' => $request->input('content', []),
            'supports' => $request->input('supports', []),
        ];
        if ($isTape) {
            $editorConfig['dimensions'] = $request->input('dimensions', []);
            $template = new PreviewTapeLabel;
        } else {
            $editorConfig['page'] = $request->input('page', []);
            $editorConfig['grid'] = $request->input('grid', []);
            $editorConfig['label'] = $request->input('label', []);
            $template = new PreviewSheetLabel;
        }

        if ($baseTemplate) {
            $template->seedFromTemplate($baseTemplate);
        }

        $template->applyEditorConfig($editorConfig);

        $exampleAsset = LabelPreviewAsset::make();;

        $customFieldColumns = CustomField::where('field_encrypted', 0)->pluck('db_column');

        collect(explode(';', Setting::getSettings()->label2_fields))
            ->filter()
            ->each(function ($item) use ($customFieldColumns, $exampleAsset) {
                $pair = explode('=', $item);

                if (isset($pair[0], $pair[1]) && $customFieldColumns->contains($pair[1])) {
                    $exampleAsset->{$pair[1]} = "{{$pair[0]}}";
                }
            });

        $settings = Setting::getSettings();
        $settingOverrides = [
            'label2_title',
            'label2_asset_logo',
            'label2_fields',
            'label2_1d_type',
            'label2_2d_type',
            'label2_2d_target',
            'label2_2d_prefix',
            'label2_empty_row_count',
        ];

        foreach ($settingOverrides as $key) {
            if ($request->has($key)) {
                $settings->{$key} = $request->input($key);
            }
        }

        return (new LabelView)
            ->with('assets', collect([$exampleAsset]))
            ->with('settings', $settings)
            ->with('template', $template)
            ->with('bulkedit', false)
            ->with('count', 0);
    }

    public function createFromExisting(Request $request, CustomLabelImportValidator $configValidator)
    {
        if ($request->filled('custom_label_id')) {
            $customLabel = CustomUserLabel::findOrFail(
                $request->get('custom_label_id')
            );

            $config = $customLabel->config_snapshot ?? [];

            $config = $configValidator->normalizeFonts($config);

            $config = $configValidator->validate(
                json_encode($config)
            );

            $config['name'] = 'Copy of ' . $customLabel->name;

            $previewLabel = $customLabel->type === 'tape'
                ? new PreviewTapeLabel
                : new PreviewSheetLabel;

            $baseLabel = CustomUserLabel::makeBaseLabel(
                $customLabel->base_label
            );

            if ($baseLabel) {
                $previewLabel->seedFromTemplate($baseLabel);
            }

            $previewLabel->applyEditorConfig($config);

            return view('settings.label-edit', [
                'config' => $config,
                'sections' => $previewLabel->getEditorSections(),
                'selectedLabel' => $customLabel->base_label,
                'selectedType' => $customLabel->type,
                'importedConfig' => null,
                'customLabel' => null,
                'formMethod' => 'POST',
                'formAction' => route('settings.labels.store'),
            ]);
        }

        $selectedLabel = $request->get('label');

        if ($selectedLabel) {
            $selectedLabel = str_replace('/', '\\', $selectedLabel);
        }

        $importedConfig = $request->boolean('import') ? session('imported_label_config') : null;

        try {
            $template = match (true) {
                $selectedLabel === 'StandardTape' => new PreviewTapeLabel,
                $selectedLabel !== null => Label::find($selectedLabel),
                default => new DefaultLabel,
            };
        } catch (\Throwable $e) {
            $template = null;
        }

        if (!$template) {
            $template = new DefaultLabel;
            $selectedLabel = 'DefaultLabel';
        }

        $label = $this->previewLabelForTemplate($template);

        if ($importedConfig) {
            $label->applyEditorConfig($importedConfig);
            $config = $importedConfig;
        } else {
            $config = $label->toEditorConfig();
        }

        $type = data_get($config, 'type');

        if ($type === null) {
            $type = str_starts_with($selectedLabel ?? '', 'Tapes\\')
                ? 'tape'
                : 'sheet';
        }

        if (!$importedConfig) {
            $config['name'] = 'Copy of ' . class_basename($selectedLabel);
        }

        return view('settings.label-edit', [
            'config' => $config,
            'sections' => $label->getEditorSections(),
            'selectedLabel' => $selectedLabel,
            'selectedType' => $type,
            'importedConfig' => $importedConfig,
            'customLabel' => null,
            'formMethod' => 'POST',
            'formAction' => route('settings.labels.store'),
        ]);
    }
    protected function previewLabelForTemplate($template)
    {
        if ($template instanceof PreviewTapeLabel) {
            return $template;
        }

        $name = $template->getName();

        if (str_starts_with($name, 'Tapes\\')) {
            return (new PreviewTapeLabel)->seedFromTemplate($template);
        }

        return (new PreviewSheetLabel)->seedFromTemplate($template);
    }
}
