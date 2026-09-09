<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\LabelsTransformer;
use App\Models\Labels\CustomLabels\PreviewSheetLabel;
use App\Models\Labels\CustomLabels\PreviewTapeLabel;
use App\Models\Labels\CustomUserLabel;
use App\Models\Labels\Label;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\ItemNotFoundException;

class LabelsController extends Controller
{
    /**
     * Returns JSON listing of all labels.
     *
     * @author Grant Le Roux <grant.leroux+snipe-it@gmail.com>
     */
    public function index(Request $request): JsonResponse|array
    {
        $this->authorize('view', Label::class);

        $baseLabels = Label::find()
            ->reject(fn(Label $label) => $label instanceof PreviewSheetLabel)
            ->reject(fn(Label $label) => $label instanceof PreviewTapeLabel)
            ->map(function (Label $label) {
                return [
                    'source' => 'base',
                    'label' => $label,
                ];
            });

        $customLabels = CustomUserLabel::query()
            ->get()
            ->map(function ($label) {
                return [
                    'source' => 'custom',
                    'label' => $label,
                ];
            });

        $labels = $baseLabels->merge($customLabels);

        if ($request->filled('search')) {
            $search = $request->input('search');

            $labels = $labels->filter(function ($row) use ($search) {
                $label = $row['label'];
                $name = $row['source'] === 'custom' ? $label->name : $label->getName();

                return stripos($name, $search) !== false;
            });
        }

        $total = $labels->count();

        $offset = $request->input('offset', 0);
        $offset = ($offset > $total) ? $total : $offset;

        $maxLimit = config('app.max_results');
        $limit = $request->input('limit', $maxLimit);
        $limit = ($limit > $maxLimit) ? $maxLimit : $limit;

        $labels = $labels->skip($offset)->take($limit);

        return (new LabelsTransformer)->transformLabels($labels, $total);
    }

    /**
     * Returns JSON with information about a label for detail view.
     *
     * @author Grant Le Roux <grant.leroux+snipe-it@gmail.com>
     */
    public function show(string $labelName): JsonResponse|array
    {
        if (str_starts_with($labelName, 'custom:')) {
            $customLabelId = (int)str_replace('custom:', '', $labelName);

            $customLabel = CustomUserLabel::find($customLabelId);

            if ($customLabel) {
                $this->authorize('view', $customLabel);

                return (new LabelsTransformer)->transformLabels(
                    collect([[
                        'source' => 'custom',
                        'label' => $customLabel,
                    ]]),
                    1
                );
            }
        }

        $labelName = str_replace('/', '\\', $labelName);
        try {
            $label = Label::find($labelName);
        } catch (ItemNotFoundException $e) {
            return response()
                ->json(
                    Helper::formatStandardApiResponse('error', null, trans('admin/labels/message.does_not_exist')),
                    404
                );
        }
        $this->authorize('view', $label);

        return (new LabelsTransformer)->transformLabels(collect([[
            'source' => 'base',
            'label' => $label,
        ]]), 1);
    }
}
