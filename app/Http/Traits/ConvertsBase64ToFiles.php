<?php

namespace App\Http\Traits;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait ConvertsBase64ToFiles
{
    protected function base64FileKeys(): array
    {
        return [];
    }

    /**
     * Pulls the Base64 contents for each file key and creates
     * an UploadedFile instance from it and sets it on the
     * request.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $flattened = Arr::dot($this->base64FileKeys());

        Collection::make($flattened)->each(function ($filename, $key) {
            rescue(function () use ($key, $filename) {
                // dont process plain files
                if ( $this->file($key)){
                    return;
                }

                $base64Contents = $this->input($key);

                if (!$base64Contents) {
                    return;
                }

                // Generate a temporary path to store the Base64 contents
                $tempFilePath = tempnam(sys_get_temp_dir(), $filename);

                // Store the contents using a stream, or throw an Error (which doesn't do anything?)
                if (Str::startsWith($base64Contents, 'data:') && count(explode(',', $base64Contents)) > 1) {
                    $source = fopen($base64Contents, 'r'); // PHP has special processing for "data:" URL's
                    $destination = fopen($tempFilePath, 'w');

                    stream_copy_to_stream($source, $destination);

                    fclose($source);
                    fclose($destination);
                } else {
                    // TODO - to get a better error message here, can we maybe do something with modifying the errorBag?
                    throw new ValidationException("Need Base64 URL starting with 'data:'"); // This doesn't actually throw?
                }

                $uploadedFile = new UploadedFile($tempFilePath, $filename, null, null, true);

                Log::debug("Trait: uploadedfile ". $tempFilePath);
                $this->offsetUnset($key);                                                                                                                                                                                                                                                                                                                                               
                Log::debug("Trait: encoded field \"$key\" removed" );
                
                //Inserting new file  to $this-files does not work so have to deal this after
                $this->offsetSet($key,$uploadedFile);
                Log::debug("Trait:  field \"$key\" inserted as UplodedFile" );
    
            }, null, false);
        });
    }
}
/**
 * Loosely based on idea https://github.com/protonemedia/laravel-mixins/tree/master/src/Request
 * */