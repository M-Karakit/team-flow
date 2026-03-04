<?php

namespace App\Http\Requests\Task;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assigned_to' => ['sometimes', 'integer', 'exists:users,id'],
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'status'      => ['sometimes', 'string', 'in:todo,in_progress,in_review,done'],
            'priority'    => ['sometimes', 'in:low,medium,high,critical'],
            'due_date'    => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Update task validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}
