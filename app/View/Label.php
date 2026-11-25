<?php

namespace App\View;

use App\Enums\FileStorage;
use App\Helpers\Helper;
use App\Helpers\StorageHelper;
use App\Http\Controllers\QrCodeController;
use App\Models\Labels\CustomLabels\PreviewSheetLabel;
use App\Models\Labels\CustomLabels\PreviewTapeLabel;
use App\Models\Labels\CustomUserLabel;
use App\Models\Labels\Field;
use App\Models\Labels\Label as LabelModel;
use App\Models\Labels\Sheet;
use Com\Tecnick\Barcode\Barcode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Traits\Macroable;
use TCPDF;
use Throwable;

class Label implements View
{
    use Macroable { __call as macroCall; }

    protected const NAME = 'label';

    /**
     * A Collection of passed data.
     *
     * @var Collection
     */
    protected $data;

    /**
     * TCPDF output destination.
     * "I" - inline by default.
     * See TCPDF's Output method for details.
     */
    private string $destination = 'I';

    public function __construct()
    {
        $this->data = new Collection;
    }

    /**
     * Render the PDF label.
     */
    public function render(?callable $callback = null)
    {
        $settings = $this->data->get('settings');
        $assets = $this->data->get('assets');
        $offset = $this->data->get('offset');
        $template = $this->data->get('template');

        if ($template === null) {
            $templateSetting = $settings->label2_template ?? 'DefaultLabel';

            if (str_starts_with((string) $templateSetting, 'custom:')) {
                $customLabel = CustomUserLabel::find((int) str_replace('custom:', '', $templateSetting));

                if ($customLabel) {
                    $baseLabel = CustomUserLabel::makeBaseLabel(
                        data_get($customLabel->config_snapshot, 'template', $customLabel->base_label)
                    );

                    $template = data_get($customLabel->config_snapshot, 'type') === 'tape'
                        ? new PreviewTapeLabel
                        : new PreviewSheetLabel;

                    if ($baseLabel) {
                        $template->seedFromTemplate($baseLabel);
                    }

                    $template->applyEditorConfig($customLabel->config_snapshot ?? []);
                }
            } else {
                $template = LabelModel::find($templateSetting);
            }
        }
        // If disabled, pass to legacy view
        if ((! $settings->label2_enable)) {
            return view('hardware/labels')
                ->with('assets', $assets)
                ->with('settings', $settings)
                ->with('bulkedit', $this->data->get('bulkedit'))
                ->with('count', $this->data->get('count'))
                ->with('barcode_urls', $this->prepareLegacyBarcodeUrls($assets, $settings));
        }

        if ($template === null) {
            $template = LabelModel::find($settings->label2_template);
        }

        if ($template === null) {
            return redirect()->route('settings.labels.index')->with('error', trans('admin/settings/message.labels.null_template'));
        }

        $template->validate();

        $labelGap = method_exists($template, 'getLabelGap')
            ? (float) $template->getLabelGap()
            : 0.0;

        $pdf = new TCPDF(
            $template->getOrientation(),
            $template->getUnit(),
            [0 => $template->getWidth() + $labelGap, 1 => $template->getHeight(), 'Rotate' => $template->getRotation()]
        );

        // Required for CJK languages, otherwise the embedded font can get too massive
        $pdf->SetFontSubsetting(true);

        // Reset parameters
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, null, true);
        $pdf->SetCellMargins(0, 0, 0, 0);
        $pdf->SetCellPaddings(0, 0, 0, 0);
        $pdf->setCreator('Snipe-IT');
        $pdf->SetSubject('Asset Labels');
        $template->preparePDF($pdf);

        // Get fields from settings
        $fieldDefinitions = collect(explode(';', $settings->label2_fields))
            ->filter(fn ($fieldString) => ! empty($fieldString))
            ->map(fn ($fieldString) => Field::fromString($fieldString));

        // Prepare data
        $data = $assets
            ->map(function ($asset) use ($template, $settings, $fieldDefinitions) {

                $assetData = new Collection;

                $assetData->put('asset', $asset);
                $assetData->put('id', $asset->id);
                $assetData->put('tag', $asset->asset_tag);

                if ($template->getSupportTitle() && ! empty($settings->label2_title)) {
                    $title = str_replace('{COMPANY}', data_get($asset, 'company.name'), $settings->label2_title);
                    $assetData->put('title', $title);
                }

                if ($template->getSupportLogo()) {

                    $logo = null;
                    // Should we use the assets assigned company logo? (A.K.A. "Is `Labels > Use Asset Logo` enabled?"), and do we have a company logo?
                    // StorageHelper::readablePath returns a local path for any disk driver:
                    // direct passthrough on local, temp-file creation on s3. TCPDF and the
                    // downstream getimagesize() both need a real path, so it has to land on the
                    // disk.
                    if ($settings->label2_asset_logo && $asset->company && $asset->company->image != '') {
                        $logo = StorageHelper::readablePath('companies/'.e($asset->company->image));
                    } elseif (! empty($settings->label_logo)) {
                        // Use the general site label logo, if available
                        $logo = StorageHelper::readablePath(e(basename($settings->label_logo)));
                    } elseif (! empty($asset->is_label_preview)) {
                        $logo = public_path('img/label-preview-logo.png');
                    }
                    if (! empty($logo)) {
                        $assetData->put('logo', $logo);
                    }
                }

                if ($template->getSupport1DBarcode()) {
                    $barcode1DType = $settings->label2_1d_type;
                    if ($barcode1DType != 'none') {
                        $assetData->put('barcode1d', (object) [
                            'type' => $barcode1DType,
                            'content' => $asset->asset_tag,
                        ]);
                    }
                }

                if ($template->getSupport2DBarcode()) {
                    $barcode2DType = $settings->label2_2d_type;
                    if (($barcode2DType != 'none') && (! is_null($barcode2DType))) {

                        $label2_2d_prefix = $settings->label2_2d_prefix ? e($settings->label2_2d_prefix) : '';
                        switch ($settings->label2_2d_target) {
                            case 'ht_tag':
                                $barcode2DTarget = route('ht/assetTag', $asset->asset_tag);
                                break;
                            case 'plain_asset_id':
                                $barcode2DTarget = $label2_2d_prefix.(string) $asset->id;
                                break;
                            case 'plain_asset_tag':
                                $barcode2DTarget = $label2_2d_prefix.$asset->asset_tag;
                                break;
                            case 'plain_serial_number':
                                $barcode2DTarget = $label2_2d_prefix.$asset->serial;
                                break;
                            case 'plain_model_number':
                                $barcode2DTarget = $label2_2d_prefix.$asset->model->model_number ?? '';
                                break;
                            case 'plain_model_name':
                                $barcode2DTarget = $label2_2d_prefix.$asset->model->display_name ?? '';
                                break;
                            case 'plain_manufacturer_name':
                                $barcode2DTarget = $label2_2d_prefix.$asset->model->display_name;
                                break;
                            case 'plain_location_name':
                                $barcode2DTarget = $label2_2d_prefix.$asset->location->name;
                                break;
                            case 'location':
                                $barcode2DTarget = $asset->location_id
                                    ? route('locations.show', $asset->location_id)
                                    : null;
                                break;
                            case 'hardware_id':
                            default:
                                $barcode2DTarget = route('hardware.show', $asset);
                                break;
                        }
                        $assetData->put('barcode2d', (object) [
                            'type' => $barcode2DType,
                            'content' => $barcode2DTarget,
                        ]);
                    }
                }

                $fields = $fieldDefinitions
                    ->map(fn ($field) => $field->toArray($asset))
                    ->filter(fn ($field) => $field != null)
                    ->reduce(function ($myFields, $field) {
                        // Remove Duplicates
                        $toAdd = $field
                            ->filter(fn ($o) => ! $myFields->contains('dataSource', $o['dataSource']))
                            // For fields that have multiple options, we need to combine them
                            // into a single field so all values are displayed.
                            ->reduce(function ($previous, $current) {
                                // On the first iteration, we simply return the item.
                                // If there is only one item to be processed for the row
                                // then this effectively skips everything below this if block.
                                if (is_null($previous)) {
                                    return $current;
                                }

                                // At this point, we are dealing with a row with multiple items being displayed.
                                // We need to combine the label and value of the current item with the previous item.

                                // The end result of this will be in this format:
                                // {labelOne} {valueOne} | {labelTwo} {valueTwo} | {labelThree} {valueThree}
                                $previous['value'] = trim(implode(' | ', [
                                    implode(' ', [$previous['label'], $previous['value']]),
                                    implode(' ', [$current['label'], $current['value']]),
                                ]));

                                // We'll set the label to an empty string since we
                                // injected the label into the value field above.
                                $previous['label'] = '';

                                return $previous;
                            });

                        return $toAdd ? $myFields->push($toAdd) : $myFields;
                    }, new Collection);

                // Stick to the template's physical capacity because the
                // ->take() below drops everything past it anyway. This
                // also handles unbounded or negative values reaching
                // range(), which otherwise materializes an N-element array
                // before the ->take() can throw it away
                $emptyRowsCount = min(max((int) $settings->label2_empty_row_count, 0), $template->getSupportFields());
                if ($emptyRowsCount) {
                    // Create empty rows
                    $emptyRows = collect(range(1, $emptyRowsCount))->map(function () {
                        return [
                            'label' => '',
                            'value' => '',
                            'dataSource' => null,
                        ];
                    });

                    // Prepend empty rows to the existing fields
                    $fieldsWithEmpty = $emptyRows->merge($fields);

                    $assetData->put('fields', $fieldsWithEmpty->take($template->getSupportFields()));

                    return $assetData;
                } else {
                    $assetData->put('fields', $fields->take($template->getSupportFields()));

                    return $assetData;
                }

            });

        if ($template instanceof Sheet) {
            $template->setLabelIndexOffset($offset ?? 0);
        }
        $template->writeAll($pdf, $data);

        $filename = $assets->count() > 1 ? 'assets.pdf' : $assets->first()->asset_tag.'.pdf';
        $pdf->Output($filename, $this->destination);
    }

    /**
     * Generate QR and 1D barcode PNGs for the legacy label template
     * once per render, so the Blade output can point its <img> tags at
     * the storage URL directly instead of round-tripping through PHP
     * per image. On S3, 60 inline <img src> fetches per 30-label sheet
     * each paid session + auth + policy + headObject + getObject +
     * stream, timing out the print preview. This pays those costs once
     * server-side and lets the browser fetch the PNGs in parallel from
     * S3.
     */
    private function prepareLegacyBarcodeUrls(Collection $assets, $settings): array
    {
        $publicDisk = Storage::disk('public');
        $barcodeDir = FileStorage::Barcodes->publicPath();

        // Loose comparison on the toggle values to match the Blade
        // template's `== '1'` gating. These columns can round-trip
        // between int and string depending on how they were last
        // written.
        $qrEnabled = $settings->qr_code == '1' && $settings->label2_2d_type !== 'none';
        $barcodeEnabled = $settings->alt_barcode_enabled == '1' && $settings->label2_1d_type !== '';

        if (! $qrEnabled && ! $barcodeEnabled) {
            return [];
        }

        $qrSize = $qrEnabled ? Helper::barcodeDimensions($settings->label2_2d_type) : null;

        // The 1D barcode width math mirrors AssetsController::getBarCode
        // so the generated file matches that endpoint byte-for-byte and
        // reuses the same cache key.
        $barcodeWidth = $barcodeEnabled
            ? min(300, ($settings->labels_width - $settings->labels_display_sgutter) * 200.000000000001)
            : null;

        // One list-objects call up front, then in-memory membership
        // checks for every asset. On S3 this replaces N headObject
        // round-trips per sheet (500+ labels times two barcodes each
        // was timing the print preview out) with one ListObjects call
        // that returns up to 1000 keys per page.
        $cachedFiles = array_flip($publicDisk->files(rtrim($barcodeDir, '/')));

        $urls = [];

        foreach ($assets as $asset) {
            if (! isset($asset->id, $asset->asset_tag)) {
                continue;
            }

            $perAsset = [];

            if ($qrEnabled) {
                $qrKey = QrCodeController::cacheKey('hardware', $asset->id);

                if (! isset($cachedFiles[$qrKey])) {
                    $barcode = new Barcode;
                    $barcodeObj = $barcode->getBarcodeObj(
                        $settings->label2_2d_type,
                        route('hardware.show', $asset->id),
                        $qrSize['height'],
                        $qrSize['width'],
                        'black',
                        [-2, -2, -2, -2]
                    );
                    $publicDisk->put($qrKey, $barcodeObj->getPngData());
                    $cachedFiles[$qrKey] = true;
                }

                $perAsset['qr'] = $publicDisk->url($qrKey);
            }

            if ($barcodeEnabled) {
                // 1D cache key matches AssetsController::getBarCode.
                $barcodeKey = $barcodeDir.str_slug($settings->label2_1d_type).'-'.str_slug($asset->asset_tag).'.png';

                if (! isset($cachedFiles[$barcodeKey])) {
                    try {
                        $barcode = new Barcode;
                        $barcodeObj = $barcode->getBarcodeObj($settings->label2_1d_type, $asset->asset_tag, $barcodeWidth, 50);
                        $publicDisk->put($barcodeKey, $barcodeObj->getPngData());
                        $cachedFiles[$barcodeKey] = true;
                    } catch (Throwable) {
                        // Fall back to the "invalid" fixture the per-request
                        // endpoint also hands out when getBarcodeObj throws.
                        $perAsset['barcode'] = $publicDisk->url($barcodeDir.'invalid_barcode.gif');
                    }
                }

                $perAsset['barcode'] = $perAsset['barcode'] ?? $publicDisk->url($barcodeKey);
            }

            $urls[$asset->id] = $perAsset;
        }

        return $urls;
    }

    /**
     * Add a piece of data.
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        $this->data->put($key, $value);

        return $this;
    }

    /**
     * Get the array of view data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name()
    {
        return $this->getName();
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
