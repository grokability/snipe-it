<?php

namespace App\Services\Documents;

use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateVersion;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Template CRUD + explicit draft→publish versioning (spec §3, §6).
 *
 * A version row is created ONLY on publishVersion() and is immutable after
 * that (enforced on the model). Editing the template changes the working
 * copy; already-signed documents keep pointing at their frozen version.
 */
class TemplateService
{
    public function rules(int $templateId = 0): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'type' => ['required', Rule::in(DocumentTemplate::TYPES)],
            'language' => ['nullable', 'string', 'max:10'],
            'page_size' => ['nullable', Rule::in(['A4', 'LETTER', 'LEGAL'])],
            'orientation' => ['nullable', Rule::in(['P', 'L'])],
            'active' => ['boolean'],
            'header_config' => ['nullable', 'array'],
            'footer_config' => ['nullable', 'array'],
            'signature_config' => ['nullable', 'array'],
            'asset_columns' => ['required_if:layout_submitted,1', 'nullable', 'array', 'min:1'],
            'asset_columns.*' => ['string', Rule::in(['asset_tag', 'name', 'model', 'manufacturer', 'serial', 'status', 'condition'])],
            'layout_submitted' => ['sometimes', 'boolean'],
            'signature_roles' => ['required_if:layout_submitted,1', 'array', 'min:1'],
            'signature_roles.*' => ['string', 'distinct', Rule::in(['employee', 'it_representative', 'manager', 'custom'])],
            'header_config.title' => ['nullable', 'string', 'max:191'],
            'header_config.show_logo' => ['nullable', 'boolean'],
            'footer_config.text' => ['nullable', 'string', 'max:2000'],
            ...DocumentLayout::rules(),
            'custom_font' => ['nullable', 'file', 'max:4096', 'extensions:ttf'],
        ];
    }

    public function create(array $data, User $actor): DocumentTemplate
    {
        $template = new DocumentTemplate;
        $this->fill($template, $data);
        $template->slug = $this->uniqueSlug($data['name']);
        $template->created_by = $actor->id;
        $template->updated_by = $actor->id;
        $template->save();

        return $template;
    }

    public function update(DocumentTemplate $template, array $data, User $actor): DocumentTemplate
    {
        $this->fill($template, $data);
        $template->updated_by = $actor->id;
        $template->save();

        return $template;
    }

    /**
     * Publish the current working copy as a new immutable version.
     */
    public function publishVersion(DocumentTemplate $template, array $eula, User $actor): DocumentTemplateVersion
    {
        $nextVersion = ($template->currentVersion()->value('version') ?? 0) + 1;

        $eula = array_merge($this->terms($template), $eula);
        $snapshot = $this->snapshot($template);
        $snapshot['eula_format'] = $eula['eula_format'];

        return DocumentTemplateVersion::create([
            'document_template_id' => $template->id,
            'version' => $nextVersion,
            'snapshot' => $snapshot,
            'eula_enabled' => (bool) ($eula['eula_enabled'] ?? false),
            'eula_title' => $eula['eula_title'] ?? null,
            'eula_body' => $eula['eula_body'] ?? null,
            'eula_version' => $eula['eula_version'] ?? null,
            'published_by' => $actor->id,
        ]);
    }

    public function snapshot(DocumentTemplate $template): array
    {
        return [
            'name' => $template->name,
            'type' => $template->type,
            'language' => $template->language,
            'page_size' => $template->page_size,
            'orientation' => $template->orientation,
            'header_config' => $template->header_config ?? [],
            'footer_config' => $template->footer_config ?? [],
            'signature_config' => $this->normalizedSignatureConfig($template->signature_config),
            'asset_columns' => $template->asset_columns ?? $this->defaultAssetColumns(),
            'body' => (string) ($template->body ?? ''),
            'eula_format' => $this->terms($template)['eula_format'],
        ];
    }

    public function defaultAssetColumns(): array
    {
        return ['asset_tag', 'name', 'model', 'manufacturer', 'serial'];
    }

    public function normalizedSignatureConfig(?array $config): array
    {
        $config = $config ?? [];

        return array_merge(array_intersect_key($config, array_flip(['columns', 'heading', 'instructions', 'show_name', 'show_date', 'show_line', 'space'])), [
            'roles' => array_values(array_intersect(
                $config['roles'] ?? ['employee', 'it_representative'],
                ['employee', 'it_representative', 'manager', 'custom']
            )),
            'labels' => $config['labels'] ?? [
                'employee' => 'Employee',
                'it_representative' => 'IT Representative',
                'manager' => 'Manager',
                'custom' => 'Additional Signer',
            ],
        ]);
    }

    private function fill(DocumentTemplate $template, array $data): void
    {
        $template->name = $data['name'];
        $template->type = $data['type'];
        $template->language = $data['language'] ?? 'en';
        $template->page_size = $data['page_size'] ?? 'A4';
        $template->orientation = $data['orientation'] ?? 'P';
        $header = $data['header_config'] ?? [];
        unset($header['custom_font']);
        if ($font = $template->header_config['custom_font'] ?? null) {
            $header['custom_font'] = $font;
        }
        if (! empty($data['custom_font'])) {
            $header['custom_font'] = app(DocumentFonts::class)->store($data['custom_font']);
        }
        if (($header['font_family'] ?? '') === 'custom' && empty($header['custom_font'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['custom_font' => trans('documents.custom.font_required')]);
        }
        $template->header_config = $header;
        $template->footer_config = $data['footer_config'] ?? null;
        $template->signature_config = $this->normalizedSignatureConfig($data['signature_config'] ?? null);
        $template->asset_columns = $data['asset_columns'] ?? $this->defaultAssetColumns();
        $template->body = $this->sanitizeHtml($data['body'] ?? '');
        $template->active = (bool) ($data['active'] ?? true);
        if (array_key_exists('eula_enabled', $data)) {
            $template->eula_config = array_intersect_key($data, array_flip([
                'eula_enabled', 'eula_title', 'eula_body', 'eula_version', 'eula_format',
            ]));
        }
    }

    public function terms(DocumentTemplate $template): array
    {
        $version = $template->exists ? $template->currentVersion()->first() : null;

        return array_merge([
            'eula_enabled' => $version?->eula_enabled ?? false,
            'eula_title' => $version?->eula_title ?? '',
            'eula_body' => $version?->eula_body ?? '',
            'eula_version' => $version?->eula_version ?? '',
            'eula_format' => $version?->snapshotField('eula_format', 'plain') ?? 'markdown',
        ], $template->eula_config ?? []);
    }

    public function renderTerms(?string $text, string $format = 'plain', ?array $context = null, array $assets = []): string
    {
        $text = $text ?? '';
        $replacements = [];
        if ($context !== null) {
            // Expand loops before Markdown, but keep data opaque until all formatting is parsed.
            $prefix = 'DOCVALUE'.bin2hex(random_bytes(12));
            $protect = function ($value) use (&$replacements, $prefix): string {
                $token = $prefix.count($replacements).'END';
                $replacements[$token] = e((string) $value);

                return $token;
            };
            $text = app(PlaceholderRenderer::class)->render($text, array_map($protect, $context),
                array_map(fn ($row) => array_map($protect, $row), $assets));
        }
        if ($format !== 'markdown') {
            $html = nl2br(e($text));
        } else {
            $parser = new \Parsedown;
            $parser->setSafeMode(true);
            $html = $this->sanitizeHtml($parser->text($text));
        }

        return strtr($html, $replacements);
    }

    public function sanitizeHtml(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $dom->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
            $render = function (\DOMNode $node) use (&$render): string {
                if ($node instanceof \DOMText) {
                    return e($node->nodeValue);
                }
                if (! $node instanceof \DOMElement) {
                    return '';
                }
                $tag = strtolower($node->tagName);
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'tcpdf', 'img'], true)) {
                    return '';
                }
                $content = '';
                foreach ($node->childNodes as $child) {
                    $content .= $render($child);
                }
                if (! in_array($tag, ['p', 'br', 'hr', 'b', 'strong', 'i', 'em', 'u', 'del', 'blockquote', 'pre', 'code', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr', 'th', 'td'], true)) {
                    return $content;
                }

                return in_array($tag, ['br', 'hr'], true) ? '<'.$tag.'>' : '<'.$tag.'>'.$content.'</'.$tag.'>';
            };

            return $render($dom->getElementsByTagName('body')->item(0));
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'template';
        $slug = $base;
        $i = 1;

        while (DocumentTemplate::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
