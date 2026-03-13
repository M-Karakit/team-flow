<?php

namespace App\Http\Requests\Comment;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCommentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body'      => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Store comment validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
