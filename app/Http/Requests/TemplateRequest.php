<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [

            'event_name' => 'required',
            'image' => 'required|mimes:jpeg,jpg,png,gif',
        ];
    }

    public function message()
    {
        return [
            'event_name.required' =>"Event Name is required",
            'image.required' =>"Template image is required",
        ];
    }
}
