<?php

namespace Facades\Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl
 */
class GenerateSignedUploadUrl extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl';
    }
}
