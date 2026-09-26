<?php

namespace App\Presenters;

class CompanyIntegrationsPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     *
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('admin/companies/table.name'),
                'visible' => true,
                'formatter' => 'companiesLinkFormatter',
            ], [
                'field' => 'email',
                'searchable' => true,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('admin/suppliers/table.email'),
                'visible' => true,
                'formatter' => 'emailFormatter',
            ], [
                'field' => 'webhook_selected',
                'searchable' => false,
                'sortable' => true,
                'switchable' => false,
                'title' => trans('admin/settings/general.webhook_provider'),
                'visible' => true,
                'formatter' => 'companyWebhookIntegrationFormatter',
                'class' => 'text-center',
            ],
            [
                'field' => 'actions',
                'scope' => 'col',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'companyWebhookActionsFormatter',
                'printIgnore' => true,
                'class' => 'hidden-print',
            ],
        ];

        return json_encode($layout);
    }
}