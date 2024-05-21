<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\JsonRequestInterface;
use Illuminate\Foundation\Http\FormRequest;

class CheckPromocodeRequest extends JsonRequestInterface
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
            'bill_amount' => 'required',
            'promocode' => 'required',
        ];
    }
}
