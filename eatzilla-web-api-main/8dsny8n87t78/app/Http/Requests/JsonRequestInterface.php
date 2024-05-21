<?php

namespace App\Http\Requests;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;

abstract class JsonRequestInterface extends LaravelFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorize();

    /**
     *
     * @return JsonResponse|void
     */
    protected function failedAuthorization()
    {
        try {
            throw new AuthorizationException('Unauthorized.');
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => false]);
        }
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->messages()->all();

        $errorMessage = '';
        $errorNumber = 1;
        foreach ($errors as $value) {
            $errorMessage .= $value;
            if(count($errors) > $errorNumber){
                $errorMessage .= ",";
            }
            $errorNumber++;
        }
        throw new HttpResponseException(
            response()->json(['status' => false, 'error_code' => 101, 'message' => $errorMessage])
        );
    }
}
