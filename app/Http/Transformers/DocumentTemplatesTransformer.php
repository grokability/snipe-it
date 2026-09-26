<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\DocumentTemplate;
use Illuminate\Database\Eloquent\Collection;

class DocumentTemplatesTransformer
{
    public function transformTemplates(Collection $templates, $total)
    {
        $array = [];
        foreach ($templates as $template) {
            $array[] = $this->transformTemplate($template);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformTemplate(?DocumentTemplate $template = null)
    {
        if ($template) {
            $array = [
                'id' => (int) $template->id,
                'name' => e($template->name),
                'slug' => e($template->slug),
                'type' => e($template->type),
                'language' => e($template->language),
                'page_size' => e($template->page_size),
                'orientation' => e($template->orientation),
                'active' => (bool) $template->active,
                'current_version' => ($template->currentVersion) ? [
                    'id' => (int) $template->currentVersion->id,
                    'version' => (int) $template->currentVersion->version,
                    'eula_enabled' => (bool) $template->currentVersion->eula_enabled,
                ] : null,
                'documents_count' => (int) $template->documents()->count(),
                'created_at' => Helper::getFormattedDateObject($template->created_at, 'datetime'),
            ];

            return $array;
        }

        return null;
    }
}
