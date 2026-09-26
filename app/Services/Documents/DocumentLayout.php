<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\Setting;
use App\Support\Jalali\Jalali;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DocumentLayout
{
    public const HEADER_DEFAULTS = [
        'show_header' => true, 'show_logo' => true, 'show_company' => true,
        'show_number' => true, 'show_date' => true, 'show_rule' => true,
        'show_title' => true, 'show_assignee' => true, 'show_assets' => true,
        'show_notes' => true, 'show_provenance' => true, 'show_revision' => true,
        'left_text' => '', 'right_text' => '', 'title' => '',
        'date_calendar' => 'both', 'date_format' => 'Y-m-d',
        'font_family' => 'dejavusans', 'font_size' => 9, 'header_size' => 9,
        'heading_size' => 14, 'line_height' => 1.25,
        'margin_top' => 34, 'margin_bottom' => 20, 'margin_left' => 15, 'margin_right' => 15,
        'terms_position' => 'after_assets', 'alignment' => 'start',
    ];

    public const FOOTER_DEFAULTS = ['text' => '', 'show_footer' => true, 'show_pages' => true, 'font_size' => 7, 'alignment' => 'left'];

    public const SIGNATURE_DEFAULTS = ['columns' => 2, 'heading' => '', 'instructions' => '', 'show_name' => true, 'show_date' => true, 'show_line' => true, 'space' => 12];

    public static function rules(): array
    {
        $rules = [];
        foreach (['header_config' => self::HEADER_DEFAULTS, 'footer_config' => self::FOOTER_DEFAULTS, 'signature_config' => self::SIGNATURE_DEFAULTS] as $prefix => $defaults) {
            foreach ($defaults as $key => $default) {
                if (is_bool($default)) {
                    $rules[$prefix.'.'.$key] = ['sometimes', 'boolean'];
                }
            }
        }
        foreach (['left_text', 'right_text'] as $key) {
            $rules['header_config.'.$key] = ['nullable', 'string', 'max:1000'];
        }
        foreach (['font_size', 'header_size', 'heading_size'] as $key) {
            $rules['header_config.'.$key] = ['sometimes', 'numeric', 'between:6,30'];
        }
        foreach (['top', 'bottom', 'left', 'right'] as $side) {
            $rules['header_config.margin_'.$side] = ['sometimes', 'numeric', 'between:10,60'];
        }
        $rules['header_config.date_calendar'] = ['sometimes', 'in:gregorian,jalali,both'];
        $rules['header_config.date_format'] = ['sometimes', 'in:Y-m-d,Y/m/d,d/m/Y,m/d/Y,d.m.Y'];
        $rules['header_config.font_family'] = ['sometimes', 'in:'.implode(',', array_keys(DocumentFonts::FAMILIES))];
        $rules['header_config.line_height'] = ['sometimes', 'numeric', 'between:1,2'];
        $rules['header_config.terms_position'] = ['sometimes', 'in:before_assets,after_assets'];
        $rules['header_config.alignment'] = ['sometimes', 'in:start,left,right,center,justify'];
        $rules['footer_config.font_size'] = ['sometimes', 'numeric', 'between:6,20'];
        $rules['footer_config.alignment'] = ['sometimes', 'in:left,right,center'];
        $rules['signature_config.columns'] = ['sometimes', 'integer', 'between:1,4'];
        $rules['signature_config.space'] = ['sometimes', 'integer', 'between:5,40'];
        $rules['signature_config.heading'] = ['nullable', 'string', 'max:191'];
        $rules['signature_config.instructions'] = ['nullable', 'string', 'max:2000'];
        $rules['signature_config.labels'] = ['sometimes', 'array:employee,it_representative,manager,custom'];
        $rules['signature_config.labels.*'] = ['required', 'string', 'max:100'];

        return $rules;
    }

    public function headerConfig(Document $document): array
    {
        return array_replace(self::HEADER_DEFAULTS, $document->templateVersion->snapshot['header_config'] ?? []);
    }

    public function footerConfig(Document $document): array
    {
        return array_replace(self::FOOTER_DEFAULTS, $document->templateVersion->snapshot['footer_config'] ?? []);
    }

    public function date(mixed $date, array $config): string
    {
        if (! $date) {
            return '';
        }
        $format = $config['date_format'] ?? 'Y-m-d';
        $calendar = $config['date_calendar'] ?? 'both';
        $gregorian = Carbon::parse($date)->format($format);
        $jalali = Jalali::format($date, $format);

        return match ($calendar) {
            'gregorian' => $gregorian,
            'jalali' => $jalali,
            default => $gregorian.' / '.$jalali,
        };
    }

    public function context(Document $document): array
    {
        $context = $document->data ?? [];
        $config = $this->headerConfig($document);
        foreach (['document.date', 'checkout.date', 'checkout.expected_return'] as $key) {
            $date = $context[$key] ?? ($key === 'document.date' ? $document->created_at : null);
            if ($date) {
                $context[$key.'_formatted'] = $this->date($date, $config);
                $context[$key] = Carbon::parse($date)->format($config['date_format']);
                $context[$key.'_jalali'] = Jalali::format($date, $config['date_format']);
            }
        }

        return $context;
    }

    public function assets(Document $document): array
    {
        return $document->items->map(fn ($item) => [
            'asset_tag' => (string) $item->asset_tag, 'name' => (string) $item->name,
            'model' => (string) $item->model_name, 'manufacturer' => (string) $item->manufacturer_name,
            'serial' => (string) $item->serial, 'status' => (string) $item->status_name,
            'condition' => (string) $item->condition,
        ])->all();
    }

    public function text(?string $text, Document $document, string $format = 'plain'): string
    {
        return app(TemplateService::class)->renderTerms($text, $format, $this->context($document), $this->assets($document));
    }

    public function terms(Document $document): string
    {
        $version = $document->templateVersion;

        return $this->text($version->eula_body, $document, $version->snapshotField('eula_format', 'plain'));
    }

    public function header(Document $document, bool $pdf = false): string
    {
        $config = $this->headerConfig($document);
        if (! $config['show_header']) {
            return '';
        }
        $settings = Setting::getSettings();
        $left = [];
        if ($config['show_logo'] && $settings->logo && Storage::disk('public')->exists($settings->logo)) {
            $left[] = $this->image(Storage::disk('public')->get($settings->logo), $pdf, 28);
        }
        if ($config['show_company']) {
            $left[] = e(($document->data['company.name'] ?? '') ?: $settings->site_name);
        }
        if ($config['left_text']) {
            $left[] = $this->text($config['left_text'], $document);
        }
        $right = [];
        if ($config['show_number']) {
            $right[] = '<b>'.e($document->number).'</b>';
        }
        if ($config['show_date']) {
            $right[] = e($this->date($document->data['document.date'] ?? $document->created_at, $config));
        }
        if ($config['right_text']) {
            $right[] = $this->text($config['right_text'], $document);
        }

        return '<table width="100%"><tr><td width="50%">'.implode('<br>', $left).'</td><td width="50%" align="right">'.implode('<br>', $right).'</td></tr></table>'.($config['show_rule'] ? '<hr>' : '');
    }

    public function body(Document $document, bool $pdf = false): string
    {
        $version = $document->templateVersion;
        $snapshot = $version->snapshot;
        $config = $this->headerConfig($document);
        $context = $this->context($document);
        $html = '';
        if ($config['show_title']) {
            $html .= '<h1 style="font-size:'.(float) $config['heading_size'].'pt;">'.$this->text($config['title'] ?: $snapshot['name'], $document).'</h1>';
        }
        if ($config['show_assignee']) {
            $html .= '<table width="100%" cellpadding="4"><tr><td><b>'.e(trans('documents.general.assignee')).':</b> '.e($context['user.name'] ?? '').'</td><td><b>'.e(trans('admin/users/table.employee_num')).':</b> '.e($context['user.employee_id'] ?? '').'</td></tr><tr><td>'.e($context['user.department'] ?? '').'</td><td>'.e($context['user.email'] ?? '').'</td></tr></table>';
        }
        if (! empty($snapshot['body'])) {
            $html .= app(PlaceholderRenderer::class)->render(app(TemplateService::class)->sanitizeHtml($snapshot['body']), array_map(fn ($v) => e((string) $v), $context), array_map(fn ($row) => array_map(fn ($v) => e($v), $row), $this->assets($document)));
        }
        $terms = '';
        if ($version->eula_enabled && $version->eula_body) {
            $terms = '<h2 style="font-size:'.(float) $config['heading_size'].'pt;">'.$this->text($version->eula_title, $document).'</h2>'.$this->terms($document);
            if ($config['show_revision'] && $version->eula_version) {
                $terms .= '<p><i>'.e(trans('documents.general.eula_version')).': '.e($version->eula_version).'</i></p>';
            }
        }
        if ($config['terms_position'] === 'before_assets') {
            $html .= $terms;
        }
        if ($config['show_assets']) {
            $columns = $snapshot['asset_columns'] ?? app(TemplateService::class)->defaultAssetColumns();
            $html .= '<table width="100%" border="0.5" cellpadding="4"><thead><tr>';
            foreach ($columns as $column) {
                $html .= '<th><b>'.e(trans('documents.columns.'.$column)).'</b></th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($this->assets($document) as $row) {
                $html .= '<tr>';
                foreach ($columns as $column) {
                    $html .= '<td>'.e($row[$column] ?? '').'</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }
        if ($config['terms_position'] === 'after_assets') {
            $html .= $terms;
        }
        if ($config['show_notes'] && $document->notes) {
            $html .= '<h3>'.e(trans('documents.general.notes')).'</h3><p>'.nl2br(e($document->notes)).'</p>';
        }
        $html .= $this->signatures($document, $pdf);
        if ($config['show_provenance']) {
            $html .= '<p style="font-size:7pt;">'.e(trans('documents.general.template')).': '.e($snapshot['name'] ?? '').' — v'.(int) $version->version.' | '.e($context['document.date_formatted'] ?? '').'</p>';
        }

        return $html;
    }

    public function signatures(Document $document, bool $pdf = false): string
    {
        $config = array_replace(self::SIGNATURE_DEFAULTS, $document->templateVersion->snapshot['signature_config'] ?? []);
        $html = $config['heading'] ? '<h3>'.$this->text($config['heading'], $document).'</h3>' : '';
        $html .= $config['instructions'] ? '<p>'.$this->text($config['instructions'], $document).'</p>' : '';
        $signatures = $document->signatures->keyBy('role');
        foreach (array_chunk($config['roles'] ?? ['employee', 'it_representative'], (int) $config['columns']) as $roles) {
            $html .= '<table class="signature-block" width="100%" cellpadding="6" nobr="true"><tr>';
            foreach ($roles as $role) {
                $signature = $signatures[$role] ?? null;
                $label = $config['labels'][$role] ?? ucfirst(str_replace('_', ' ', $role));
                $html .= '<td width="'.(100 / (int) $config['columns']).'%"><b>'.e($label).'</b><br>';
                if ($signature?->signature_filename && Storage::exists($signature->signature_filename)) {
                    $html .= $this->image(Storage::get($signature->signature_filename), $pdf, 36).'<br>';
                } else {
                    $html .= '<div style="height:'.(int) $config['space'].'mm;">'.str_repeat('<br>', max(1, (int) ($config['space'] / 5))).'</div>';
                }
                if ($config['show_line']) {
                    $html .= '____________________<br>';
                }
                if ($config['show_name']) {
                    $html .= e(trans('general.name')).': '.e($signature?->signer_name ?? ($role === 'employee' ? ($document->data['user.name'] ?? '') : '')).'<br>';
                }
                if ($config['show_date']) {
                    $html .= e(trans('general.date')).': '.e($signature?->signed_at ? $this->date($signature->signed_at, $this->headerConfig($document)) : '________________').'<br>';
                }
                if ($signature?->method === 'printed') {
                    $html .= '<i>'.e(trans('documents.general.printed')).'</i>';
                }
                $html .= '</td>';
            }
            $html .= '</tr></table>';
        }

        return $html;
    }

    private function image(string $bytes, bool $pdf, int $height): string
    {
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        if (! in_array($mime, ['image/png', 'image/jpeg', 'image/gif'], true)) {
            return '';
        }

        return '<img alt="" src="'.($pdf ? '@' : 'data:'.$mime.';base64,').base64_encode($bytes).'" height="'.$height.'">';
    }
}
