<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryInstructionRequest extends FormRequest
{
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
            'instruction'=>'required|custom_text',
        ];
    }

    public function messages()
    {
        return [
            'care_instructions.custom_text'=>'Please enter a valid instruction',
        ];
    }
}
