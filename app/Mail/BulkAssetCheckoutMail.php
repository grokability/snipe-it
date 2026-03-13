<?php

namespace App\Mail;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class BulkAssetCheckoutMail extends Mailable
{
    use Queueable, SerializesModels;

    public bool $requires_acceptance;

    public Collection $assetsByCategory;

    public function __construct(
        public Collection $assets,
        public Model $target,
        public User $admin,
        public string $checkout_at,
        public string $expected_checkin,
        public string $note,
    ) {
        $this->requires_acceptance = $this->requiresAcceptance();

        $this->loadCustomFieldsOnAssets();
        $this->loadEulasOnAssets();

        $this->assetsByCategory = $this->groupAssetsByCategory();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.markdown.bulk-asset-checkout-mail',
            with: [
                'introduction' => $this->getIntroduction(),
                'requires_acceptance' => $this->requires_acceptance,
                'requires_acceptance_info' => $this->getRequiresAcceptanceInfo(),
                'requires_acceptance_prompt' => $this->getRequiresAcceptancePrompt(),
                'singular_eula' => $this->getSingularEula(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function getSubject(): string
    {
        if ($this->assets->count() > 1) {
            return ucfirst(trans('general.assets_checked_out_count'));
        }

        return trans('mail.Asset_Checkout_Notification', ['tag' => $this->assets->first()->asset_tag]);
    }

    private function loadCustomFieldsOnAssets(): void
    {
        $this->assets = $this->assets->map(function (Asset $asset) {
            $fields = $asset->model?->fieldset?->fields->filter(function (CustomField $field) {
                return $field->show_in_email && !$field->field_encrypted;
            });

            $asset->setRelation('fields', $fields);

            return $asset;
        });
    }

    private function loadEulasOnAssets(): void
    {
        $this->assets = $this->assets->map(function (Asset $asset) {
            $asset->eula = $asset->getEula();

            return $asset;
        });
    }

    private function groupAssetsByCategory(): Collection
    {
        return $this->assets->groupBy(fn($asset) => $asset->model->category->id);
    }

    private function getIntroduction(): string
    {
        if ($this->target instanceof Location) {
            return trans_choice('mail.new_item_checked_location', $this->assets->count(), ['location' => $this->target->name]);
        }

        return trans_choice('mail.new_item_checked', $this->assets->count());
    }

    private function getRequiresAcceptanceInfo(): ?string
    {
        if (!$this->requires_acceptance) {
            return null;
        }

        return trans_choice('mail.items_checked_out_require_acceptance', $this->assets->count());
    }

    private function getRequiresAcceptancePrompt(): ?string
    {
        if (!$this->requires_acceptance) {
            return null;
        }

        $acceptanceUrl = $this->assets->count() === 1
            ? route('account.accept.item', $this->assets->first())
            : route('account.accept');

        return
            sprintf(
                '**[✔ %s](%s)**',
                trans_choice('mail.click_here_to_review_terms_and_accept_item', $this->assets->count()),
                $acceptanceUrl,
            );
    }

    private function getSingularEula()
    {
        // get unique categories from all assets
        $categories = $this->assets->pluck('model.category.id')->unique();

        // if assets do not have the same category then return early...
        if ($categories->count() > 1) {
            return null;
        }

        // if assets do have the same category then return the shared EULA
        if ($categories->count() === 1) {
            return $this->assets->first()->getEula();
        }
    }

    private function requiresAcceptance(): bool
    {
        foreach ($this->assets as $asset) {
            if ($asset->requireAcceptance()) {
                return true;
            }
        }

        return false;
    }
}
