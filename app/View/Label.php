<?php

namespace App\View;

use App\Models\Labels\Field;
use App\Models\Labels\Label as LabelModel;
use App\Models\Labels\Sheet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Traits\Macroable;
use TCPDF;

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
     *
     * @var string
     */
    private string $destination = 'I';

    public function __construct() {
        $this->data = new Collection();
    }

    /**
     * Render the PDF label.
     *
     * @param  callable|null  $callback
     */
    public function render(callable $callback = null)
    {
        $settings = $this->data->get('settings');
        $assets = $this->data->get('assets');
        $offset = $this->data->get('offset');


        // If disabled, pass to legacy view
        if ((!$settings->label2_enable)) {
            return view('hardware/labels')
                ->with('assets', $assets)
                ->with('settings', $settings)
                ->with('bulkedit', $this->data->get('bulkedit'))
                ->with('count', $this->data->get('count'));
        }

            $template = LabelModel::find($settings->label2_template);

        if ($template === null) {
            return redirect()->route('settings.labels.index')->with('error', trans('admin/settings/message.labels.null_template'));
        }

        $template->validate();

        $pdf = new TCPDF(
            $template->getOrientation(),
            $template->getUnit(),
            [0 => $template->getWidth(), 1 => $template->getHeight(), 'Rotate' => $template->getRotation()]
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
            ->filter(fn($fieldString) => !empty($fieldString))
            ->map(fn($fieldString) => Field::fromString($fieldString));

        // Prepare data
        $data = $assets
            ->map(function ($asset) use ($template, $settings, $fieldDefinitions) {

                $assetData = new Collection();

                $assetData->put('asset', $asset);
                $assetData->put('id', $asset->id);
                $assetData->put('tag', $asset->asset_tag);

                if ($template->getSupportTitle() && !empty($settings->label2_title)) {
                    $title = str_replace('{COMPANY}', data_get($asset, 'company.name'), $settings->label2_title);
                    $assetData->put('title', $title);
                }

                if ($template->getSupportLogo()) {

                    $logo = null;

                    // Should we use the assets assigned company logo? (A.K.A. "Is `Labels > Use Asset Logo` enabled?"), and do we have a company logo?
                    if ($settings->label2_asset_logo && $asset->company && $asset->company->image!='') {
                        $logo = Storage::disk('public')->path('companies/'.e($asset->company->image));
                    } elseif (!empty($settings->label_logo)) {
                        // Use the general site label logo, if available
                        $logo = Storage::disk('public')->path('/'.e($settings->label_logo));
                    }

                    if (!empty($logo)) {
                        $assetData->put('logo', $logo);
                    }
                }


                    if ($template->getSupport1DBarcode()) {
                        $barcode1DType = $settings->label2_1d_type;
                        if ($barcode1DType != 'none') {
                            $assetData->put('barcode1d', (object)[
                                'type' => $barcode1DType,
                                'content' => $asset->asset_tag,
                            ]);
                        }
                    }
              
            if ($template->getSupport2DBarcode()) {
                    $barcode2DType = $settings->label2_2d_type;
                if (($barcode2DType != 'none') && (!is_null($barcode2DType))) {
                        switch ($settings->label2_2d_target) {
                            case 'ht_tag': 
                                $barcode2DTarget = route('ht/assetTag', $asset->asset_tag); 
                                break;
                            case 'plain_asset_id': 
                                $barcode2DTarget = (string) $asset->id; 
                                break;
                            case 'plain_asset_tag': 
                                $barcode2DTarget = $asset->asset_tag; 
                                break;
                            case 'plain_serial_number': 
                                $barcode2DTarget = $asset->serial; 
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
                            $assetData->put('barcode2d', (object)[
                                'type' => $barcode2DType,
                                'content' => $barcode2DTarget,
                            ]);
                        }
                    }

                $fields = $fieldDefinitions
                    ->map(fn($field) => $field->toArray($asset))
                    ->filter(fn($field) => $field != null)
                    ->reduce(function($myFields, $field) {
                        // Remove Duplicates
                        $toAdd = $field
                            ->filter(fn($o) => !$myFields->contains('dataSource', $o['dataSource']))
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
                    }, new Collection());

                $emptyRowsCount = $settings->label2_empty_row_count;
                if($emptyRowsCount) {
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
                }
               else{
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
