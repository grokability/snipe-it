<?php

namespace App\Policies;

class DocumentTemplatePolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'document_templates';
    }
}
