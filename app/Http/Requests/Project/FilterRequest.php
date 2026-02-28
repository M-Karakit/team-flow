<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status'    => ['nullable', 'string', 'in:active,on_hold,archived'],
            'owner_id'  => ['nullable', 'integer'],
            'member_id' => ['nullable', 'integer'],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
            'page'      => ['nullable', 'integer', 'min:1'],
        ];
    }
}
