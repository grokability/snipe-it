<?php

namespace App\Http\Requests;

use App\Models\SnipeModel;
use enshrined\svgSanitize\Sanitizer;
use Intervention\Image\Facades\Image;
use App\Http\Traits\ConvertsBase64ToFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\NotReadableException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImageUploadRequest extends Request
{
    use ConvertsBase64ToFiles;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
       
            return [
                'image' => 'mimes:png,gif,jpg,jpeg,svg,bmp,svg+xml,webp,avif',
                'avatar' => 'mimes:png,gif,jpg,jpeg,svg,bmp,svg+xml,webp,avif',
                'favicon' => 'mimes:png,gif,jpg,jpeg,svg,bmp,svg+xml,webp,image/x-icon,image/vnd.microsoft.icon,ico',
            ];
    }

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }
    
    /** 
     * Fields that should be traited from base64 to files
     */
    protected function base64FileKeys(): array
    {
        /**
         * image_source is here just legacy reasons. Api\AssetController
         * had it once to allow encoded image uploads.
        */ 
        return [
            'image' => 'auto',
            'image_source' => 'auto'
        ];
    }

    /**
     * Handle and store any images attached to request
     * @param SnipeModel $item Item the image is associated with
     * @param string $path  location for uploaded images, defaults to uploads/plural of item type.
     * @return SnipeModel        Target asset is being checked out to.
     */
    public function handleImages($item, $w = 600, $form_fieldname = 'image', $path = null, $db_fieldname = 'image')
    {

        $type = class_basename(get_class($item));

        if (is_null($path)) {

            $path = strtolower(str_plural($type));

            if ($type == 'AssetModel') {
                $path = 'models';
            }

            if ($type == 'user') {
                $path = 'avatars';
            }

        }


        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }

        if ($this->offsetGet($form_fieldname) instanceof UploadedFile) {
           $image = $this->offsetGet($form_fieldname);
        } elseif ($this->hasFile($form_fieldname)) {
            $image = $this->file($form_fieldname);
        }

        if (isset($image)) {


                $ext = $image->guessExtension();
                $file_name = $type.'-'.$form_fieldname.($item->id ?? '-'.$item->id).'-'.str_random(10).'.'.$ext;
                
                if (($image->getMimeType() == 'image/vnd.microsoft.icon') || ($image->getMimeType() == 'image/x-icon') || ($image->getMimeType() == 'image/avif') || ($image->getMimeType() == 'image/webp')) {
                    // If the file is an icon, webp or avif, we need to just move it since gd doesn't support resizing
                    // icons or avif, and webp support and needs to be compiled into gd for resizing to be available
                    Storage::disk('public')->put($path.'/'.$file_name, file_get_contents($image));

                } elseif($image->getMimeType() == 'image/svg+xml') {
                    // If the file is an SVG, we need to clean it and NOT encode it
                    $sanitizer = new Sanitizer();
                    $dirtySVG = file_get_contents($image->getRealPath());
                    $cleanSVG = $sanitizer->sanitize($dirtySVG);

                    try {
                        Storage::disk('public')->put($path . '/' . $file_name, $cleanSVG);
                    } catch (\Exception $e) {
                        Log::debug($e);
                    }
                } else {

                    try {
                        $upload = Image::make($image->getRealPath())->setFileInfoFromPath($image->getRealPath())->resize(null, $w, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })->orientate();

                    } catch(NotReadableException $e) {
                        Log::debug($e);
                        $validator = Validator::make([], []);
                        $validator->errors()->add($form_fieldname, trans('general.unaccepted_image_type', ['mimetype' => $image->getClientMimeType()]));

                        throw new \Illuminate\Validation\ValidationException($validator);
                    }

                    // This requires a string instead of an object, so we use ($string)
                    Storage::disk('public')->put($path.'/'.$file_name, (string) $upload->encode());

                }

                 // Remove Current image if exists
                $item = $this->deleteExistingImage($item, $path, $db_fieldname);
                $item->{$db_fieldname} = $file_name;



        // If the user isn't uploading anything new but wants to delete their old image, do so
        } elseif ($this->input('image_delete') == '1') {
            $item = $this->deleteExistingImage($item, $path, $db_fieldname);
        }

        return $item;
    }

    public function deleteExistingImage($item, $path = null, $db_fieldname = 'image') {

        if ($item->{$db_fieldname}!='') {
            try {
                Storage::disk('public')->delete($path.'/'.$item->{$db_fieldname});
                $item->{$db_fieldname} = null;
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        return $item;
    }
    
}
