<?php

namespace App\Models;

class Checkoutable
{
    public function __construct(
        public int $acceptance_id,
        public string $company,
        public string $category,
        public string $model,
        public string $asset_tag,
        public string $name,
        public string $type,
        public object $acceptance,
        public readonly User|Asset|Location|null $assignee,
        public readonly string $plain_text_category,
        public readonly string $plain_text_model,
        public readonly string $plain_text_name,
        public readonly string $plain_text_company,
    ){}

    public static function fromAcceptance(CheckoutAcceptance $unaccepted): self
    {
        $unaccepted_row = $unaccepted->checkoutable;
        $acceptance = $unaccepted;

        $assignee = $acceptance->assignedTo;
        $company = $unaccepted_row?->company?->present()?->nameUrl() ?? '';
        $category = $model = $name = $tag = '';
        $type = $acceptance->checkoutable_item_type ?? '';


        if($unaccepted_row instanceof Asset){
            $category = optional($unaccepted_row->model?->category?->present())->nameUrl() ?? '';
            $model = optional($unaccepted_row->present())->modelUrl() ?? '';
            $name = optional($unaccepted_row->present())->nameUrl() ?? '';
            $tag = (string) ($unaccepted_row->asset_tag ?? '');
         }
        if($unaccepted_row instanceof Accessory){
            $category = optional($unaccepted_row->category?->present())->nameUrl() ?? '';
            $model = $unaccepted_row->model_number ?? '';
            $name = optional($unaccepted_row->present())->nameUrl() ?? '';
        }
        if($unaccepted_row instanceof LicenseSeat){
            $category = optional($unaccepted_row->license?->category?->present())->nameUrl() ?? '';
            $company = optional($unaccepted_row->license?->company?->present())?->nameUrl() ?? '';
            $model = '';
            $name = $unaccepted_row->license?->present()->nameUrl() ?? '';
        }
        if($unaccepted_row instanceof Consumable){
            $category = optional($unaccepted_row->category?->present())->nameUrl() ?? '';
            $model = $unaccepted_row->model_number ?? '';
            $name = $unaccepted_row?->present()?->nameUrl() ?? '';
        }
        if($unaccepted_row instanceof Component){
            $category = optional($unaccepted_row->category?->present())->nameUrl() ?? '';
            $model = $unaccepted_row->model_number ?? '';
            $name = $unaccepted_row?->present()?->nameUrl()  ?? '';
        }

        return new self(
            acceptance_id: $acceptance->id,
            company: $company,
            category: $category,
            model: $model,
            asset_tag: $tag,
            name: $name,
            type: $type,
            acceptance: $acceptance,
            assignee: $assignee,
            //plain text for CSVs
            plain_text_category: $unaccepted_row?->model?->category?->name ?? $unaccepted_row?->license?->category?->name ?? $unaccepted_row?->category?->name ?? '',
            plain_text_model: $unaccepted_row?->model?->name ?? $unaccepted_row?->model_number ?? '',
            plain_text_name: $unaccepted_row?->name ?? $unaccepted_row?->license?->name ?? '',
            plain_text_company: $unaccepted_row?->company->name ?? $unaccepted_row?->license?->company?->name ?? '',
        );
    }
}
