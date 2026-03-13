<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    protected $rules = [];

    public function json($key = null, $default = null)
    {
        if ($this->ajax() || $this->wantsJson()) {
            json_decode($this->getContent(), false, 512, JSON_THROW_ON_ERROR); // ignore output, just throw
        }
        return parent::json($key, $default);
    }

    public function rules()
    {
        return $this->rules;
    }

    public function response(array $errors)
    {
        if ($this->ajax() || $this->wantsJson()) {
            return Helper::formatStandardApiResponse('error', null, $errors);
        }

        return $this->redirector->to($this->getRedirectUrl())
            ->withInput($this->except($this->dontFlash))
            ->withErrors($errors, $this->errorBag);
    }
}
